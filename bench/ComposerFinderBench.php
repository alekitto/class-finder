<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Benchmark;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PhpBench\Attributes as Bench;

use function iterator_to_array;

class ComposerFinderBench
{
    #[Bench\Iterations(10)]
    #[Bench\Revs(10)]
    public function benchIterate(): void
    {
        $finder = (new ComposerFinder())->skipBogonFiles();
        iterator_to_array($finder);
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(10)]
    public function benchIterateNamespaceFilter(): void
    {
        $finder = (new ComposerFinder())
            ->inNamespace('Symfony')
            ->skipBogonFiles();
        iterator_to_array($finder);
    }
}
