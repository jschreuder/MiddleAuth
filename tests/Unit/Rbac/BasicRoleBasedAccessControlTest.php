<?php

use jschreuder\MiddleAuth\Rbac\BasicRoleBasedAccessControl;
use jschreuder\MiddleAuth\Rbac\RoleBasedAccessControlInterface;
use jschreuder\MiddleAuth\Rbac\RoleProviderInterface;
use jschreuder\MiddleAuth\Rbac\RoleInterface;
use jschreuder\MiddleAuth\Rbac\PermissionInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

describe('BasicRoleBasedAccessControl', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('implements RoleBasedAccessControlInterface', function () {
        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $rbac = new BasicRoleBasedAccessControl($roleProvider);
        expect($rbac)->toBeInstanceOf(RoleBasedAccessControlInterface::class);
    });

    it('returns true when a permission matches all conditions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $permission->shouldReceive('matchesContext')->with([])->andReturn(true);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getPermissions')->andReturn([$permission]);

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn([$role]);

        $rbac = new BasicRoleBasedAccessControl($roleProvider);
        $result = $rbac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeTrue();
    });

    it('returns false when no permission matches all conditions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getPermissions')->andReturn([$permission]);

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn([$role]);

        $rbac = new BasicRoleBasedAccessControl($roleProvider);
        $result = $rbac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeFalse();
    });

    it('returns false when actor has no roles', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn([]);

        $rbac = new BasicRoleBasedAccessControl($roleProvider);
        $result = $rbac->hasAccess($actor, $resource, 'read');

        expect($result)->toBeFalse();
    });

    it('returns true when any permission from any role matches', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission1->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $permission2 = Mockery::mock(PermissionInterface::class);
        $permission2->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission2->shouldReceive('matchesAction')->with('write')->andReturn(true);
        $permission2->shouldReceive('matchesContext')->with(['key' => 'value'])->andReturn(true);

        $role1 = Mockery::mock(RoleInterface::class);
        $role1->shouldReceive('getPermissions')->andReturn([$permission1]);

        $role2 = Mockery::mock(RoleInterface::class);
        $role2->shouldReceive('getPermissions')->andReturn([$permission2]);

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn([$role1, $role2]);

        $rbac = new BasicRoleBasedAccessControl($roleProvider);
        $result = $rbac->hasAccess($actor, $resource, 'write', ['key' => 'value']);

        expect($result)->toBeTrue();
    });
});
