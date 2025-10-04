<?php

use jschreuder\MiddleAuth\Basic\AccessControlMiddleware;
use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\Rbac\RoleBasedAccessControl;
use jschreuder\MiddleAuth\Rbac\BasicRoleProvider;
use jschreuder\MiddleAuth\Rbac\BasicRole;
use jschreuder\MiddleAuth\Rbac\BasicPermission;
use jschreuder\MiddleAuth\Rbac\RolesCollection;
use jschreuder\MiddleAuth\Rbac\PermissionsCollection;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AccessControlMiddleware with RBAC', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when RBAC permits', function () {
        $subject = new AuthorizationEntity('user', '123');
        $resource = new AuthorizationEntity('order', '567');

        // Create permission that allows viewing orders
        $permission = new BasicPermission('order::567', 'view');
        $permissions = new PermissionsCollection($permission);

        // Create role with the permission
        $role = new BasicRole('viewer', $permissions);
        $roles = new RolesCollection($role);

        // Map the subject to their roles
        $roleProvider = new BasicRoleProvider(['user::123' => $roles]);
        $rbac = new RoleBasedAccessControl($roleProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $middleware = new AccessControlMiddleware($rbac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toContain('RoleBasedAccessControl');
    });

    it('denies access when RBAC denies', function () {
        $subject = new AuthorizationEntity('guestuser', '234');
        $resource = new AuthorizationEntity('admin', 'settings');

        // Create role provider with no roles for this user
        $roleProvider = new BasicRoleProvider([]);
        $rbac = new RoleBasedAccessControl($roleProvider);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $deniedResponse = Mockery::mock(AuthorizationResponseInterface::class);
        $deniedResponse->shouldReceive('isPermitted')->andReturn(false);
        $deniedResponse->shouldReceive('getReason')->andReturn('Denied by handler');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($deniedResponse);

        $middleware = new AccessControlMiddleware($rbac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });
});
