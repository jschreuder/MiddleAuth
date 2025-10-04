<?php

use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\Abac\ClosureBasedAccessEvaluator;

afterEach(function () {
    Mockery::close();
});

describe('Abac/ClosureBasedAccessEvaluator', function () {
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

        $accessEvaluator = new ClosureBasedAccessEvaluator($evaluator);
        $result = $accessEvaluator->hasAccess($actor, $resource, 'read', ['key' => 'value']);

        expect($result)->toBeTrue();
    });

    it('returns false when evaluator returns false', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $accessEvaluator = new ClosureBasedAccessEvaluator(fn() => false);
        $result = $accessEvaluator->hasAccess($actor, $resource, 'write', []);

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

        $accessEvaluator = new ClosureBasedAccessEvaluator($evaluator);
        $result = $accessEvaluator->hasAccess($actor, $resource, 'read', []);

        expect($result)->toBeTrue();
    });

    it('throws exception when evaluator is not callable', function () {
        expect(fn() => new ClosureBasedAccessEvaluator('not-callable'))
            ->toThrow(TypeError::class);
    });

    it('throws exception when evaluator has wrong number of parameters', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $b) {
            return true;
        };

        expect(fn() => new ClosureBasedAccessEvaluator($evaluator))
            ->toThrow(InvalidArgumentException::class, 'Evaluator must accept exactly 4 parameters');
    });

    it('throws exception when first parameter has wrong type hint', function () {
        $evaluator = function (string $a, AuthorizationEntityInterface $r, string $action, array $context) {
            return true;
        };

        expect(fn() => new ClosureBasedAccessEvaluator($evaluator))
            ->toThrow(InvalidArgumentException::class, 'First parameter must be AuthorizationEntityInterface');
    });

    it('throws exception when second parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, string $r, string $action, array $context) {
            return true;
        };

        expect(fn() => new ClosureBasedAccessEvaluator($evaluator))
            ->toThrow(InvalidArgumentException::class, 'Second parameter must be AuthorizationEntityInterface');
    });

    it('throws exception when third parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, int $action, array $context) {
            return true;
        };

        expect(fn() => new ClosureBasedAccessEvaluator($evaluator))
            ->toThrow(InvalidArgumentException::class, 'Third parameter must be string');
    });

    it('throws exception when fourth parameter has wrong type hint', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, string $action, string $context) {
            return true;
        };

        expect(fn() => new ClosureBasedAccessEvaluator($evaluator))
            ->toThrow(InvalidArgumentException::class, 'Fourth parameter must be array');
    });

    it('accepts evaluator with correct type hints', function () {
        $evaluator = function (AuthorizationEntityInterface $a, AuthorizationEntityInterface $r, string $action, array $context): bool {
            return true;
        };

        $accessEvaluator = new ClosureBasedAccessEvaluator($evaluator);
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        expect($accessEvaluator->hasAccess($actor, $resource, 'read', []))->toBeTrue();
    });
});
