<?php

use jschreuder\MiddleAuth\AccessControlInterface;
use jschreuder\MiddleAuth\Abac\AttributeBasedAccessControl;
use jschreuder\MiddleAuth\Abac\PolicyProviderInterface;
use jschreuder\MiddleAuth\Abac\PolicyInterface;
use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

describe('Abac\AttributeBasedAccessControl', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('implements AccessControlInterface', function () {
        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $abac = new AttributeBasedAccessControl($policyProvider);
        expect($abac)->toBeInstanceOf(AccessControlInterface::class);
    });

    it('returns true when a policy evaluates to true', function () {
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

        $abac = new AttributeBasedAccessControl($policyProvider);
        $result = $abac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeTrue();
    });

    it('returns false when no policy evaluates to true', function () {
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

        $abac = new AttributeBasedAccessControl($policyProvider);
        $result = $abac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeFalse();
    });

    it('returns false when there are no policies', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policyProvider = Mockery::mock(PolicyProviderInterface::class);
        $policyProvider->shouldReceive('getPolicies')
            ->with($actor, $resource, 'read', [])
            ->andReturn(new PoliciesCollection());

        $abac = new AttributeBasedAccessControl($policyProvider);
        $result = $abac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeFalse();
    });

    it('returns true when any policy evaluates to true', function () {
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

        $abac = new AttributeBasedAccessControl($policyProvider);
        $result = $abac->hasAccess($actor, $resource, 'write', ['key' => 'value']);

        expect($result)->toBeTrue();
    });
});
