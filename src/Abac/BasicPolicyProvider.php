<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicPolicyProvider implements PolicyProviderInterface
{
    private PoliciesCollection $policies;

    public function __construct(PolicyInterface ...$policies)
    {
        $this->policies = new PoliciesCollection(...$policies);
    }

    public function getPolicies(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): PoliciesCollection
    {
        return $this->policies;
    }
}
