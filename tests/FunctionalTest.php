<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

class FunctionalTest extends TestCase
{
    public function testShouldNotBreakIteratingAllClassesInProject(): void
    {
        $this->expectNotToPerformAssertions();

        $finder = (new ComposerFinder())->useAutoloading(false);
        iterator_to_array($finder);
    }
}
