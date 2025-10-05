<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Util\NullAuthLogger;

describe('Util\NullAuthLogger', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('does not fail when logging info without PSR logger', function () {
        $authLogger = new NullAuthLogger();
        $authLogger->info('Test message', ['key' => 'value']);

        expect(true)->toBeTrue(); // No exception thrown
    });

    it('does not fail when logging debug without PSR logger', function () {
        $authLogger = new NullAuthLogger();
        $authLogger->debug('Debug message', ['debug' => 'data']);

        expect(true)->toBeTrue(); // No exception thrown
    });

    it('does not fail when logging warning without PSR logger', function () {
        $authLogger = new NullAuthLogger();
        $authLogger->warning('Warning message', ['warning' => 'context']);

        expect(true)->toBeTrue(); // No exception thrown
    });
});
