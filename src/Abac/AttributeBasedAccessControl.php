<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AccessControlInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class AttributeBasedAccessControl implements AccessControlInterface
{
    public function __construct(
        private PolicyProviderInterface $policyProvider
    )
    {
    }

    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context = []
    ): bool
    {
        $policies = $this->policyProvider->getPolicies($actor, $resource, $action, $context);

        foreach ($policies as $policy) {
            if ($policy->evaluate($actor, $resource, $action, $context)) {
                return true;
            }
        }

        return false;
    }
}
