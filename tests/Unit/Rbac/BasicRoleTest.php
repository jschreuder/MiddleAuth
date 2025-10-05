<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Rbac\BasicRole;
use jschreuder\MiddleAuth\Rbac\PermissionInterface;
use jschreuder\MiddleAuth\Rbac\PermissionsCollection;

describe('BasicRole', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('returns the name', function () {
        $role = new BasicRole('admin', new PermissionsCollection());
        expect($role->getName())->toBe('admin');
    });

    it('returns the permissions', function () {
        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission2 = Mockery::mock(PermissionInterface::class);
        $permissions = new PermissionsCollection($permission1, $permission2);

        $role = new BasicRole('editor', $permissions);
        expect($role->getPermissions())->toBe($permissions);
    });

    it('returns empty collection when no permissions', function () {
        $permissions = new PermissionsCollection();
        $role = new BasicRole('viewer', $permissions);

        expect($role->getPermissions())->toBeInstanceOf(PermissionsCollection::class)
            ->and($role->getPermissions()->isEmpty())->toBeTrue();
    });
});
