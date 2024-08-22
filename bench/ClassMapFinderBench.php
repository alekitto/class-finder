<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Benchmark;

use Kcs\ClassFinder\Finder\ClassMapFinder;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Util\ClassMap;
use PhpBench\Attributes as Bench;

use function iterator_to_array;

class ClassMapFinderBench
{
    private ClassMapFinder $finder;

    public function setUp(): void
    {
        $finder = (new ComposerFinder())->skipBogonFiles();
        $this->finder = ClassMap::fromFinder($finder)->createFinder();
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\Iterations(10)]
    #[Bench\Revs(10)]
    public function benchIterate(): void
    {
        $this->finder->skipBogonFiles();
        iterator_to_array($this->finder);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\Iterations(10)]
    #[Bench\Revs(10)]
    public function benchIterateNamespaceFilter(): void
    {
        $this->finder
            ->inNamespace('Symfony')
            ->skipBogonFiles();
        iterator_to_array($this->finder);
    }
}
