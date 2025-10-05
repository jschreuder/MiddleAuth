<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Util;

use Psr\Log\LoggerInterface;

final class PsrAuthLogger implements AuthLoggerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function info(string $message, ?array $context = null): void
    {
        $this->logger->info($message, $context ?? []);
    }

    public function debug(string $message, ?array $context = null): void
    {
        $this->logger->debug($message, $context ?? []);
    }

    public function warning(string $message, ?array $context = null): void
    {
        $this->logger->warning($message, $context ?? []);
    }
}
