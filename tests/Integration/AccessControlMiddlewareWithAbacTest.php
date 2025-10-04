<?php

use jschreuder\MiddleAuth\Basic\AccessControlMiddleware;
use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\Abac\AttributeBasedAccessControl;
use jschreuder\MiddleAuth\Abac\BasicPolicyProvider;
use jschreuder\MiddleAuth\Abac\BasicPolicy;
use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AccessControlMiddleware with ABAC', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ABAC permits', function () {
        $subject = new AuthorizationEntity('user', '123');
        $resource = new AuthorizationEntity('document', '567');

        // Create policy that allows user 123 to view document 567
        $policy = new BasicPolicy(
            function (AuthorizationEntityInterface $actor, AuthorizationEntityInterface $res, string $action, array $context) {
                return $actor->getId() === '123'
                    && $res->getType() === 'document'
                    && $res->getId() === '567'
                    && $action === 'view';
            },
            'Allow user 123 to view document 567'
        );

        $policyProvider = new BasicPolicyProvider($policy);
        $abac = new AttributeBasedAccessControl($policyProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $middleware = new AccessControlMiddleware($abac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toContain('AttributeBasedAccessControl');
    });

    it('denies access when ABAC denies', function () {
        $subject = new AuthorizationEntity('user', '234');
        $resource = new AuthorizationEntity('restricted', 'settings');

        // Create policy provider with no policies
        $policyProvider = new BasicPolicyProvider();
        $abac = new AttributeBasedAccessControl($policyProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $deniedResponse = Mockery::mock(AuthorizationResponseInterface::class);
        $deniedResponse->shouldReceive('isPermitted')->andReturn(false);
        $deniedResponse->shouldReceive('getReason')->andReturn('Denied by handler');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($deniedResponse);

        $middleware = new AccessControlMiddleware($abac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });
});
