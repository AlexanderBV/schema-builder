<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

trait HasPermissions
{
    /**
     * @var array<int, string>
     */
    protected array $permissions = [];

    /**
     * Asigna uno o más permisos requeridos (RBAC).
     *
     * @param  string|array<int, string>  $permission
     */
    public function permission(string|array $permission): static
    {
        $this->permissions = is_array($permission) ? array_values($permission) : [$permission];

        return $this;
    }

    /**
     * Alias de permission().
     */
    public function can(string $ability): static
    {
        return $this->permission($ability);
    }

    /**
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getFirstPermission(): ?string
    {
        return $this->permissions[0] ?? null;
    }

    public function hasPermissions(): bool
    {
        return ! empty($this->permissions);
    }
}
