<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;
use Psr\Log\LoggerInterface;

final class AbacMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private PolicyProviderInterface $policyProvider,
        private ?LoggerInterface $logger = null
    ) {}

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

        if (!is_null($this->logger)) {
            $this->logger->debug('ABAC middleware evaluating request', [
                'subject_type' => $actor->getType(),
                'subject_id' => $actor->getId(),
                'resource_type' => $resource?->getType(),
                'resource_id' => $resource?->getId(),
                'action' => $action,
                'policies_count' => $policies->count(),
                'context_keys' => array_keys($context),
            ]);
        }

        foreach ($policies as $policy) {
            if (!is_null($this->logger)) {
                $policyDescription = $policy->getDescription();
                $this->logger->debug('Evaluating policy', ['policy_description' => $policyDescription]);
            }

            if ($policy->evaluate($actor, $resource, $action, $context)) {
                if (!is_null($this->logger)) {
                    $this->logger->debug('Policy granted access', ['policy_description' => $policyDescription ?? $policy->getDescription()]);
                }

                return new AuthorizationResponse(
                    true,
                    'Access granted by ' . self::class,
                    self::class
                );
            }
        }

        $this->logger?->debug('No policies granted access, delegating to next handler');

        return $handler->handle($request);
    }
}
