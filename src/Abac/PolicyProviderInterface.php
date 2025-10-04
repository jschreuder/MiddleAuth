<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface PolicyProviderInterface
{
    public function getPolicies(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): PoliciesCollection;
}
