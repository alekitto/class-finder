<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Fixtures\Psr4;

use Kcs\ClassFinder\Fixtures\Psr4\SubNs\FooBaz;

/**
 * @FooBaz()
 */
#[FooBaz]
abstract class AbstractClass
{
}
