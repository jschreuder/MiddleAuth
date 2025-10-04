<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

final class AbacMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private PolicyProviderInterface $policyProvider
    )
    {
    }

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        $actor = $request->getSubject();
        $resource = $request->getResource();
        $action = $request->getAction();
        $context = $request->getContext();

        $policies = $this->policyProvider->getPolicies($actor, $resource, $action, $context);

        foreach ($policies as $policy) {
            if ($policy->evaluate($actor, $resource, $action, $context)) {
                return new AuthorizationResponse(
                    true,
                    'Access granted by ' . self::class,
                    self::class
                );
            }
        }

        return $handler->handle($request);
    }
}
