<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

final class RbacMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private RoleBasedAccessControlInterface $rbac
    )
    {
    }

    public function process(AuthorizationRequestInterface $request, AuthorizationHandlerInterface $handler): AuthorizationResponseInterface
    {
        if ($this->rbac->hasAccess(
            $request->getSubject(),
            $request->getResource(),
            $request->getAction(),
            $request->getContext()
        )) {
            return new AuthorizationResponse(true, 'Checked against RBAC', self::class);
        }

        return $handler->handle($request);
    }
}
