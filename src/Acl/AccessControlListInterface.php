<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

interface AccessControlListInterface
{
    public function hasAccess(string $actor, string $resource, string $action, ?array $context = null): bool;
}
