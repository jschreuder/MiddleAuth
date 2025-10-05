<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Util\PsrAuthLogger;
use Psr\Log\LoggerInterface;

describe('Util\PsrAuthLogger', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('logs info message with context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('info')
            ->once()
            ->with('Test message', ['key' => 'value']);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->info('Test message', ['key' => 'value']);
    });

    it('logs info message without context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('info')
            ->once()
            ->with('Test message', []);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->info('Test message');
    });

    it('logs debug message with context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('debug')
            ->once()
            ->with('Debug message', ['debug' => 'data']);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->debug('Debug message', ['debug' => 'data']);
    });

    it('logs debug message without context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('debug')
            ->once()
            ->with('Debug message', []);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->debug('Debug message');
    });

    it('logs warning message with context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('warning')
            ->once()
            ->with('Warning message', ['warning' => 'context']);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->warning('Warning message', ['warning' => 'context']);
    });

    it('logs warning message without context when PSR logger is present', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('warning')
            ->once()
            ->with('Warning message', []);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->warning('Warning message');
    });

    it('handles null context as empty array', function () {
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger->shouldReceive('info')
            ->once()
            ->with('Message', []);

        $authLogger = new PsrAuthLogger($psrLogger);
        $authLogger->info('Message', null);
    });
});
