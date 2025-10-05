<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;
use jschreuder\MiddleAuth\Util\NullAuthLogger;

final class RbacMiddleware implements AuthorizationMiddlewareInterface
{
    private AuthLoggerInterface $logger;

    public function __construct(
        private RoleProviderInterface $roleProvider,
        ?AuthLoggerInterface $logger = null
    )
    {
        $this->logger = $logger ?? new NullAuthLogger();
    }

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        $actor = $request->getSubject();
        $resource = $request->getResource();
        $action = $request->getAction();

        $roles = $this->roleProvider->getRolesForActor($actor);

        $this->logger->debug('RBAC middleware evaluating request', [
            'subject_type' => $actor->getType(),
            'subject_id' => $actor->getId(),
            'resource_type' => $resource->getType(),
            'resource_id' => $resource->getId(),
            'action' => $action,
            'roles_count' => $roles->count(),
        ]);

        foreach ($roles as $role) {
            $this->logger->debug('Checking role permissions', [
                'role_name' => $role->getName(),
                'permissions_count' => $role->getPermissions()->count(),
            ]);

            foreach ($role->getPermissions() as $permission) {
                if (
                    $permission->matchesResource($resource)
                    && $permission->matchesAction($action)
                ) {
                    $this->logger->debug('Permission matched', [
                        'role_name' => $role->getName(),
                        'resource_type' => $resource->getType(),
                        'action' => $action,
                    ]);

                    return new AuthorizationResponse(
                        true,
                        'Access granted by ' . self::class,
                        self::class
                    );
                }
            }
        }

        $this->logger->debug('No role permissions matched, delegating to next handler');

        return $handler->handle($request);
    }
}
