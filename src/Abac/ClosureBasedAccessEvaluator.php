<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionParameter;

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

        if (count($parameters) !== 4) {
            throw new InvalidArgumentException(
                'AccessEvaluator must accept exactly 4 parameters (AuthorizationEntityInterface, AuthorizationEntityInterface, string, array)'
            );
        }

        // If we have 4 parameters, validate their types if type hints are present
        $this->checkType($parameters[0], 1, AuthorizationEntityInterface::class);
        $this->checkType($parameters[1], 2, AuthorizationEntityInterface::class);
        $this->checkType($parameters[2], 3, 'string');
        $this->checkType($parameters[3], 4, 'array');
    }

    private function checkType(ReflectionParameter $parameter, int $number, string $type)
    {
        if (!$parameter->hasType() || strval($parameter->getType()) !== $type) {
            throw new InvalidArgumentException('Parameter '.$number.' must be '.$type);
        }
    }
}