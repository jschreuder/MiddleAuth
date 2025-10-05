<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationHandlerInterface;
use jschreuder\MiddleAuth\AuthorizationPipelineInterface;
use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationResponseInterface;
use jschreuder\MiddleAuth\Util\AuthLoggerInterface;
use jschreuder\MiddleAuth\Util\NullAuthLogger;

final class AuthorizationPipeline implements AuthorizationPipelineInterface
{
    public function __construct(
        private \SplQueue $queue,
        private ?AuthLoggerInterface $logger = null
    )
    {
        if (is_null($logger)) {
            $this->logger = new NullAuthLogger();
        }
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
            $this->logger->warning('Authorization pipeline is empty, no handlers to process');
            throw new \RuntimeException('Pipeline is empty, no handlers to process.');
        }

        $this->logger->debug('Authorization pipeline processing request', [
            'subject_type' => $request->getSubject()->getType(),
            'subject_id' => $request->getSubject()->getId(),
            'resource_type' => $request->getResource()->getType(),
            'resource_id' => $request->getResource()->getId(),
            'action' => $request->getAction(),
        ]);

        $queue = clone $this->queue;
        $handler = $queue->dequeue();

        $response = $handler->handle($request);

        $this->logger->info(
            'Authorization decision: ' . ($response->isPermitted() ? 'PERMIT' : 'DENY'),
            [
                'subject_type' => $request->getSubject()->getType(),
                'subject_id' => $request->getSubject()->getId(),
                'resource_type' => $request->getResource()->getType(),
                'resource_id' => $request->getResource()->getId(),
                'action' => $request->getAction(),
                'permitted' => $response->isPermitted(),
                'reason' => $response->getReason(),
                'handler' => $response->getHandler(),
            ]
        );

        return $response;
    }
}
