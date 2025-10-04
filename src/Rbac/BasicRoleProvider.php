<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicRoleProvider implements RoleProviderInterface
{
    /**
     * @param array<string, RoleInterface[]> $roleMap Maps actor identifiers (type::id) to array of roles
     */
    public function __construct(
        private array $roleMap
    )
    {
    }

    /**
     * @return RoleInterface[]
     */
    public function getRolesForActor(AuthorizationEntityInterface $actor): array
    {
        $actorIdentifier = $actor->getType().'::'.$actor->getId();
        return $this->roleMap[$actorIdentifier] ?? [];
    }
}
