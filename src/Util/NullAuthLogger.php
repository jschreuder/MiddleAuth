<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Util;

/**
 * The NullLogger supports disabling logging completely by black-holing all
 * log entries send to it.
 */
final class NullAuthLogger implements AuthLoggerInterface
{
    public function info(string $message, ?array $context = null): void
    {
        // does nothing, is a black hole
    }

    public function debug(string $message, ?array $context = null): void
    {
        // does nothing, is a black hole
    }

    public function warning(string $message, ?array $context = null): void
    {
        // does nothing, is a black hole
    }
}
