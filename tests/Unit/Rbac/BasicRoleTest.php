<?php

use jschreuder\MiddleAuth\Rbac\BasicRole;
use jschreuder\MiddleAuth\Rbac\PermissionInterface;

afterEach(function () {
    Mockery::close();
});

describe('BasicRole', function () {
    it('returns the name', function () {
        $role = new BasicRole('admin', []);
        expect($role->getName())->toBe('admin');
    });

    it('returns the permissions', function () {
        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission2 = Mockery::mock(PermissionInterface::class);
        $permissions = [$permission1, $permission2];

        $role = new BasicRole('editor', $permissions);
        expect($role->getPermissions())->toBe($permissions);
    });

    it('returns empty array when no permissions', function () {
        $role = new BasicRole('viewer', []);
        expect($role->getPermissions())->toBeArray()->toBeEmpty();
    });
});
