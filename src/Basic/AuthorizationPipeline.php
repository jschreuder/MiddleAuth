<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationPipelineInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class AuthorizationPipeline implements AuthorizationPipelineInterface
{
    private \SplQueue $queue;

    public function __construct(\SplQueue $queue)
    {
        $this->queue = $queue;
    }

    public function withHandler(AuthorizationHandlerInterface $handler): self
    {
        $newQueue = clone $this->queue;
        $newQueue->enqueue($handler);
        return new self($newQueue);
    }

    public function process(AuthorizationRequestInterface $request): AuthorizationResponseInterface
    {
        if ($this->queue->count() === 0) {
            throw new \RuntimeException('Pipeline is empty, no handlers to process.');
        }

        $queue = clone $this->queue;
        $handler = $queue->dequeue();

        return $handler->handle($request);
    }
}
