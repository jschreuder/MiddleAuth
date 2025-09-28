<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationHandler;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use Mockery as m;

describe('Basic\AuthorizationHandler', function () {
    afterEach(function () {
        m::close();
    });

    it('should throw exception when no handlers are left in stack', function () {
        $stack = new \SplStack();
        $handler = new AuthorizationHandler($stack);

        $handler->handle(m::mock(AuthorizationRequestInterface::class));
    })->throws(\RuntimeException::class, 'No more handlers to call on.');

    it('should throw exception when handler is called twice', function () {
        $stack = new \SplStack();
        $stack->push(m::mock(AuthorizationMiddlewareInterface::class));
        $stack->push(m::mock(AuthorizationMiddlewareInterface::class));

        $handler = new AuthorizationHandler($stack);
        $request = m::mock(AuthorizationRequestInterface::class);

        // First call should work
        $response = m::mock(AuthorizationResponseInterface::class);
        $stack->top()->shouldReceive('process')->once()->andReturn($response);

        $handler->handle($request);

        // Second call should throw
        $handler->handle($request);
    })->throws(\RuntimeException::class, 'Already processed, cannot be ran twice.');

    it('should process middleware and return response', function () {
        $stack = new \SplStack();
        $middleware = m::mock(AuthorizationMiddlewareInterface::class);
        $stack->push($middleware);

        $handler = new AuthorizationHandler($stack);
        $request = m::mock(AuthorizationRequestInterface::class);
        $response = m::mock(AuthorizationResponseInterface::class);

        $middleware->shouldReceive('process')
            ->once()
            ->with($request, m::type(AuthorizationHandler::class))
            ->andReturn($response);

        $result = $handler->handle($request);

        $this->assertSame($response, $result);
    });

    it('should create new handler instance with remaining stack', function () {
        $stack = new \SplStack();
        $middleware1 = m::mock(AuthorizationMiddlewareInterface::class);
        $middleware2 = m::mock(AuthorizationMiddlewareInterface::class);
        $stack->push($middleware1);
        $stack->push($middleware2);

        $handler = new AuthorizationHandler($stack);
        $request = m::mock(AuthorizationRequestInterface::class);
        $response = m::mock(AuthorizationResponseInterface::class);

        $middleware2->shouldReceive('process')
            ->once()
            ->with($request, m::on(function ($handler) use ($stack, $middleware1) {
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