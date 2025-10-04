<?php

use jschreuder\MiddleAuth\Basic\AccessControlMiddleware;
use jschreuder\MiddleAuth\Acl\AccessControlList;
use jschreuder\MiddleAuth\Acl\BasicAclEntry;
use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('AccessControlMiddleware with ACL', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('permits access when ACL permits', function () {
        $subject = new AuthorizationEntity('user', '123');
        $resource = new AuthorizationEntity('order', '567');

        // Create ACL entry that matches our test case
        $aclEntry = new BasicAclEntry('user::123', 'order::567', 'view', null);
        $acl = new AccessControlList($aclEntry);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldNotReceive('handle');

        $middleware = new AccessControlMiddleware($acl);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeTrue();
        expect($response->getReason())->toContain('AccessControlList');
    });

    it('denies access when ACL denies', function () {
        $subject = new AuthorizationEntity('guestuser', '234');
        $resource = new AuthorizationEntity('admin', 'settings');

        // Create ACL with entry that doesn't match
        $aclEntry = new BasicAclEntry('user::123', 'order::567', 'view', null);
        $acl = new AccessControlList($aclEntry);

        $request = new AuthorizationRequest($subject, $resource, 'view', []);

        $deniedResponse = Mockery::mock(AuthorizationResponseInterface::class);
        $deniedResponse->shouldReceive('isPermitted')->andReturn(false);
        $deniedResponse->shouldReceive('getReason')->andReturn('Denied by handler');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($deniedResponse);

        $middleware = new AccessControlMiddleware($acl);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBeFalse();
        expect($response->getReason())->toBe('Denied by handler');
    });
});