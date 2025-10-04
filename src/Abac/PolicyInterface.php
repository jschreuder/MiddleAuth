<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface PolicyInterface
{
    public function evaluate(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): bool;

    public function getDescription(): string;
}
