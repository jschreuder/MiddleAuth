# MiddleAuth

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/badges/build.png?b=master)](https://scrutinizer-ci.com/g/jschreuder/MiddleAuth/?branch=master)

**PHP 8.4+ Authorization Framework**

A lightweight, flexible authorization library implementing ACL, RBAC, and ABAC patterns through a [AuthZen](https://openid.net/wg/authzen/) and [PSR-15](https://www.php-fig.org/psr/psr-15/)-inspired middleware architecture.

> ⚠️ **Alpha Status**: This library is in development and exploratory phase. The API will change. Not recommended for production use, though feel free to fork/take it for yourself.

## 🎯 Goals

MiddleAuth provides the **structural foundation** for application authorization, allowing you to focus on your domain-specific authorization logic rather than building authorization infrastructure from scratch.

**What MiddleAuth gives you:**
- Well-tested authorization patterns (ACL, RBAC, ABAC)
- Middleware pipeline architecture for composing authorization strategies
- Type-safe interfaces following PHP best practices
- Extensible evaluation system for custom business rules
- Very basic implementations for a rapid start

**What you provide:**
- Domain-specific authorization logic to replace the basic implementations where they are too simple
- Integration with your user/permission storage
- Custom evaluators for your business rules

## 🧩 Core Concepts

### Middleware Pipeline

Authorization flows through a **chain of middleware handlers**, each implementing a specific authorization strategy. Handlers either grant access immediately or pass the request to the next handler:

```
Request → ACL Check → RBAC Check → ABAC Check → Deny All
             ↓            ↓            ↓            ↓
          Grant?       Grant?       Grant?       Deny
```

To give an example of how this might work: Let's say you have a cloud-drive in which you can edit your own files, files that are shared with your teams and files that are shared with you specifically by another user. In the above example you would use ACL to check individual shares, RBAC to check your team roles and ABAC to show files that are owned by themselves and shared with people working in the physical office.

### Authorization Entity Wrapper

Domain objects are wrapped in a generic `AuthorizationEntity` to decouple your business logic from the authorization system:

```php
// Your domain user
$user = $userRepository->find(123);

// Wrapped for authorization
$actor = new AuthorizationEntity(
    type: 'user',
    id: (string) $user->getId(),
    attributes: ['role' => $user->getRole(), 'department' => $user->getDepartment()]
);
```

### Three Included Authorization Strategies

MiddleAuth provides pure implementations of three distinct authorization patterns:

- **ACL (Access Control List)**: Direct actor-resource-action rules. Evaluates only the actor identity, resource identity, and action.
- **RBAC (Role-Based Access Control)**: Permissions grouped into roles. Evaluates actor roles, resource identity, and action.
- **ABAC (Attribute-Based Access Control)**: Dynamic rules based on attributes and context. Evaluates actor attributes, resource attributes, action, **and context** for complex business logic.

**Note:** The included implementations follow pure pattern definitions—ACL and RBAC do not use context, only ABAC does. However, all `AuthorizationRequest` data (including context) is available to custom middleware implementations if you need hybrid approaches for your specific requirements.

## 📦 Installation

```bash
composer require jschreuder/middle-auth
```

**Requirements:**
- PHP 8.4 or higher
- PSR-3 LoggerInterface (for optional logging support)

## 🚀 Getting Started

### Basic ACL Example

```php
use jschreuder\MiddleAuth\Acl\{AclMiddleware, BasicAclEntry};
use jschreuder\MiddleAuth\Basic\{AuthorizationEntity, AuthorizationRequest, AuthorizationPipeline, DenyAllMiddleware};

// Define ACL rules
$aclMiddleware = new AclMiddleware(
    // User 123 can view order 456
    new BasicAclEntry('user::123', 'order::456', 'view'),
    
    // All admins can do anything
    new BasicAclEntry('admin::*', '*', '*'),
    
    // All users can view their own profile
    new BasicAclEntry('user::*', 'profile::*', 'view')
);

// Create authorization pipeline
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($aclMiddleware)
    ->withHandler(new DenyAllMiddleware());

// Make authorization request
$user = new AuthorizationEntity('user', '123');
$order = new AuthorizationEntity('order', '456');
$request = new AuthorizationRequest($user, $order, 'view', []); // Context (empty array) ignored by ACL

$response = $pipeline->process($request);

if ($response->isPermitted()) {
    echo "Access granted: " . $response->getReason();
} else {
    echo "Access denied: " . $response->getReason();
}
```

### Pattern Matching

MiddleAuth supports flexible pattern matching:

| Pattern | Matches |
|---------|---------|
| `*` | Everything |
| `user::*` | All entities of type "user" |
| `user::123` | Specific user with ID 123 |

### RBAC Example

```php
use jschreuder\MiddleAuth\Rbac\{RbacMiddleware, BasicRoleProvider, BasicRole, BasicPermission, RolesCollection, PermissionsCollection};

// Define permissions
$viewOrders = new BasicPermission('order::*', 'view');
$editOrders = new BasicPermission('order::*', 'edit');
$deleteOrders = new BasicPermission('order::*', 'delete');

// Create roles
$viewer = new BasicRole('viewer', new PermissionsCollection($viewOrders));
$editor = new BasicRole('editor', new PermissionsCollection($viewOrders, $editOrders));
$admin = new BasicRole('admin', new PermissionsCollection($viewOrders, $editOrders, $deleteOrders));

// Map users to roles
$roleProvider = new BasicRoleProvider([
    'user::123' => new RolesCollection($viewer),
    'user::456' => new RolesCollection($editor, $admin), // Multiple roles!
]);

$rbacMiddleware = new RbacMiddleware($roleProvider);

// Use in pipeline
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($rbacMiddleware)
    ->withHandler(new DenyAllMiddleware());

// Note: RBAC ignores context in authorization requests
```

### ABAC Example

```php
use jschreuder\MiddleAuth\Abac\{AbacMiddleware, BasicPolicyProvider, BasicPolicy};
use jschreuder\MiddleAuth\Util\ClosureBasedAccessEvaluator;

// Define attribute-based policies
$ownerCanEdit = new BasicPolicy(
    new ClosureBasedAccessEvaluator(
        function ($actor, $resource, $action, $context) {
            // Users can edit documents they own
            return $action === 'edit' 
                && $resource->getType() === 'document'
                && $resource->getAttributes()['owner_id'] === $actor->getId();
        }
    ),
    'Document owners can edit their documents'
);

$departmentAccess = new BasicPolicy(
    new ClosureBasedAccessEvaluator(
        function ($actor, $resource, $action, $context) {
            // Users can view resources in their department
            $actorDept = $actor->getAttributes()['department'] ?? null;
            $resourceDept = $resource->getAttributes()['department'] ?? null;
            
            return $action === 'view' && $actorDept === $resourceDept;
        }
    ),
    'Department members can view department resources'
);

$policyProvider = new BasicPolicyProvider($ownerCanEdit, $departmentAccess);
$abacMiddleware = new AbacMiddleware($policyProvider);
```

### Combining Strategies

The power of MiddleAuth is composing multiple strategies:

```php
// Try ACL first (explicit rules), then RBAC (role-based), then ABAC (dynamic), finally deny
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($aclMiddleware)      // Fast, explicit rules
    ->withHandler($rbacMiddleware)     // Role-based permissions
    ->withHandler($abacMiddleware)     // Complex business logic
    ->withHandler(new DenyAllMiddleware()); // Default deny
```

## 🔧 Integration Patterns

### Integrating with Your Domain

#### 1. Custom Role Provider (Database-backed)

```php
use jschreuder\MiddleAuth\Rbac\RoleProviderInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class DatabaseRoleProvider implements RoleProviderInterface
{
    public function __construct(
        private PDO $db,
        private RoleFactory $roleFactory
    ) {}
    
    public function getRolesForActor(AuthorizationEntityInterface $actor): RolesCollection
    {
        // Query your database
        $stmt = $this->db->prepare(
            'SELECT r.* FROM roles r 
             JOIN user_roles ur ON r.id = ur.role_id 
             WHERE ur.user_id = :userId'
        );
        $stmt->execute(['userId' => $actor->getId()]);
        
        $roles = [];
        foreach ($stmt->fetchAll() as $row) {
            $roles[] = $this->roleFactory->createFromRow($row);
        }
        
        return new RolesCollection(...$roles);
    }
}
```

#### 2. Custom Policy Provider (Business Rules Engine)

```php
final class BusinessRulesPolicyProvider implements PolicyProviderInterface
{
    public function __construct(
        private RulesEngine $rulesEngine
    ) {}
    
    public function getPolicies(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): PoliciesCollection {
        // Load policies from your rules engine
        $rules = $this->rulesEngine->getApplicableRules(
            resourceType: $resource->getType(),
            action: $action
        );
        
        $policies = [];
        foreach ($rules as $rule) {
            $policies[] = new BasicPolicy(
                new ClosureBasedAccessEvaluator($rule->getEvaluator()),
                $rule->getDescription()
            );
        }
        
        return new PoliciesCollection(...$policies);
    }
}
```

#### 3. Context-Aware Evaluators (ABAC Only)

```php
// Time-based access control
$businessHoursOnly = new BasicPolicy(
    new ClosureBasedAccessEvaluator(
        function ($actor, $resource, $action, $context) {
            $hour = (int) date('H');
            return $hour >= 9 && $hour < 17; // 9 AM to 5 PM
        }
    ),
    'Access restricted to business hours'
);

// IP-based restrictions
$internalNetworkOnly = new BasicPolicy(
    new ClosureBasedAccessEvaluator(
        function ($actor, $resource, $action, $context) {
            $clientIp = $context['client_ip'] ?? null;
            return str_starts_with($clientIp, '192.168.');
        }
    ),
    'Access restricted to internal network'
);

// Combine multiple conditions in a single policy
$restrictedAccess = new BasicPolicy(
    new ClosureBasedAccessEvaluator(
        function ($actor, $resource, $action, $context) {
            $hour = (int) date('H');
            $isBusinessHours = $hour >= 9 && $hour < 17;
            $clientIp = $context['client_ip'] ?? null;
            $isInternalNetwork = str_starts_with($clientIp, '192.168.');

            return $isBusinessHours && $isInternalNetwork;
        }
    ),
    'Admin panel access restricted to business hours on internal network'
);

$policyProvider = new BasicPolicyProvider($businessHoursOnly, $internalNetworkOnly, $restrictedAccess);
$abacMiddleware = new AbacMiddleware($policyProvider);
```

#### 4. Framework Integration (PSR-15 Example)

```php
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationPipelineInterface $authPipeline,
        private EntityFactory $entityFactory
    ) {}
    
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Extract from HTTP request
        $user = $request->getAttribute('user');
        $resourceType = $request->getAttribute('resource_type');
        $resourceId = $request->getAttribute('resource_id');
        $action = $this->mapHttpMethodToAction($request->getMethod());
        
        // Wrap in authorization entities
        $actor = $this->entityFactory->createFromUser($user);
        $resource = $this->entityFactory->create($resourceType, $resourceId);
        
        // Create authorization request
        $authRequest = new AuthorizationRequest(
            $actor,
            $resource,
            $action,
            context: ['ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null]
        );
        
        // Check authorization
        $authResponse = $this->authPipeline->process($authRequest);
        
        if (!$authResponse->isPermitted()) {
            return new JsonResponse(
                ['error' => 'Forbidden', 'reason' => $authResponse->getReason()],
                403
            );
        }
        
        // Proceed with request
        return $handler->handle($request);
    }
    
    private function mapHttpMethodToAction(string $method): string
    {
        return match($method) {
            'GET', 'HEAD' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'edit',
            'DELETE' => 'delete',
            default => 'unknown'
        };
    }
}
```

## 🎓 Best Practices

### 1. Always End with DenyAllMiddleware

White-listing (defining what is allowed) is considered superior to black-listing (defining what is not allowed). For this reason the only final Middleware included is the `DenyAllMiddleware`. All other included middleware will assume there's at least one more Middleware to check when they fail to give permission. You can add any other type of middleware at the end (even an `AllowAllMiddleware`) but it is not recommended.

```php
// ✅ Good - explicit deny
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($aclMiddleware)
    ->withHandler(new DenyAllMiddleware());

// ❌ Bad - throws exception when no handler grants access
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($aclMiddleware);
```

### 2. Order Handlers by Specificity

Think about the order in which they are processed, any allow will work, but if one is computationally more cheap there's a good reason to start with it. Or if one is 90% of the time the one giving the answer, that might be the best one to start with. Or of course if you want to add more complex behaviors than are included.

```php
// ✅ Good - specific to general
$pipeline = (new AuthorizationPipeline(new \SplQueue()))
    ->withHandler($aclMiddleware)        // Specific rules
    ->withHandler($rbacMiddleware)       // Role-based
    ->withHandler($abacMiddleware)       // Dynamic/complex
    ->withHandler(new DenyAllMiddleware());
```

### 3. Use Attributes for Dynamic Data

It is a good practice to include relevant attributes that might assist in access decisions.

```php
$user = new AuthorizationEntity('user', '123', [
    'role' => 'editor',
    'department' => 'engineering',
    'subscription_tier' => 'premium'
]);

$document = new AuthorizationEntity('document', '456', [
    'owner_id' => '123',
    'department' => 'engineering',
    'status' => 'published',
    'created_at' => '2024-01-15'
]);
```

### 4. Leverage Context for Request-Specific Data (ABAC Only)

```php
$request = new AuthorizationRequest(
    $user,
    $resource,
    'edit',
    context: [
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'time' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'mfa_verified' => $session->get('mfa_verified'),
    ]
);

// This context will only be used by ABAC policies, not by ACL or RBAC handlers
```

### 5. Create Domain-Specific Evaluators

Instead of inline closures everywhere, create reusable evaluators.

```php
final class DocumentOwnershipEvaluator implements AccessEvaluatorInterface
{
    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): bool {
        if ($resource->getType() !== 'document') {
            return false;
        }
        
        $ownerId = $resource->getAttributes()['owner_id'] ?? null;
        return $ownerId === $actor->getId();
    }
}
```

## 📊 Logging & Audit Trail

MiddleAuth includes built-in support for PSR-3 logging to create comprehensive audit trails of authorization decisions.

### Enabling Logging

Logging is **optional** and can be added as the last constructor parameter:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create your PSR-3 logger
$logger = new Logger('authorization');
$logger->pushHandler(new StreamHandler('path/to/auth.log', Logger::DEBUG));

// Add logger to pipeline
$pipeline = new AuthorizationPipeline(new \SplQueue(), $logger);

// Add logger to middleware (fluent interface)
$aclMiddleware = new AclMiddleware($aclEntries, $logger);
$rbacMiddleware = new RbacMiddleware($roleProvider, $logger);
$abacMiddleware = new AbacMiddleware($policyProvider, $logger);
$denyMiddleware = new DenyAllMiddleware($logger);

// Build pipeline with logging enabled
$pipeline = (new AuthorizationPipeline(new \SplQueue(), $logger))
    ->withHandler($aclMiddleware)
    ->withHandler($rbacMiddleware)
    ->withHandler($abacMiddleware)
    ->withHandler($denyMiddleware);
```

### What Gets Logged

**Pipeline (INFO level)**:
- Final authorization decisions (PERMIT/DENY)
- Subject, resource, action, and which handler responded
- Reason for the decision

**Middleware (DEBUG level)**:
- Each middleware's evaluation process
- Which ACL entries, roles, or policies were checked
- Match successes and delegation to next handler

**Example Log Output** (INFO level):
```
[2024-01-15 10:23:45] authorization.INFO: Authorization decision: PERMIT {"subject_type":"user","subject_id":"123","resource_type":"document","resource_id":"456","action":"edit","permitted":true,"reason":"Access granted by AclMiddleware","handler":"jschreuder\\MiddleAuth\\Acl\\AclMiddleware"}
```

**Example Log Output** (DEBUG level):
```
[2024-01-15 10:23:45] authorization.DEBUG: Authorization pipeline processing request {"subject_type":"user","subject_id":"123","resource_type":"document","resource_id":"456","action":"edit"}
[2024-01-15 10:23:45] authorization.DEBUG: ACL middleware evaluating request {"subject_type":"user","subject_id":"123","resource_type":"document","resource_id":"456","action":"edit","acl_entries_count":5}
[2024-01-15 10:23:45] authorization.DEBUG: ACL entry matched {"entry_index":2,"subject_type":"user","subject_id":"123","action":"edit"}
```

### Security Considerations

The logging implementation:
- ✅ Logs only actor, resource and action for ACL and RBAC, and just the context keys for ABAC
- ✅ Logs structured data for easy parsing and analysis
- ✅ Includes enough detail for security audits
- ⚠️ Consider sanitizing PII in entity attributes before logging

## 📄 License

MIT

---

**Philosophy**: MiddleAuth provides the *structure* for authorization. You provide the *logic* specific to your application's needs. Together, they create a robust, maintainable authorization system without reinventing the wheel.