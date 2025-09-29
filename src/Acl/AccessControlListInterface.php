<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface AccessControlListInterface
{
    public function hasAccess(
        AuthorizationEntityInterface $actor, 
        AuthorizationEntityInterface $resource, 
        string $action, 
        array $context = []
    ): bool;
}
