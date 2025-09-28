<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\BasicEntityStringifier;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

describe('BasicEntityStringifier', function () {
    it('should stringify an entity correctly', function () {
        // Arrange
        $entity = Mockery::mock(AuthorizationEntityInterface::class);
        $entity->shouldReceive('getType')->andReturn('user');
        $entity->shouldReceive('getId')->andReturn('123');
        
        $stringifier = new BasicEntityStringifier();
        
        // Act
        $result = $stringifier->stringifyEntity($entity);
        
        // Assert
        expect($result)->toBe('user::123');
    });
    
    it('should handle different entity types and IDs', function () {
        // Arrange
        $entity = Mockery::mock(AuthorizationEntityInterface::class);
        $entity->shouldReceive('getType')->andReturn('group');
        $entity->shouldReceive('getId')->andReturn('admin');
        
        $stringifier = new BasicEntityStringifier();
        
        // Act
        $result = $stringifier->stringifyEntity($entity);
        
        // Assert
        expect($result)->toBe('group::admin');
    });
});