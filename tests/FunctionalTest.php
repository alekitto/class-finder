<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testAll(): void
    {
        $finder = new ComposerFinder();
        \iterator_to_array($finder);
    }
}
