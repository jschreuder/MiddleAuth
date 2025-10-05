<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationHandler;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('Basic\AuthorizationHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('throws an exception when no handlers are left in queue', function () {
        $queue = new \SplQueue();
        $handler = new AuthorizationHandler($queue);

        $handler->handle(Mockery::mock(AuthorizationRequestInterface::class));
    })->throws(\RuntimeException::class, 'No more handlers to call on.');

    it('throws an exception when handler is called twice', function () {
        $queue = new \SplQueue();
        $queue->enqueue(Mockery::mock(AuthorizationMiddlewareInterface::class));
        $queue->enqueue(Mockery::mock(AuthorizationMiddlewareInterface::class));

        $handler = new AuthorizationHandler($queue);
        $request = Mockery::mock(AuthorizationRequestInterface::class);

        // First call should work
        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $queue->bottom()->shouldReceive('process')->once()->andReturn($response);

        $handler->handle($request);

        // Second call should throw
        $handler->handle($request);
    })->throws(\RuntimeException::class, 'Already processed, cannot be ran twice.');

    it('processes middleware and return response', function () {
        $queue = new \SplQueue();
        $middleware = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $queue->enqueue($middleware);

        $handler = new AuthorizationHandler($queue);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $middleware->shouldReceive('process')
            ->once()
            ->with($request, Mockery::type(AuthorizationHandler::class))
            ->andReturn($response);

        $result = $handler->handle($request);

        expect($result)->toBe($response);
    });

    it('creates new handler instance with remaining queue', function () {
        $queue = new \SplQueue();
        $middleware1 = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $middleware2 = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $queue->enqueue($middleware1);
        $queue->enqueue($middleware2);

        $handler = new AuthorizationHandler($queue);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $middleware1->shouldReceive('process')
            ->once()
            ->with($request, Mockery::on(function ($handler) use ($queue, $middleware2) {
                // Verify that the new handler has the remaining queue
                $reflection = new ReflectionClass($handler);
                $property = $reflection->getProperty('queue');
                $property->setAccessible(true);
                $handlerQueue = $property->getValue($handler);

                return $handlerQueue->count() === 1 &&
                       $handlerQueue->bottom() === $middleware2;
            }))
            ->andReturn($response);

        $handler->handle($request);
    });
});