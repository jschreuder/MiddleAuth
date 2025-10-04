<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

final class AbacMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private AttributeBasedAccessControlInterface $abac
    )
    {
    }

    public function process(AuthorizationRequestInterface $request, AuthorizationHandlerInterface $handler): AuthorizationResponseInterface
    {
        if ($this->abac->hasAccess(
            $request->getSubject(),
            $request->getResource(),
            $request->getAction(),
            $request->getContext()
        )) {
            return new AuthorizationResponse(true, 'Checked against ABAC', self::class);
        }

        return $handler->handle($request);
    }
}
