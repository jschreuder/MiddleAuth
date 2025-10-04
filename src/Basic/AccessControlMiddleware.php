<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AccessControlInterface;
use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class AccessControlMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private AccessControlInterface $accessControl
    )
    {
    }

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        if ($this->accessControl->hasAccess(
            $request->getSubject(),
            $request->getResource(),
            $request->getAction(),
            $request->getContext()
        )) {
            return new AuthorizationResponse(
                true,
                'Access granted by ' . $this->accessControl::class,
                self::class
            );
        }

        return $handler->handle($request);
    }
}
