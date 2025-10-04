<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class AuthorizationHandler implements AuthorizationHandlerInterface
{
    private \SplQueue $queue;
    private bool $called = false;

    public function __construct(\SplQueue $queue)
    {
        $this->queue = $queue;
    }

    public function handle(AuthorizationRequestInterface $request): AuthorizationResponseInterface
    {
        if ($this->queue->count() === 0) {
            throw new \RuntimeException('No more handlers to call on.');
        }
        if ($this->called) {
            throw new \RuntimeException('Already processed, cannot be ran twice.');
        }

        /** @var  AuthorizationMiddlewareInterface $next */
        $next = $this->queue->dequeue();
        $this->called = true;

        return $next->process($request, new self($this->queue));
    }
}
