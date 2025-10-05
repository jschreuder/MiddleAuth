<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationHandler;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\Basic\DenyAllMiddleware;

describe('Basic\DenyAllMiddleware', function () {
    it('can be instantiated', function () {
        $middleware = new DenyAllMiddleware();

        expect($middleware)->toBeInstanceOf(DenyAllMiddleware::class);
    });

    it('will deny every request', function () {
        $middleware = new DenyAllMiddleware();
        $request = new AuthorizationRequest(
            new AuthorizationEntity('user', '123'),
            new AuthorizationEntity('resource', '456'),
            'action',
            []
        );
        $handler = new AuthorizationHandler(new SplQueue);
        $response = $middleware->process($request, $handler);

        expect($response->isPermitted())->toBe(false);
    });
});
