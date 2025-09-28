<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Provides an authorization stack of middlewares to deal with determining
 * authorization of a given subject for a resource.
 */
interface AuthorizationStackInterface
{
    public function addHandler(AuthorizationHandlerInterface $handler): self;
    public function process(AuthorizationRequestInterface $request): AuthorizationResponseInterface;
}
