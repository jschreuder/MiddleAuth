<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class DenyAllMiddleware implements AuthorizationMiddlewareInterface
{
    public function process(
        AuthorizationRequestInterface $request, 
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        return new AuthorizationResponse(false, 'No authorization rule matched', self::class);
    }
}
