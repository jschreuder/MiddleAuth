<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationEntity;
use jschreuder\MiddleAuth\Basic\AuthorizationHandler;
use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\Basic\DenyAllMiddleware;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;

describe('Basic\DenyAllMiddleware', function () {
    afterEach(function () {
        Mockery::close();
    });
    
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

    it('logs denial message when logger is injected', function () {
        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with(
                'DenyAllMiddleware rejecting request - no authorization rules matched',
                [
                    'subject_type' => 'user',
                    'subject_id' => '123',
                    'resource_type' => 'resource',
                    'resource_id' => '456',
                    'action' => 'action',
                ]
            );

        $middleware = new DenyAllMiddleware($logger);
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
