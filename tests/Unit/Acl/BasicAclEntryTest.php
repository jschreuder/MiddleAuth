<?php

use jschreuder\MiddleAuth\Acl\BasicAclEntry;

beforeEach(function () {
    $this->actor = 'user::123';
    $this->resource = 'post::456';
    $this->action = 'view';
    $this->context = ['key' => 'value'];
});

describe('BasicAclEntry', function () {
    it('matches actor with exact match', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('matches actor with wildcard', function () {
        $entry = new BasicAclEntry('user::*', $this->resource, $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('does not match actor with different type', function () {
        $entry = new BasicAclEntry('admin::*', $this->resource, $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeFalse();
    });

    it('matches resource with exact match', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with wildcard', function () {
        $entry = new BasicAclEntry($this->actor, 'post::*', $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('does not match resource with different type', function () {
        $entry = new BasicAclEntry($this->actor, 'comment::*', $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeFalse();
    });

    it('matches action with exact match', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, null);
        expect($entry->matchesAction($this->action))->toBeTrue();
    });

    it('matches action with wildcard', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, '*', null);
        expect($entry->matchesAction('any_action'))->toBeTrue();
    });

    it('does not match action with different action', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, 'edit', null);
        expect($entry->matchesAction($this->action))->toBeFalse();
    });

    it('matches context with no context matcher', function () {
        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, null);
        expect($entry->matchesContext($this->context))->toBeTrue();
    });

    it('matches context with context matcher returning true', function () {
        $contextMatcher = function (array $context) { return true; };

        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, $contextMatcher);
        expect($entry->matchesContext($this->context))->toBeTrue();
    });

    it('does not match context with context matcher returning false', function () {
        $contextMatcher = function (array $context) { return false; };

        $entry = new BasicAclEntry($this->actor, $this->resource, $this->action, $contextMatcher);
        expect($entry->matchesContext($this->context))->toBeFalse();
    });
});