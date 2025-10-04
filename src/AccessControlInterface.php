<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

interface AccessControlInterface
{
    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context = []
    ): bool;
}
