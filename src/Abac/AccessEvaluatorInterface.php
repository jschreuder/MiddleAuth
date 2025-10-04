<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface AccessEvaluatorInterface
{
    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): bool;
}
