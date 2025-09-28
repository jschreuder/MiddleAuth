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
        private AccessControlListInterface $acl,
        private ?EntityStringifierInterface $entityStringifier = null
    )
    {
        if (is_null($entityStringifier)) {
            $this->entityStringifier = new BasicEntityStringifier();
        }
    }

    public function process(AuthorizationRequestInterface $request, AuthorizationHandlerInterface $handler): AuthorizationResponseInterface
    {
        $permitted = $this->acl->hasAccess(
            $this->entityStringifier->stringifyEntity($request->getSubject()),
            $this->entityStringifier->stringifyEntity($request->getResource()),
            $request->getAction(),
            $request->getContext()
        );

        if (!$permitted) {
            return $handler->handle($request);
        }

        return new AuthorizationResponse($permitted, 'Checked against ACL', self::class);
    }
}
