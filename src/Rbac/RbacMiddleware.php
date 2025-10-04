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
        private RoleProviderInterface $roleProvider
    )
    {
    }

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        $actor = $request->getSubject();
        $resource = $request->getResource();
        $action = $request->getAction();
        $context = $request->getContext();

        $roles = $this->roleProvider->getRolesForActor($actor);

        foreach ($roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                if (
                    $permission->matchesResource($resource)
                    && $permission->matchesAction($action)
                ) {
                    return new AuthorizationResponse(
                        true,
                        'Access granted by ' . self::class,
                        self::class
                    );
                }
            }
        }

        return $handler->handle($request);
    }
}
