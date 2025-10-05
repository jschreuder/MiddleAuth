<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Util;

interface AuthLoggerInterface
{
    public function info(string $message, ?array $context = null): void;
    public function debug(string $message, ?array $context = null): void;
    public function warning(string $message, ?array $context = null): void;
}
