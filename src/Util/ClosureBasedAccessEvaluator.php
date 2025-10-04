<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Util;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;

final class ClosureBasedAccessEvaluator implements AccessEvaluatorInterface
{
    private Closure $evaluator;

    public function __construct(callable $evaluator)
    {
        $this->evaluator = Closure::fromCallable($evaluator);
        $this->validateSignature();
    }

    public function hasAccess(AuthorizationEntityInterface $actor, AuthorizationEntityInterface $resource, string $action, array $context): bool
    {
        return ($this->evaluator)($actor, $resource, $action, $context);
    }

    private function validateSignature(): void
    {
        $reflection = new ReflectionFunction($this->evaluator);

        $parameters = $reflection->getParameters();
        $paramCount = count($parameters);

        // Only validate parameter count if any parameters have type hints
        $hasTypeHints = false;
        foreach ($parameters as $param) {
            if ($param->hasType()) {
                $hasTypeHints = true;
                break;
            }
        }

        if ($hasTypeHints && $paramCount !== 4) {
            throw new InvalidArgumentException(
                'Evaluator must accept exactly 4 parameters (AuthorizationEntityInterface, AuthorizationEntityInterface, string, array)'
            );
        }

        // If we have 4 parameters, validate their types if type hints are present
        if ($paramCount === 4) {
            if ($parameters[0]->hasType() && $parameters[0]->getType()?->getName() !== AuthorizationEntityInterface::class) {
                throw new InvalidArgumentException('First parameter must be AuthorizationEntityInterface');
            }

            if ($parameters[1]->hasType() && $parameters[1]->getType()?->getName() !== AuthorizationEntityInterface::class) {
                throw new InvalidArgumentException('Second parameter must be AuthorizationEntityInterface');
            }

            if ($parameters[2]->hasType() && $parameters[2]->getType()?->getName() !== 'string') {
                throw new InvalidArgumentException('Third parameter must be string');
            }

            if ($parameters[3]->hasType() && $parameters[3]->getType()?->getName() !== 'array') {
                throw new InvalidArgumentException('Fourth parameter must be array');
            }
        }
    }
}