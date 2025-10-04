<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\AclEntriesCollection;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\Acl\AclEntryInterface;
use jschreuder\MiddleAuth\Acl\AclMiddleware;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;

describe('Acl\AclMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('implements AuthorizationMiddlewareInterface', function () {
        $acl = new AclMiddleware(new AclEntriesCollection());
        expect($acl)->toBeInstanceOf(AuthorizationMiddlewareInterface::class);
    });

    it('can be instantiated with AclEntryInterface objects', function () {
        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry));
        expect($acl)->toBeInstanceOf(AclMiddleware::class);
    });

    it('grants access when an AclEntry matches all conditions', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with($user)->andReturn(true);
        $aclEntry->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $aclEntry->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $aclEntry->shouldReceive('matchesContext')->with($user, $resource, 'read', [])->andReturn(true);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry));
        $response = $acl->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('delegates to handler when no AclEntry matches all conditions', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with($user)->andReturn(false);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry));
        $response = $acl->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('grants access when any AclEntry matches all conditions with multiple entries', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);

        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry1->shouldReceive('matchesActor')->with($user)->andReturn(false);

        $aclEntry2 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2->shouldReceive('matchesActor')->with($user)->andReturn(true);
        $aclEntry2->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $aclEntry2->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $aclEntry2->shouldReceive('matchesContext')->with($user, $resource, 'read', [])->andReturn(true);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry1, $aclEntry2));
        $response = $acl->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });
});
