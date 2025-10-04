<?php

use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\Abac\AbacMiddleware;
use jschreuder\MiddleAuth\Abac\PolicyProviderInterface;
use jschreuder\MiddleAuth\Abac\PolicyInterface;
use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;

describe('Abac\AbacMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('implements AuthorizationMiddlewareInterface', function () {
        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $abac = new AbacMiddleware($policyProvider);
        expect($abac)->toBeInstanceOf(AuthorizationMiddlewareInterface::class);
    });

    it('grants access when a policy evaluates to true', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('evaluate')
            ->with($actor, $resource, 'read', [])
            ->andReturn(true);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'read', [])
            ->andReturn(new PoliciesCollection($policy));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $abac = new AbacMiddleware($policyProvider);
        $response = $abac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('delegates to handler when no policy evaluates to true', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('evaluate')
            ->with($actor, $resource, 'read', [])
            ->andReturn(false);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'read', [])
            ->andReturn(new PoliciesCollection($policy));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $abac = new AbacMiddleware($policyProvider);
        $response = $abac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('delegates to handler when there are no policies', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'read', [])
            ->andReturn(new PoliciesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $abac = new AbacMiddleware($policyProvider);
        $response = $abac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('grants access when any policy evaluates to true', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy1->shouldReceive('evaluate')
            ->with($actor, $resource, 'write', ['key' => 'value'])
            ->andReturn(false);

        $policy2 = Mockery::mock(PolicyInterface::class);
        $policy2->shouldReceive('evaluate')
            ->with($actor, $resource, 'write', ['key' => 'value'])
            ->andReturn(true);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'write', ['key' => 'value'])
            ->andReturn(new PoliciesCollection($policy1, $policy2));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');
        $request->shouldReceive('getContext')->andReturn(['key' => 'value']);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $abac = new AbacMiddleware($policyProvider);
        $response = $abac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });
});
