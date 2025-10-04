<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface PermissionInterface
{
    public function matchesResource(AuthorizationEntityInterface $resource): bool;
    public function matchesAction(string $action): bool;
    public function matchesContext(array $context): bool;
}
