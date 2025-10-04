<?php

use jschreuder\MiddleAuth\Rbac\RoleInterface;
use jschreuder\MiddleAuth\Rbac\RolesCollection;

describe('Rbac\RolesCollection', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('can be created empty', function () {
        $collection = new RolesCollection();

        expect($collection->isEmpty())->toBeTrue()
            ->and($collection->count())->toBe(0);
    });

    it ('is countable', function () {
        $role1 = Mockery::mock(RoleInterface::class);
        $role2 = Mockery::mock(RoleInterface::class);
        $role3 = Mockery::mock(RoleInterface::class);
        $collection = new RolesCollection($role1, $role2, $role3);

        expect(count($collection))->toBe(3);
    });

    it('can be created with roles', function () {
        $role1 = Mockery::mock(RoleInterface::class);
        $role2 = Mockery::mock(RoleInterface::class);

        $collection = new RolesCollection($role1, $role2);

        expect($collection->isEmpty())->toBeFalse()
            ->and($collection->count())->toBe(2);
    });

    it('is iterable', function () {
        $role1 = Mockery::mock(RoleInterface::class);
        $role2 = Mockery::mock(RoleInterface::class);

        $collection = new RolesCollection($role1, $role2);

        $iterations = 0;
        foreach ($collection as $role) {
            expect($role)->toBeInstanceOf(RoleInterface::class);
            $iterations++;
        }

        expect($iterations)->toBe(2);
    });

    it('returns array of roles', function () {
        $role1 = Mockery::mock(RoleInterface::class);
        $role2 = Mockery::mock(RoleInterface::class);

        $collection = new RolesCollection($role1, $role2);

        $array = $collection->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveCount(2)
            ->and($array[0])->toBe($role1)
            ->and($array[1])->toBe($role2);
    });
});
