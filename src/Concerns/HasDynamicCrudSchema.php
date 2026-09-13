<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Table\TableSchema;

/**
 * Trait HasDynamicCrudSchema
 *
 * Facilita la integración nativa de controladores API de Laravel con componentes frontend
 * como <CrudComponent />, <DynamicDataTable /> y <DynamicForm /> (Vue, React, BootstrapVue, etc.).
 */
trait HasDynamicCrudSchema
{
    /**
     * Define la especificación del TableSchema del recurso.
     */
    abstract protected function tableSchema(): TableSchema;

    /**
     * Define la especificación del FormSchema utilizado para crear y editar.
     */
    abstract protected function formSchema(): FormSchema;

    /**
     * Define el DetailSchema opcional para la vista de inspección de sólo lectura.
     * Si retorna null, el frontend utiliza automáticamente el FormSchema como fallback.
     */
    protected function detailSchema(): ?DetailSchema
    {
        return null;
    }

    /**
     * Retorna la metadata unificada de esquemas requerida por componentes CRUD del frontend.
     *
     * Endpoint: GET /api/v1/{resource}/schema
     *
     * @return JsonResponse Retorna { table: {...}, form: {...}, detail: {...} }
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
     * Valida la petición HTTP entrante contra las reglas compiladas del FormSchema.
     *
     * Permite inyectar reglas adicionales o sobreescrituras, mensajes y atributos personalizados.
     * En operaciones de actualización ($isUpdate = true), activa automáticamente
     * dirty tracking (convirtiendo reglas 'required' a 'sometimes|required').
     *
     * @param  Request  $request  Petición HTTP actual.
     * @param  FormSchema|null  $schema  Esquema a usar (por defecto $this->formSchema()).
     * @param  bool  $isUpdate  Si es true, activa modo PATCH con validación parcial (dirty tracking).
     * @param  array<string, mixed>  $additionalRules  Reglas de validación complementarias o sobreescritura.
     * @param  array<string, string>  $messages  Mensajes de error personalizados para el validador.
     * @param  array<string, string>  $customAttributes  Nombres de atributos personalizados para mensajes de error.
     * @return array<string, mixed> Datos validados listos para persistir.
     */
    protected function validateWithSchema(
        Request $request,
        ?FormSchema $schema = null,
        bool $isUpdate = false,
        array $additionalRules = [],
        array $messages = [],
        array $customAttributes = []
    ): array {
        $schema = $schema ?? $this->formSchema();
        $rules = array_merge($schema->toValidationRules($isUpdate), $additionalRules);

        return $request->validate($rules, $messages, $customAttributes);
    }
}
