<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationPipeline;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use jschreuder\MiddleAuth\Exception\AuthorizationException;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;

describe('Basic\AuthorizationPipeline', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('throws exception when pipeline is empty', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);

        $pipeline->process(Mockery::mock(AuthorizationRequestInterface::class));
    })->throws(AuthorizationException::class, 'Pipeline is empty, no handlers to process.');

    it('returns new instance with handler added', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);

        $newPipeline = $pipeline->withHandler($handler);

        expect($newPipeline)->not->toBe($pipeline);
        expect($newPipeline)->toBeInstanceOf(AuthorizationPipeline::class);
    });

    it('does not modify original pipeline when adding handler', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);

        $pipeline->withHandler($handler);

        // Original pipeline should still be empty
        expect(fn() => $pipeline->process(Mockery::mock(AuthorizationRequestInterface::class)))
            ->toThrow(AuthorizationException::class, 'Pipeline is empty, no handlers to process.');
    });

    it('processes first handler in queue', function () {
        $queue = new \SplQueue();
        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $queue->enqueue($handler);

        $pipeline = new AuthorizationPipeline($queue);

        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('test');
        $response->shouldReceive('getHandler')->andReturn('test');

        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $pipeline->process($request);

        expect($result)->toBe($response);
    });

    it('processes handlers in FIFO order', function () {
        $queue = new \SplQueue();
        $handler1 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler2 = Mockery::mock(AuthorizationHandlerInterface::class);
        $queue->enqueue($handler1);
        $queue->enqueue($handler2);

        $pipeline = new AuthorizationPipeline($queue);

        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('test');
        $response->shouldReceive('getHandler')->andReturn('test');

        // First handler should be called (FIFO)
        $handler1->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $pipeline->process($request);

        expect($result)->toBe($response);
    });

    it('allows chaining multiple handlers', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);

        $handler1 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler2 = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler3 = Mockery::mock(AuthorizationHandlerInterface::class);

        $newPipeline = $pipeline
            ->withHandler($handler1)
            ->withHandler($handler2)
            ->withHandler($handler3);

        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('test');
        $response->shouldReceive('getHandler')->andReturn('test');

        $handler1->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $result = $newPipeline->process($request);

        expect($result)->toBe($response);
    });

    it('logs warning when pipeline is empty', function () {
        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->with('Authorization pipeline is empty, no handlers to process');

        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue, $logger);

        expect(fn() => $pipeline->process(Mockery::mock(AuthorizationRequestInterface::class)))
            ->toThrow(AuthorizationException::class);
    });

    it('logs debug message when processing request', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('post');
        $resource->shouldReceive('getId')->andReturn('456');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('Authorization pipeline processing request', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'post',
                'resource_id' => '456',
                'action' => 'read',
            ]);
        $logger->shouldReceive('info')->once();

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('test');
        $response->shouldReceive('getHandler')->andReturn('handler');

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($response);

        $queue = new \SplQueue();
        $queue->enqueue($handler);
        $pipeline = new AuthorizationPipeline($queue, $logger);

        $pipeline->process($request);
    });

    it('logs info message with PERMIT when access is granted', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('post');
        $resource->shouldReceive('getId')->andReturn('456');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('read');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('Access granted');
        $response->shouldReceive('getHandler')->andReturn('TestHandler');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->once();
        $logger->shouldReceive('info')
            ->once()
            ->with('Authorization decision: PERMIT', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'post',
                'resource_id' => '456',
                'action' => 'read',
                'permitted' => true,
                'reason' => 'Access granted',
                'handler' => 'TestHandler',
            ]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($response);

        $queue = new \SplQueue();
        $queue->enqueue($handler);
        $pipeline = new AuthorizationPipeline($queue, $logger);

        $pipeline->process($request);
    });

    it('logs info message with DENY when access is denied', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('123');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('post');
        $resource->shouldReceive('getId')->andReturn('456');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('write');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(false);
        $response->shouldReceive('getReason')->andReturn('Access denied');
        $response->shouldReceive('getHandler')->andReturn('DenyHandler');

        $logger = Mockery::mock(AuthLoggerInterface::class);
        $logger->shouldReceive('debug')->once();
        $logger->shouldReceive('info')
            ->once()
            ->with('Authorization decision: DENY', [
                'subject_type' => 'user',
                'subject_id' => '123',
                'resource_type' => 'post',
                'resource_id' => '456',
                'action' => 'write',
                'permitted' => false,
                'reason' => 'Access denied',
                'handler' => 'DenyHandler',
            ]);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $handler->shouldReceive('handle')->andReturn($response);

        $queue = new \SplQueue();
        $queue->enqueue($handler);
        $pipeline = new AuthorizationPipeline($queue, $logger);

        $pipeline->process($request);
    });

    it('creates default logger when none is provided', function () {
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue);

        expect($pipeline)->toBeInstanceOf(AuthorizationPipeline::class);
    });

    it('preserves logger when using withHandler', function () {
        $logger = Mockery::mock(AuthLoggerInterface::class);
        $queue = new \SplQueue();
        $pipeline = new AuthorizationPipeline($queue, $logger);

        $handler = Mockery::mock(AuthorizationHandlerInterface::class);
        $newPipeline = $pipeline->withHandler($handler);

        // Verify logger is preserved by attempting to process (which would log)
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $subject->shouldReceive('getType')->andReturn('user');
        $subject->shouldReceive('getId')->andReturn('1');

        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $resource->shouldReceive('getType')->andReturn('resource');
        $resource->shouldReceive('getId')->andReturn('1');

        $request = Mockery::mock(AuthorizationRequestInterface::class);
        $request->shouldReceive('getSubject')->andReturn($subject);
        $request->shouldReceive('getResource')->andReturn($resource);
        $request->shouldReceive('getAction')->andReturn('test');

        $response = Mockery::mock(AuthorizationResponseInterface::class);
        $response->shouldReceive('isPermitted')->andReturn(true);
        $response->shouldReceive('getReason')->andReturn('test');
        $response->shouldReceive('getHandler')->andReturn('test');

        $handler->shouldReceive('handle')->andReturn($response);

        $logger->shouldReceive('debug')->once();
        $logger->shouldReceive('info')->once();

        $newPipeline->process($request);
    });
});
