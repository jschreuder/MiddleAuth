<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Basic\AuthorizationEntity;

describe('Basic\AuthorizationEntity', function () {
    it('can be instantiated', function () {
        $entity = new AuthorizationEntity('user', '123');

        expect($entity)->toBeInstanceOf(AuthorizationEntity::class);
    });

    it('returns the type', function () {
        $entity = new AuthorizationEntity('user', '123');

        expect($entity->getType())->toBe('user');
    });

    it('returns the id', function () {
        $entity = new AuthorizationEntity('user', '123');

        expect($entity->getId())->toBe('123');
    });

    it('returns the attributes array', function() {
        $entity = new AuthorizationEntity('user', '123', ['name' => 'John Doe']);

        expect($entity->getAttributes())->toBe(['name' => 'John Doe']);
    });

    it('throws exception when type is empty', function () {
        expect(fn() => new AuthorizationEntity('', '123'))
            ->toThrow(InvalidArgumentException::class, 'Type of AuthorizationEntity cannot be empty.');
    });

    it('throws exception when type is whitespace only', function () {
        expect(fn() => new AuthorizationEntity('   ', '123'))
            ->toThrow(InvalidArgumentException::class, 'Type of AuthorizationEntity cannot be empty.');
    });

    it('throws exception when id is empty', function () {
        expect(fn() => new AuthorizationEntity('user', ''))
            ->toThrow(InvalidArgumentException::class, 'ID of AuthorizationEntity cannot be empty.');
    });

    it('throws exception when id is whitespace only', function () {
        expect(fn() => new AuthorizationEntity('user', '   '))
            ->toThrow(InvalidArgumentException::class, 'ID of AuthorizationEntity cannot be empty.');
    });
});
