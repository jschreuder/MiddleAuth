# MiddleAuth - Authorization Library

PHP 8.4+ authorization library implementing ACL, RBAC, ABAC patterns via PSR-15 middleware architecture.

## 🚨 CRITICAL RULES

1. **DO NOT modify tests as a solution** - When fixing bugs or adding features, modify implementation code, not tests. Only modify tests when explicitly asked or when interface/API changes require it.
2. **ALWAYS create interface before implementation** - No public classes without interfaces
3. **ALL implementations MUST be final** - Composition over inheritance
4. **NEVER type-hint concrete classes** - Only interfaces
5. **ALWAYS use strict types** - `declare(strict_types=1);` in every file
6. **NEVER create public properties** - Use getter methods

## Development Commands

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Unit/Acl/AclMiddlewareTest.php

# Run tests with coverage (if configured)
vendor/bin/pest --coverage
```

## File Structure

```
src/
  ├── {Interface}.php              # Core interfaces in root
  ├── Acl/                         # ACL implementation
  │   ├── AclMiddleware.php        # Specific implementation
  │   ├── BasicAccessControlList.php
  │   └── BasicAclEntry.php
  ├── Rbac/                        # RBAC (planned)
  ├── Abac/                        # ABAC (planned)
  └── Basic/                       # Default implementations of root interfaces
      ├── AuthorizationEntity.php
      ├── AuthorizationRequest.php
      ├── AuthorizationResponse.php
      └── DenyAllMiddleware.php

tests/Unit/                        # Mirrors src/ structure
```

## Naming Conventions

**Interfaces:** Describe capability without implementation hints
- `AuthorizationEntityInterface` (not `IAuthorizationEntity`)
- `AccessControlListInterface`

**Default Implementations:** Use "Basic" prefix, live in `Basic/` subdirectory
- `BasicAccessControlList` implements `AccessControlListInterface`
- `BasicAclEntry` implements `AclEntryInterface`
- Root interface implementations → `Basic/` namespace
- Feature-specific implementations → Feature namespace (e.g., `Acl/`)

**Middleware:** Describe strategy/purpose
- `AclMiddleware` (ACL-based authorization)
- `RbacMiddleware` (Role-based authorization)
- `DenyAllMiddleware` (Default deny)

## Code Requirements

### Every New Class

```php
<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\{Namespace};

final class ClassName implements ClassNameInterface
{
    public function __construct(
        private DependencyInterface $dependency,
        private string $value
    ) {}
}
```

**MUST:**
- Start with `declare(strict_types=1);`
- Be marked `final` (implementations only)
- Use constructor property promotion when possible
- Type-hint all parameters and return types
- Inject dependencies (never instantiate)

**NEVER:**
- Create public properties
- Type-hint concrete classes in signatures
- Use inheritance for implementations
- Use static methods or properties

### Every New Interface

```php
interface AuthorizationEntityInterface
{
    public function getId(): string;
    public function getType(): string;
    public function getAttributes(): array;
}
```

**MUST:**
- Have minimal, focused method sets
- Specify all return types
- Live in root namespace or feature namespace (e.g., `Acl/`)
- NOT expose implementation details (no hints about internal data structures or algorithms)

### Immutable Objects

When modifying state, return new instance:

```php
public function withHandler(AuthorizationHandlerInterface $handler): self
{
    $newQueue = clone $this->queue;
    $newQueue->enqueue($handler);
    return new self($newQueue);
}
```

## Testing with Pest

### Test File Rules

**File naming:** `{ClassName}Test.php`
**Location:** `tests/Unit/{Namespace}/`
**Test names:** Action verbs (returns, can be, matches, throws)

### Required Structure

```php
<?php

use jschreuder\MiddleAuth\Basic\AuthorizationEntity;

