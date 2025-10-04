<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Provides an authorization stack of middlewares to deal with determining
 * authorization of a given subject for a resource. Implementations should
 * be immutable and return new instances when changed.
 */
interface AuthorizationPipelineInterface
{
    public function withHandler(AuthorizationHandlerInterface $handler): self;
    public function process(AuthorizationRequestInterface $request): AuthorizationResponseInterface;
}
