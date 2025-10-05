<?php

use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\Abac\AbacMiddleware;
use jschreuder\MiddleAuth\Abac\PolicyProviderInterface;
use jschreuder\MiddleAuth\Abac\PolicyInterface;
use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;

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
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('getDescription')->andReturn('test policy');
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
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('getDescription')->andReturn('test policy');
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
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

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
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy1->shouldReceive('getDescription')->andReturn('policy 1');
        $policy1->shouldReceive('evaluate')
            ->with($actor, $resource, 'write', ['key' => 'value'])
            ->andReturn(false);

        $policy2 = Mockery::mock(PolicyInterface::class);
        $policy2->shouldReceive('getDescription')->andReturn('policy 2');
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

    it('logs debug message when evaluating request', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'read', ['context_key' => 'context_value'])
            ->andReturn(new PoliciesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn(['context_key' => 'context_value']);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('ABAC middleware evaluating request', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'document',
                'resource_id' => '456',
                'action' => 'read',
                'policies_count' => 0,
                'context_keys' => ['context_key'],
            ]);
        $logger->shouldReceive('debug')
            ->once()
            ->with('No policies granted access, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $abac = new AbacMiddleware($policyProvider, $logger);
        $abac->process($request, $handler);
    });

    it('logs debug message when evaluating policies', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('getDescription')->andReturn('Test policy description');
        $policy->shouldReceive('evaluate')->andReturn(false);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')->andReturn(new PoliciesCollection($policy));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('ABAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('Evaluating policy', ['policy_description' => 'Test policy description']);
        $logger->shouldReceive('debug')->with('No policies granted access, delegating to next handler')->once();

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $abac = new AbacMiddleware($policyProvider, $logger);
        $abac->process($request, $handler);
    });

    it('logs debug message when policy grants access', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $policy = Mockery::mock(PolicyInterface::class);
        $policy->shouldReceive('getDescription')->andReturn('Granting policy');
        $policy->shouldReceive('evaluate')->andReturn(true);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')->andReturn(new PoliciesCollection($policy));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('ABAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')->with('Evaluating policy', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('Policy granted access', ['policy_description' => 'Granting policy']);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $abac = new AbacMiddleware($policyProvider, $logger);
        $response = $abac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('logs debug message when delegating to next handler', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')->andReturn(new PoliciesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('ABAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('No policies granted access, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $abac = new AbacMiddleware($policyProvider, $logger);
        $abac->process($request, $handler);
    });

    it('creates default logger when none is provided', function () {
        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $abac = new AbacMiddleware($policyProvider);

        expect($abac)->toBeInstanceOf(AbacMiddleware::class);
    });
});
