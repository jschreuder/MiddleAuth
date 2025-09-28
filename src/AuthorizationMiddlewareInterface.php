<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Designed as an analogue to PSR-15's MiddlewareInterface
 */
interface AuthorizationMiddlewareInterface
{
    public function process(AuthorizationRequestInterface $request, AuthorizationHandlerInterface $handler): AuthorizationResponseInterface;
}