describe('Basic\AuthorizationEntity', function () {
    it('returns the type', function () {
        $entity = new AuthorizationEntity('user', '123');
        expect($entity->getType())->toBe('user');
    });
});
```

**MUST:**
- Use `describe()` for grouping
- Use `it()` for test cases
- One assertion per test when possible
- Use `beforeEach()` for setup
- Call `Mockery::close()` in `afterEach()` when using mocks

### Mocking Pattern

```php
afterEach(function () {
    Mockery::close();
});

it('permits access when ACL permits', function () {
    $acl = Mockery::mock(AccessControlListInterface::class);
    $acl->shouldReceive('hasAccess')
        ->once()
        ->with($subject, $resource, 'view', [])
        ->andReturn(true);
    
    // test implementation
});
```

**MUST:**
- Mock interfaces only (never concrete classes)
- Specify call counts (`once()`, `twice()`)
- Use `shouldNotReceive()` for negative expectations

## Implementation Patterns

### Middleware Pattern

```php
interface AuthorizationMiddlewareInterface
{
    public function process(
        AuthorizationRequestInterface $request, 
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface;
}

final class AclMiddleware implements AuthorizationMiddlewareInterface
{
    public function process(
        AuthorizationRequestInterface $request, 
        AuthorizationHandlerInterface $handler
    ): AuthorizationResponseInterface
    {
        if ($this->acl->hasAccess(/* ... */)) {
            return new AuthorizationResponse(true, 'reason', self::class);
        }
        return $handler->handle($request);
    }
}
```

**Pattern:** Check condition → return early if met → otherwise call `$handler->handle($request)`

### Pipeline Usage

```php
$pipeline = new AuthorizationPipeline(new \SplQueue())
    ->withHandler($aclHandler)
    ->withHandler($rbacHandler)
    ->withHandler($denyAllHandler);
    
$response = $pipeline->process($request);
```

**Immutable:** `withHandler()` returns new instance

### Entity Wrapper

```php
// Wrap domain entities for authorization
$user = new AuthorizationEntity('user', '123', ['role' => 'admin']);
$resource = new AuthorizationEntity('post', '456', ['author_id' => '123']);
```

**Pattern:** type + id + attributes (no domain knowledge)

## Tech Stack

- PHP 8.4+
- PSR Log (logging interface)
- Pest PHP (testing)
- Mockery (test mocking)
- NO external authorization libraries

## Workflow Procedures

### Creating New Authorization Implementation (e.g., RBAC, ABAC)

1. Create directory: `src/Rbac/`
2. Define interfaces in `src/Rbac/`
3. Create middleware: `RbacMiddleware implements AuthorizationMiddlewareInterface`
4. Implement support classes (all `final`)
5. Create tests in `tests/Unit/Rbac/`
6. Update this file with implementation-specific patterns if needed

### Adding New Middleware to Existing Implementation

1. Create interface if needed (e.g., `RoleCheckerInterface`)
2. Create `final` implementation
3. Implement `AuthorizationMiddlewareInterface`
4. Constructor inject dependencies (type-hint interfaces)
5. Return early on success, call `$handler->handle($request)` otherwise
6. Write tests (mock all dependencies)

### Creating Value Objects

1. Create interface
2. Create `final` implementation with interface
3. Use constructor property promotion
4. All properties `private`
5. Public getters only, no setters
6. Write tests

## File Boundaries

**NEVER modify:**
- `composer.json` (unless adding dependencies)
- `phpunit.xml` 
- `.gitignore`

**MODIFY ONLY when explicitly requested or when APIs change:**
- Existing test files

**READ but don't modify:**
- `README.md` (for project understanding)

**SAFE to create/modify:**
- New files in `src/`
- New test files in `tests/`
- This `Claude.md` file

## Common Mistakes to Avoid

- Public properties
- Non-final implementations  
- Missing type hints or strict types
- Type-hinting concrete classes
- Using inheritance for code reuse
- Static methods/properties
- Modifying tests as a solution (instead of fixing implementation)
- Adding external authorization libraries
- Exposing implementation details in interfaces
