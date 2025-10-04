<?php

use jschreuder\MiddleAuth\Abac\PoliciesCollection;
use jschreuder\MiddleAuth\Abac\PolicyInterface;

afterEach(function () {
    Mockery::close();
});

describe('PoliciesCollection', function () {
    it('can be created with policies', function () {
        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy2 = Mockery::mock(PolicyInterface::class);

        $collection = new PoliciesCollection($policy1, $policy2);

        expect($collection)->toBeInstanceOf(PoliciesCollection::class)
            ->and(iterator_to_array($collection))->toBe([$policy1, $policy2]);
    });

    it('can be created empty', function () {
        $collection = new PoliciesCollection();

        expect($collection->isEmpty())->toBeTrue();
    });

    it('can be iterated', function () {
        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy2 = Mockery::mock(PolicyInterface::class);

        $collection = new PoliciesCollection($policy1, $policy2);
        $result = [];

        foreach ($collection as $policy) {
            $result[] = $policy;
        }

        expect($result)->toBe([$policy1, $policy2]);
    });

    it('can be counted', function () {
        $policy1 = Mockery::mock(PolicyInterface::class);
        $policy2 = Mockery::mock(PolicyInterface::class);
        $policy3 = Mockery::mock(PolicyInterface::class);

        $collection = new PoliciesCollection($policy1, $policy2, $policy3);

        expect($collection->count())->toBe(3);
    });
});
