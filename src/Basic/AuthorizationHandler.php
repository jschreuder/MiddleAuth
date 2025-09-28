<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class AuthorizationHandler implements AuthorizationHandlerInterface
{
    private \SplStack $stack;
    private bool $called = false;

    public function __construct(\SplStack $stack)
    {
        $this->stack = $stack;
    }

    public function handle(AuthorizationRequestInterface $request): AuthorizationResponseInterface
    {
        if ($this->stack->count() === 0) {
            throw new \RuntimeException('No more handlers to call on.');
        }
        if ($this->called) {
            throw new \RuntimeException('Already processed, cannot be ran twice.');
        }

        /** @var  AuthorizationMiddlewareInterface $next */
        $next = $this->stack->pop();
        $this->called = true;

        return $next->process($request, new self($this->stack));
    }
}
