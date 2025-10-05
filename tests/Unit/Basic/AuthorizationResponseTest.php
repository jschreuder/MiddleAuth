<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationResponse;

describe('Basic\AuthorizationResponse', function () {
    it('initializes with permitted status', function () {
        $response = new AuthorizationResponse(true);

        expect($response->isPermitted())->toBeTrue();
    });

    it('initializes with not permitted status', function () {
        $response = new AuthorizationResponse(false);

        expect($response->isPermitted())->toBeFalse();
    });

    it('returns the reason', function () {
        $reason = 'Insufficient permissions';
        $response = new AuthorizationResponse(false, $reason);

        expect($response->getReason())->toBe($reason);
    });

    it('returns null for reason if not provided', function () {
        $response = new AuthorizationResponse(true);

        expect($response->getReason())->toBeNull();
    });

    it('returns the handler', function () {
        $handler = 'AdminHandler';
        $response = new AuthorizationResponse(false, null, $handler);

        expect($response->getHandler())->toBe($handler);
    });

    it('returns null for handler if not provided', function () {
        $response = new AuthorizationResponse(true);

        expect($response->getHandler())->toBeNull();
    });
});