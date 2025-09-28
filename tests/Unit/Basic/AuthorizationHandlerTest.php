<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationHandler;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('Basic\AuthorizationHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('should throw exception when no handlers are left in stack', function () {
        $stack = new \SplStack();
        $handler = new AuthorizationHandler($stack);

        $handler->handle(Mockery::mock(AuthorizationRequestInterface::class));
    })->throws(\RuntimeException::class, 'No more handlers to call on.');

    it('should throw exception when handler is called twice', function () {
        $stack = new \SplStack();
        $stack->push(Mockery::mock(AuthorizationMiddlewareInterface::class));
        $stack->push(Mockery::mock(AuthorizationMiddlewareInterface::class));

        $handler = new AuthorizationHandler($stack);
        $request = Mockery::mock(AuthorizationRequestInterface::class);

        // First call should work
        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $stack->top()->shouldReceive('process')->once()->andReturn($response);

        $handler->handle($request);

        // Second call should throw
        $handler->handle($request);
    })->throws(\RuntimeException::class, 'Already processed, cannot be ran twice.');

    it('should process middleware and return response', function () {
        $stack = new \SplStack();
        $middleware = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $stack->push($middleware);

        $handler = new AuthorizationHandler($stack);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $middleware->shouldReceive('process')
            ->once()
            ->with($request, Mockery::type(AuthorizationHandler::class))
            ->andReturn($response);

        $result = $handler->handle($request);

        $this->assertSame($response, $result);
    });

    it('should create new handler instance with remaining stack', function () {
        $stack = new \SplStack();
        $middleware1 = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $middleware2 = Mockery::mock(AuthorizationMiddlewareInterface::class);
        $stack->push($middleware1);
        $stack->push($middleware2);

        $handler = new AuthorizationHandler($stack);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $middleware2->shouldReceive('process')
            ->once()
            ->with($request, Mockery::on(function ($handler) use ($stack, $middleware1) {
                // Verify that the new handler has the remaining stack
                $reflection = new ReflectionClass($handler);
                $property = $reflection->getProperty('stack');
                $property->setAccessible(true);
                $handlerStack = $property->getValue($handler);

                return $handlerStack->count() === 1 &&
                       $handlerStack->top() === $middleware1;
            }))
            ->andReturn($response);

        $handler->handle($request);
    });
});