<?php

use jschreuder\MiddleAuth\Abac\BasicPolicy;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

afterEach(function () {
    Mockery::close();
});

describe('BasicPolicy', function () {
    it('returns the description', function () {
        $policy = new BasicPolicy(fn() => true, 'Allow all access');
        expect($policy->getDescription())->toBe('Allow all access');
    });

    it('evaluates using the provided callable', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $evaluator = function ($a, $r, $action, $context) use ($actor, $resource) {
            expect($a)->toBe($actor);
            expect($r)->toBe($resource);
            expect($action)->toBe('read');
            expect($context)->toBe(['key' => 'value']);
            return true;
        };

        $policy = new BasicPolicy($evaluator, 'Test policy');
        $result = $policy->evaluate($actor, $resource, 'read', ['key' => 'value']);

        expect($result)->toBeTrue();
    });

    it('returns false when evaluator returns false', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $policy = new BasicPolicy(fn() => false, 'Deny policy');
        $result = $policy->evaluate($actor, $resource, 'write', []);

        expect($result)->toBeFalse();
    });

    it('can use attributes to make decisions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getAttributes')->andReturn(['department' => 'engineering']);

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getAttributes')->andReturn(['owner_department' => 'engineering']);

        $evaluator = function ($a, $r, $action, $context) {
            return $a->getAttributes()['department'] === $r->getAttributes()['owner_department'];
        };

        $policy = new BasicPolicy($evaluator, 'Same department access');
        $result = $policy->evaluate($actor, $resource, 'read', []);

        expect($result)->toBeTrue();
    });

    it('throws exception when evaluator is not callable', function () {
        expect(fn() => new BasicPolicy('not-callable', 'Invalid'))
            ->toThrow(TypeError::class);
    });

    it('throws exception when evaluator has wrong number of parameters', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $b) {
            return true;
        };

        expect(fn() => new BasicPolicy($evaluator, 'Invalid'))
            ->toThrow(InvalidArgumentException::class, 'Evaluator must accept exactly 4 parameters');
    });

    it('throws exception when first parameter has wrong type hint', function () {
        $evaluator = function (string $a, AuthorizationEntityInterface $r, string $action, array $context) {
            return true;
        };

        expect(fn() => new BasicPolicy($evaluator, 'Invalid'))
            ->toThrow(InvalidArgumentException::class, 'First parameter must be AuthorizationEntityInterface');
    });

    it('throws exception when second parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, string $r, string $action, array $context) {
            return true;
        };

        expect(fn() => new BasicPolicy($evaluator, 'Invalid'))
            ->toThrow(InvalidArgumentException::class, 'Second parameter must be AuthorizationEntityInterface');
    });

    it('throws exception when third parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, int $action, array $context) {
            return true;
        };

        expect(fn() => new BasicPolicy($evaluator, 'Invalid'))
            ->toThrow(InvalidArgumentException::class, 'Third parameter must be string');
    });

    it('throws exception when fourth parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, string $action, string $context) {
            return true;
        };

        expect(fn() => new BasicPolicy($evaluator, 'Invalid'))
            ->toThrow(InvalidArgumentException::class, 'Fourth parameter must be array');
    });

    it('accepts evaluator with correct type hints', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, string $action, array $context): bool {
            return true;
        };

        $policy = new BasicPolicy($evaluator, 'Valid policy');
        expect($policy->getDescription())->toBe('Valid policy');
    });
});
