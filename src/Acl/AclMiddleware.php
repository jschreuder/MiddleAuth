<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

final class AclMiddleware implements AuthorizationMiddlewareInterface
{
    /** @var AclEntryInterface[] */
    private array $aclEntries;

    public function __construct(AclEntryInterface ...$aclEntries)
    {
        $this->aclEntries = $aclEntries;
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

        foreach ($this->aclEntries as $aclEntry) {
            if (
                $aclEntry->matchesActor($actor)
                && $aclEntry->matchesResource($resource)
                && $aclEntry->matchesAction($action)
            ) {
                return new AuthorizationResponse(
                    true,
                    'Access granted by ' . self::class,
                    self::class
                );
            }
        }

        return $handler->handle($request);
    }
}
