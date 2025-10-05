<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\AclEntriesCollection;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\Acl\AclEntryInterface;
use jschreuder\MiddleAuth\Acl\AclMiddleware;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;

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
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with($user)->andReturn(true);
        $aclEntry->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $aclEntry->shouldReceive('matchesAction')->with('read')->andReturn(true);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry));
        $response = $acl->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('delegates to handler when no AclEntry matches all conditions', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with($user)->andReturn(false);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->with($request)->andReturn($handlerResponse);

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry));
        $response = $acl->process($request, $handler);

        expect($response)->toBe($handlerResponse);
    });

    it('grants access when any AclEntry matches all conditions with multiple entries', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry1->shouldReceive('matchesActor')->with($user)->andReturn(false);

        $aclEntry2 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2->shouldReceive('matchesActor')->with($user)->andReturn(true);
        $aclEntry2->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $aclEntry2->shouldReceive('matchesAction')->with('read')->andReturn(true);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry1, $aclEntry2));
        $response = $acl->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('logs debug message when evaluating request', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('file');
        $resource->shouldReceive('getId')->andReturn('456');

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->andReturn(false);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('ACL middleware evaluating request', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'file',
                'resource_id' => '456',
                'action' => 'read',
                'acl_entries_count' => 1,
            ]);
        $logger->shouldReceive('debug')
            ->once()
            ->with('No ACL entries matched, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry), $logger);
        $acl->process($request, $handler);
    });

    it('logs debug message when ACL entry matches', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('file');
        $resource->shouldReceive('getId')->andReturn('456');

        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with($user)->andReturn(true);
        $aclEntry->shouldReceive('matchesResource')->with($resource)->andReturn(true);
        $aclEntry->shouldReceive('matchesAction')->with('write')->andReturn(true);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('ACL middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('ACL entry matched', [
                'entry_index' => 0,
                'subject_type' => 'user',
                'subject_id' => '123',
                'action' => 'write',
            ]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $acl = new AclMiddleware(new AclEntriesCollection($aclEntry), $logger);
        $response = $acl->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
    });

    it('logs debug message when delegating to next handler', function () {
        $user = Mockery::mock(AuthorizationEntityInterface::class);
        $user->shouldReceive('getType')->andReturn('user');
        $user->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('file');
        $resource->shouldReceive('getId')->andReturn('456');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($user);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');
        $request->shouldReceive('getContext')->andReturn([]);

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->with('ACL middleware evaluating request', Mockery::any())->once();
        $logger->shouldReceive('debug')
            ->once()
            ->with('No ACL entries matched, delegating to next handler');

        $handlerResponse = Mockery::mock(\jschreuder\MiddleAuth\AuthorizationResponseInterface::class);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($handlerResponse);

        $acl = new AclMiddleware(new AclEntriesCollection(), $logger);
        $acl->process($request, $handler);
    });

    it('creates default logger when none is provided', function () {
        $acl = new AclMiddleware(new AclEntriesCollection());
        expect($acl)->toBeInstanceOf(AclMiddleware::class);
    });
});
