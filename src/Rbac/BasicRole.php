<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

final class BasicRole implements RoleInterface
{
    /**
     * @param string $name
     * @param PermissionInterface[] $permissions
     */
    public function __construct(
        private string $name,
        private array $permissions
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
