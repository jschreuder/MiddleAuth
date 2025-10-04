<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Basic\AuthorizationResponse;
use Psr\Log\LoggerInterface;

final class AclMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private AclEntriesCollection $aclEntries,
        private ?LoggerInterface $logger = null
    ) {}

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        $actor = $request->getSubject();
        $resource = $request->getResource();
        $action = $request->getAction();

        $this->logger?->debug('ACL middleware evaluating request', [
            'subject_type' => $actor->getType(),
            'subject_id' => $actor->getId(),
            'resource_type' => $resource?->getType(),
            'resource_id' => $resource?->getId(),
            'action' => $action,
            'acl_entries_count' => count($this->aclEntries),
        ]);

        foreach ($this->aclEntries as $index => $aclEntry) {
            if (
                $aclEntry->matchesActor($actor)
                && $aclEntry->matchesResource($resource)
                && $aclEntry->matchesAction($action)
            ) {
                $this->logger?->debug('ACL entry matched', [
                    'entry_index' => $index,
                    'subject_type' => $actor->getType(),
                    'subject_id' => $actor->getId(),
                    'action' => $action,
                ]);

                return new AuthorizationResponse(
                    true,
                    'Access granted by ' . self::class,
                    self::class
                );
            }
        }

        $this->logger?->debug('No ACL entries matched, delegating to next handler');

        return $handler->handle($request);
    }
}
