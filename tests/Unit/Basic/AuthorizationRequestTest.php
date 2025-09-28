<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationRequest;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

describe('Basic\AuthorizationRequest', function () {
    it('can be constructed with valid arguments', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $action = 'view';
        $context = ['key' => 'value'];

        $request = new AuthorizationRequest(
            $subject,
            $resource,
            $action,
            $context
        );

        expect($request)->toBeInstanceOf(AuthorizationRequest::class);
    });

    it('returns the correct subject', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $action = 'view';
        $context = ['key' => 'value'];

        $request = new AuthorizationRequest(
            $subject,
            $resource,
            $action,
            $context
        );

        expect($request->getSubject())->toBe($subject);
    });

    it('returns the correct resource', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $action = 'view';
        $context = ['key' => 'value'];

        $request = new AuthorizationRequest(
            $subject,
            $resource,
            $action,
            $context
        );

        expect($request->getResource())->toBe($resource);
    });

    it('returns the correct action', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $action = 'view';
        $context = ['key' => 'value'];

        $request = new AuthorizationRequest(
            $subject,
            $resource,
            $action,
            $context
        );

        expect($request->getAction())->toBe($action);
    });

    it('returns the correct context', function () {
        $subject = Mockery::mock(AuthorizationEntityInterface::class);
        $resource = Mockery::mock(AuthorizationEntityInterface::class);
        $action = 'view';
        $context = ['key' => 'value'];

        $request = new AuthorizationRequest(
            $subject,
            $resource,
            $action,
            $context
        );

        expect($request->getContext())->toBe($context);
    });
});