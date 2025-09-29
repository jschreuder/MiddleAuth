<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

final class AclMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private AccessControlListInterface $acl
    )
    {
    }

    public function process(AuthorizationRequestInterface $request, AuthorizationHandlerInterface $handler): AuthorizationResponseInterface
    {
        if ($this->acl->hasAccess(
            $request->getSubject(),
            $request->getResource(),
            $request->getAction(),
            $request->getContext()
        )) {
            return new AuthorizationResponse(true, 'Checked against ACL', self::class);
        }

        return $handler->handle($request);
    }
}
