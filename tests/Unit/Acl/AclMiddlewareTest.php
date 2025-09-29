<?php

use jschreuder\MiddleAuth\Acl\AclMiddleware;
use jschreuder\MiddleAuth\Acl\AccessControlListInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AclMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ACL permits', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');
        
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('order');
        $resource->shouldReceive('getId')->andReturn('567');

        $acl = Mockery::mock(AccessControlListInterface::class);
        $acl->shouldReceive('hasAccess')
            ->once()
            ->with($subject, $resource, 'view', [])
            ->andReturn(true);
        
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
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('guestuser');
        $subject->shouldReceive('getId')->andReturn('234');
        
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('admin');
        $resource->shouldReceive('getId')->andReturn('settings');

        $acl = Mockery::mock(AccessControlListInterface::class);
        $acl->shouldReceive('hasAccess')
            ->once()
            ->with($subject, $resource, 'view', [])
            ->andReturn(false);
        
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
});