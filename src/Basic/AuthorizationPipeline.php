<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationPipelineInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use Psr\Log\LoggerInterface;

final class AuthorizationPipeline implements AuthorizationPipelineInterface
{
    private \SplQueue $queue;

    public function __construct(\SplQueue $queue, private ?LoggerInterface $logger = null)
    {
        $this->queue = $queue;
    }

    public function withHandler(AuthorizationHandlerInterface $handler): self
    {
        $newQueue = clone $this->queue;
        $newQueue->enqueue($handler);
        return new self($newQueue, $this->logger);
    }

    public function process(AuthorizationRequestInterface $request): AuthorizationResponseInterface
    {
        if ($this->queue->count() === 0) {
            $this->logger?->warning('Authorization pipeline is empty, no handlers to process');
            throw new \RuntimeException('Pipeline is empty, no handlers to process.');
        }

        if (!is_null($this->logger)) {
            $context = [
                'subject_type' => $request->getSubject()->getType(),
                'subject_id' => $request->getSubject()->getId(),
                'resource_type' => $request->getResource()?->getType(),
                'resource_id' => $request->getResource()?->getId(),
                'action' => $request->getAction(),
            ];

            $this->logger->debug('Authorization pipeline processing request', $context);
        }

        $queue = clone $this->queue;
        $handler = $queue->dequeue();

        $response = $handler->handle($request);

        if (!is_null($this->logger)) {
            $context = $context ?? [
                'subject_type' => $request->getSubject()->getType(),
                'subject_id' => $request->getSubject()->getId(),
                'resource_type' => $request->getResource()?->getType(),
                'resource_id' => $request->getResource()?->getId(),
                'action' => $request->getAction(),
            ];

            $this->logger->info(
                'Authorization decision: ' . ($response->isPermitted() ? 'PERMIT' : 'DENY'),
                array_merge($context, [
                    'permitted' => $response->isPermitted(),
                    'reason' => $response->getReason(),
                    'handler' => $response->getHandler(),
                ])
            );
        }

        return $response;
    }
}
