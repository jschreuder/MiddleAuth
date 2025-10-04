<?php

use jschreuder\MiddleAuth\Abac\AbacMiddleware;
use jschreuder\MiddleAuth\Abac\AttributeBasedAccessControlInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AbacMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ABAC permits', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('document');
        $resource->shouldReceive('getId')->andReturn('567');

        $abac = Mockery::mock(AttributeBasedAccessControlInterface::class);
        $abac->shouldReceive('hasAccess')
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

        $middleware = new AbacMiddleware($abac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toBe('Checked against ABAC');
    });

    it('denies access when ABAC denies', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('234');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('restricted');
        $resource->shouldReceive('getId')->andReturn('settings');

        $abac = Mockery::mock(AttributeBasedAccessControlInterface::class);
        $abac->shouldReceive('hasAccess')
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

        $middleware = new AbacMiddleware($abac);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });
});
