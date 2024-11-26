<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\ErrorHandler;
use ReflectionClass;
use Throwable;

use function assert;

final class ClassMapIterator extends ClassIterator
{
    private readonly ReflectorFactoryInterface $reflectorFactory;

    /** @param array<class-string, string> $classMap */
    public function __construct(
        private readonly array $classMap,
        ReflectorFactoryInterface|null $reflectorFactory,
        int $flags = 0,
        array|null $excludeNamespaces = null,
        Closure|null $pathCallback = null,
    ) {
        parent::__construct($flags, $excludeNamespaces, $pathCallback);

        $this->reflectorFactory = $reflectorFactory ?? new NativeReflectorFactory();
    }

    /** @return Generator<class-string, ReflectionClass> */
    protected function getGenerator(): Generator
    {
        $include = Closure::bind(
            static function (string $path): void {
                include_once $path;
            },
            null,
            null,
        );

        foreach ($this->classMap as $className => $path) {
            $path = PathNormalizer::resolvePath($path);
            if ($this->pathCallback && ! ($this->pathCallback)($path)) {
                continue;
            }

            ErrorHandler::register();
            try {
                @$include($path);
                $reflectionClass = $this->reflectorFactory->reflect($className);
            } catch (Throwable) { /** @phpstan-ignore-line */
                continue;
            } finally {
                ErrorHandler::unregister();
            }

            assert($reflectionClass instanceof ReflectionClass);

            yield $className => $reflectionClass;
        }
    }
}
