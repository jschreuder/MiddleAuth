<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Designed as an analogue to PSR-15's RequestHandlerInterface
 */
interface AuthorizationHandlerInterface
{
    public function handle(AuthorizationRequestInterface $request): AuthorizationResponseInterface;
}
