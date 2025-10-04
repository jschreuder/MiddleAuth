<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicRoleBasedAccessControl implements RoleBasedAccessControlInterface
{
    public function __construct(
        private RoleProviderInterface $roleProvider
    )
    {
    }

    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context = []
    ): bool
    {
        $roles = $this->roleProvider->getRolesForActor($actor);

        foreach ($roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                if (
                    $permission->matchesResource($resource)
                    && $permission->matchesAction($action)
                    && $permission->matchesContext($context)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
