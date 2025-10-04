<?php

use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\Abac\AbacMiddleware;
use jschreuder\MiddleAuth\Abac\BasicPolicyProvider;
use jschreuder\MiddleAuth\Abac\BasicPolicy;
use jschreuder\MiddleAuth\Abac\ClosureBasedAccessEvaluator;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AbacMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ABAC permits', function () {
        $subject = new AuthorizationEntity('user', '123');
        $resource = new AuthorizationEntity('document', '567');

        // Create policy that allows user 123 to view document 567
        $evaluator = new ClosureBasedAccessEvaluator(
            function (AuthorizationEntityInterface $actor, AuthorizationEntityInterface $res, string $action, array $context) {
                return $actor->getId() === '123'
                    && $res->getType() === 'document'
                    && $res->getId() === '567'
                    && $action === 'view';
            }
        );

        $policy = new BasicPolicy(
            $evaluator,
            'Allow user 123 to view document 567'
        );

        $policyProvider = new BasicPolicyProvider($policy);
        $middleware = new AbacMiddleware($policyProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toContain('AbacMiddleware');
    });

    it('denies access when ABAC denies', function () {
        $subject = new AuthorizationEntity('user', '234');
        $resource = new AuthorizationEntity('restricted', 'settings');

        // Create policy provider with no policies
        $policyProvider = new BasicPolicyProvider();
        $middleware = new AbacMiddleware($policyProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $deniedResponse = Mockery::mock(AuthorizationResponseInterface::class);
        $deniedResponse->shouldReceive('isPermitted')->andReturn(false);
        $deniedResponse->shouldReceive('getReason')->andReturn('Denied by handler');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($deniedResponse);

        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });
});
