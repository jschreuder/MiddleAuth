<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationPipeline;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

describe('Basic\AuthorizationPipeline', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('should throw exception when pipeline is empty', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);

        $pipeline->process(Mockery::mock(AuthorizationRequestInterface::class));
    })->throws(\RuntimeException::class, 'Pipeline is empty, no handlers to process.');

    it('should return new instance with handler added', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);

        $newPipeline = $pipeline->withHandler($handler);

        $this->assertNotSame($pipeline, $newPipeline);
        $this->assertInstanceOf(AuthorizationPipeline::class, $newPipeline);
    });

    it('should not modify original pipeline when adding handler', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);

        $pipeline->withHandler($handler);

        // Original pipeline should still be empty
        expect(fn() => $pipeline->process(Mockery::mock(AuthorizationRequestInterface::class)))
            ->toThrow(\RuntimeException::class, 'Pipeline is empty, no handlers to process.');
    });

    it('should process first handler in queue', function () {
        $queue = new \SplQueue();
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $queue->enqueue($handler);

        $pipeline = new AuthorizationPipeline($queue);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $pipeline->process($request);

        $this->assertSame($response, $result);
    });

    it('should process handlers in FIFO order', function () {
        $queue = new \SplQueue();
        $handler1 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler2 = Mockery::mock(AuthorizationHandlerInterface::class);
        $queue->enqueue($handler1);
        $queue->enqueue($handler2);

        $pipeline = new AuthorizationPipeline($queue);
        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        // First handler should be called (FIFO)
        $handler1->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $pipeline->process($request);

        $this->assertSame($response, $result);
    });

    it('should allow chaining multiple handlers', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);

        $handler1 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler2 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler3 = Mockery::mock(AuthorizationHandlerInterface::class);

        $newPipeline = $pipeline
            ->withHandler($handler1)
            ->withHandler($handler2)
            ->withHandler($handler3);

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $response = Mockery::mock(AuthorizationResponseInterface::class);

        $handler1->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $newPipeline->process($request);

        $this->assertSame($response, $result);
    });
});
