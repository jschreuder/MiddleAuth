<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface RoleProviderInterface
{
    public function getRolesForActor(AuthorizationEntityInterface $actor): RolesCollection;
}
