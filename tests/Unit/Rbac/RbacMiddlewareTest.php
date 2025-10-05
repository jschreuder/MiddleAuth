<?php declare(strict_types=1);

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
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;

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
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission->shouldReceive('matchesAction')->with('read')->andReturn(true);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getName')->andReturn('admin');
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('delegates to handler when no permission matches all conditions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getName')->andReturn('admin');
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('delegates to handler when actor has no roles', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('grants access when any permission from any role matches', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $permission1 = Mockery::mock(PermissionInterface::class);
        $permission1->shouldReceive('matchesResource')->with($resource)->andReturn(false);

        $permission2 = Mockery::mock(PermissionInterface::class);
        $permission2->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission2->shouldReceive('matchesAction')->with('write')->andReturn(true);

        $role1 = Mockery::mock(RoleInterface::class);
        $role1->shouldReceive('getName')->andReturn('role1');
        $role1->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission1));

        $role2 = Mockery::mock(RoleInterface::class);
        $role2->shouldReceive('getName')->andReturn('role2');
        $role2->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission2));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role1, $role2));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $rbac = new RbacMiddleware($roleProvider);
        $response = $rbac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('logs debug message when evaluating request', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('RBAC middleware evaluating request', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'document',
                'resource_id' => '456',
                'action' => 'read',
                'roles_count' => 0,
            ]);
        $logger->shouldReceive('debug')
            ->once()
            ->with('No role permissions matched, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider, $logger);
        $rbac->process($request, $handler);
    });

    it('logs debug message when checking role permissions', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->andReturn(false);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getName')->andReturn('admin');
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('RBAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('Checking role permissions', [
                'role_name' => 'admin',
                'permissions_count' => 1,
            ]);
        $logger->shouldReceive('debug')->with('No role permissions matched, delegating to next handler')->once();

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider, $logger);
        $rbac->process($request, $handler);
    });

    it('logs debug message when permission matches', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $permission = Mockery::mock(PermissionInterface::class);
        $permission->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $permission->shouldReceive('matchesAction')->with('write')->andReturn(true);

        $role = Mockery::mock(RoleInterface::class);
        $role->shouldReceive('getName')->andReturn('editor');
        $role->shouldReceive('getPermissions')->andReturn(new PermissionsCollection($permission));

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection($role));

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('RBAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')->with('Checking role permissions', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('Permission matched', [
                'role_name' => 'editor',
                'resource_type' => 'document',
                'action' => 'write',
            ]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $rbac = new RbacMiddleware($roleProvider, $logger);
        $response = $rbac->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('logs debug message when delegating to next handler', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->andReturn('user');
        $actor->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('456');

        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $roleProvider->shouldReceive('getRolesForActor')->with($actor)->andReturn(new RolesCollection());

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($actor);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('RBAC middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('No role permissions matched, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $rbac = new RbacMiddleware($roleProvider, $logger);
        $rbac->process($request, $handler);
    });

    it('creates default logger when none is provided', function () {
        $roleProvider = Mockery::mock(RoleProviderInterface::class);
        $rbac = new RbacMiddleware($roleProvider);

        expect($rbac)->toBeInstanceOf(RbacMiddleware::class);
    });
});
