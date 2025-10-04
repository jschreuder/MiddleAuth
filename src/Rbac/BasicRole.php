<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

final class BasicRole implements RoleInterface
{
    public function __construct(
        private string $name,
        private PermissionsCollection $permissions
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPermissions(): PermissionsCollection
    {
        return $this->permissions;
    }
}
