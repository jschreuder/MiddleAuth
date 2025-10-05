<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicRoleProvider implements RoleProviderInterface
{
    /**
     * @param array<string, RolesCollection> $roleMap Maps actor identifiers (type::id) to RolesCollection
     */
    public function __construct(
        private array $roleMap
    )
    {
    }

    public function getRolesForActor(AuthorizationEntityInterface $actor): RolesCollection
    {
        $actorIdentifier = $actor->getType() . '::' . $actor->getId();
        return $this->roleMap[$actorIdentifier] ?? new RolesCollection();
    }
}
