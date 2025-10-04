<?php

use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\Rbac\RbacMiddleware;
use jschreuder\MiddleAuth\Rbac\RoleProviderInterface;
use jschreuder\MiddleAuth\Rbac\RoleInterface;
use jschreuder\MiddleAuth\Rbac\PermissionInterface;
use jschreuder\MiddleAuth\Rbac\PermissionsCollection;
use jschreuder\MiddleAuth\Rbac\RolesCollection;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;

describe('Rbac\RbacMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('implements AuthorizationMiddlewareInterface', function () {
        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $rbac = new RbacMiddleware($roleProvider);
        expect($rbac)->toBeInstanceOf(AuthorizationMiddlewareInterface::class);
    });

    it('grants access when a permission matches all conditions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $permission->shouldReceive('matchesContext')->with($actor, $resource, 'read', [])->andReturn(true);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('delegates to handler when no permission matches all conditions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('delegates to handler when actor has no roles', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('grants access when any permission from any role matches', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission1->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $permission2 = Mockery::mock(PermissionInterface::class);
        $permission2->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission2->shouldReceive('matchesAction')->with('write')->andReturn(true);
        $permission2->shouldReceive('matchesContext')->with($actor, $resource, 'write', ['key' => 'value'])->andReturn(true);

        $role1 = Mockery::mock(RoleInterface::class);
        $role1->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission1));

        $role2 = Mockery::mock(RoleInterface::class);
        $role2->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission2));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role1, $role2));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');
        $request->shouldReceive('getContext')->andReturn(['key' => 'value']);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });
});
