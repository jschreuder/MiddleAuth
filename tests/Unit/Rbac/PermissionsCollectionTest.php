<?php

use jschreuder\MiddleAuth\Rbac\PermissionInterface;
use jschreuder\MiddleAuth\Rbac\PermissionsCollection;

describe('Rbac\PermissionsCollection', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('can be created empty', function () {
        $collection = new PermissionsCollection();

        expect($collection->isEmpty())->toBeTrue()
            ->and($collection->count())->toBe(0);
    });

    it('can be created with permissions', function () {
        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission2 = Mockery::mock(PermissionInterface::class);

        $collection = new PermissionsCollection($permission1, $permission2);

        expect($collection->isEmpty())->toBeFalse()
            ->and($collection->count())->toBe(2);
    });

    it('is iterable', function () {
        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission2 = Mockery::mock(PermissionInterface::class);

        $collection = new PermissionsCollection($permission1, $permission2);

        $iterations = 0;
        foreach ($collection as $permission) {
            expect($permission)->toBeInstanceOf(PermissionInterface::class);
            $iterations++;
        }

        expect($iterations)->toBe(2);
    });

    it('returns array of permissions', function () {
        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission2 = Mockery::mock(PermissionInterface::class);

        $collection = new PermissionsCollection($permission1, $permission2);

        $array = $collection->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveCount(2)
            ->and($array[0])->toBe($permission1)
            ->and($array[1])->toBe($permission2);
    });
});
