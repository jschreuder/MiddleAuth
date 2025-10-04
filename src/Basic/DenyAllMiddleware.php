<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationMiddlewareInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use Psr\Log\LoggerInterface;

final class DenyAllMiddleware implements AuthorizationMiddlewareInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {}

    public function process(
        AuthorizationRequestInterface $request,
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        $this->logger?->info('DenyAllMiddleware rejecting request - no authorization rules matched', [
            'subject_type' => $request->getSubject()->getType(),
            'subject_id' => $request->getSubject()->getId(),
            'resource_type' => $request->getResource()?->getType(),
            'resource_id' => $request->getResource()?->getId(),
            'action' => $request->getAction(),
        ]);

        return new AuthorizationResponse(false, 'No authorization rule matched', self::class);
    }
}
