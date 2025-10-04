<?php

use jschreuder\MiddleAuth\Abac\BasicPolicy;
use jschreuder\MiddleAuth\Abac\AccessEvaluatorInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

afterEach(function () {
    Mockery::close();
});

describe('BasicPolicy', function () {
    it('returns the description', function () {
        $evaluator = Mockery::mock(AccessEvaluatorInterface::class);
        $policy = new BasicPolicy($evaluator, 'Allow all access');
        expect($policy->getDescription())->toBe('Allow all access');
    });

    it('evaluates using the provided access evaluator', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $evaluator = Mockery::mock(AccessEvaluatorInterface::class);
        $evaluator->shouldReceive('hasAccess')
            ->once()
            ->with($actor, $resource, 'read', ['key' => 'value'])
            ->andReturn(true);

        $policy = new BasicPolicy($evaluator, 'Test policy');
        $result = $policy->evaluate($actor, $resource, 'read', ['key' => 'value']);

        expect($result)->toBeTrue();
    });

    it('returns false when evaluator returns false', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $evaluator = Mockery::mock(AccessEvaluatorInterface::class);
        $evaluator->shouldReceive('hasAccess')
            ->once()
            ->with($actor, $resource, 'write', [])
            ->andReturn(false);

        $policy = new BasicPolicy($evaluator, 'Deny policy');
        $result = $policy->evaluate($actor, $resource, 'write', []);

        expect($result)->toBeFalse();
    });
});
