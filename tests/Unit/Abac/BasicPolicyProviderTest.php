<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Abac\BasicPolicyProvider;
use jschreuder\MiddleAuth\Abac\PolicyInterface;
use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

afterEach(function () {
    Mockery::close();
});

describe('BasicPolicyProvider', function () {
    it('returns all policies', function () {
        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy2 = Mockery::mock(PolicyInterface::class);

        $provider = new BasicPolicyProvider($policy1, $policy2);

        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policies = $provider->getPolicies($actor, $resource, 'read', []);

        expect($policies)->toBeInstanceOf(PoliciesCollection::class)
            ->and(iterator_to_array($policies))->toBe([$policy1, $policy2]);
    });

    it('returns empty collection when no policies', function () {
        $provider = new BasicPolicyProvider();

        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policies = $provider->getPolicies($actor, $resource, 'write', []);

        expect($policies)->toBeInstanceOf(PoliciesCollection::class)
            ->and($policies->isEmpty())->toBeTrue();
    });
});
