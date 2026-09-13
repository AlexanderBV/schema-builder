<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

/**
 * Trait HasPermissions
 *
 * Facilita la asignación de permisos de control de acceso basados en roles (RBAC)
 * a columnas, acciones de fila, botones de toolbar o campos de formulario.
 */
trait HasPermissions
{
    /**
     * Lista de permisos requeridos para autorizar el elemento en el frontend o backend.
     *
     * @var array<int, string>
     */
    protected array $permissions = [];

    /**
     * Asigna uno o más permisos requeridos (RBAC/Abilities).
     *
     * @param  string|array<int, string>  $permission  Permiso individual o array de permisos.
     */
    public function permission(string|array $permission): static
    {
        $this->permissions = is_array($permission) ? array_values($permission) : [$permission];

        return $this;
    }

    /**
     * Alias semántico y legible de permission().
     *
     * @param  string  $ability  Habilidad requerida (ej. 'users.create').
     */
    public function can(string $ability): static
    {
        return $this->permission($ability);
    }

    /**
     * Obtiene el listado de todos los permisos asignados.
     *
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Obtiene el primer permiso configurado o null si no se definió ninguno.
     * Útil para serializar en componentes simples que requieren un único string de permiso.
     */
    public function getFirstPermission(): ?string
    {
        return $this->permissions[0] ?? null;
    }

    /**
     * Comprueba si el elemento tiene al menos un permiso registrado.
     */
    public function hasPermissions(): bool
    {
        return ! empty($this->permissions);
    }
}
