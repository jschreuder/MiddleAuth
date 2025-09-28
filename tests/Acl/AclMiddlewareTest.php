<?php

use jschreuder\MiddleAuth\Acl\AclMiddleware;
use jschreuder\MiddleAuth\Acl\AccessControlListInterface;
use jschreuder\MiddleAuth\Acl\EntityStringifierInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AclMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ACL permits', function () {
        $acl = Mockery::mock(AccessControlListInterface::class);
        $acl->shouldReceive('hasAccess')
            ->once()
            ->with('user::123', 'order::567', 'view', [])
            ->andReturn(true);
        
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');
        
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('order');
        $resource->shouldReceive('getId')->andReturn('567');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->once()->andReturn($subject);
        $request->shouldReceive('getResource')->once()->andReturn($resource);
        $request->shouldReceive('getAction')->once()->andReturn('view');
        $request->shouldReceive('getContext')->once()->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $middleware = new AclMiddleware($acl);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toBe('Checked against ACL');
    });

    it('denies access when ACL denies', function () {
        $acl = Mockery::mock(AccessControlListInterface::class);
        $acl->shouldReceive('hasAccess')
            ->once()
            ->with('guestuser::234', 'admin::settings', 'view', [])
            ->andReturn(false);
        
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('guestuser');
        $subject->shouldReceive('getId')->andReturn('234');
        
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('admin');
        $resource->shouldReceive('getId')->andReturn('settings');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->once()->andReturn($subject);
        $request->shouldReceive('getResource')->once()->andReturn($resource);
        $request->shouldReceive('getAction')->once()->andReturn('view');
        $request->shouldReceive('getContext')->once()->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn(Mockery::mock(AuthorizationResponseInterface::class, [
                'isPermitted' => false,
                'getReason' => 'Denied by handler',
            ]));

        $middleware = new AclMiddleware($acl);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });

    it('uses custom entity stringifier when provided', function () {
        $acl = Mockery::mock(AccessControlListInterface::class);
        $acl->shouldReceive('hasAccess')
            ->once()
            ->with('custom_user_1', 'custom_post', 'view', [])
            ->andReturn(true);

        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('custom_user');
        $subject->shouldReceive('getId')->andReturn('1');
        
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('custom');
        $resource->shouldReceive('getId')->andReturn('post');

        $stringifier = Mockery::mock(EntityStringifierInterface::class);
        $stringifier->shouldReceive('stringifyEntity')
            ->once()->with($subject)->andReturn('custom_user_1');
        $stringifier->shouldReceive('stringifyEntity')
            ->once()->with($resource)->andReturn('custom_post');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->once()->andReturn($subject);
        $request->shouldReceive('getResource')->once()->andReturn($resource);
        $request->shouldReceive('getAction')->once()->andReturn('view');
        $request->shouldReceive('getContext')->once()->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $middleware = new AclMiddleware($acl, $stringifier);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });
});