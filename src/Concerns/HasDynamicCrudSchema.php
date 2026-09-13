<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Table\TableSchema;

trait HasDynamicCrudSchema
{
    /**
     * Define el TableSchema del recurso.
     */
    abstract protected function tableSchema(): TableSchema;

    /**
     * Define el FormSchema para creación y edición.
     */
    abstract protected function formSchema(): FormSchema;

    /**
     * Define el DetailSchema opcional. Si es null, el frontend usará el formSchema como fallback.
     */
    protected function detailSchema(): ?DetailSchema
    {
        return null;
    }

    /**
     * Retorna la metadata unificada de esquemas requerida por <CrudComponent />.
     * Endpoint: GET /api/v1/{resource}/schema
     */
    public function schema(): JsonResponse
    {
        $detail = $this->detailSchema();

        return response()->json([
            'table' => $this->tableSchema()->toArray(),
            'form' => $this->formSchema()->toArray(),
            'detail' => $detail?->toArray() ?? $this->formSchema()->toArray(),
        ]);
    }

    /**
     * Valida la petición entrante contra el FormSchema.
     *
     * @param  bool  $isUpdate  Si es true, activa modo PATCH (dirty tracking: required -> sometimes, required)
     * @return array<string, mixed>
     */
    protected function validateWithSchema(Request $request, ?FormSchema $schema = null, bool $isUpdate = false): array
    {
        $schema = $schema ?? $this->formSchema();
        $rules = $schema->toValidationRules($isUpdate);

        return $request->validate($rules);
    }
}
