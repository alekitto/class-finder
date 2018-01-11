<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\PhpDocumentorIterator;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class PhpDocumentorFinder implements FinderInterface
{
    use PhpDocumentorFilterTrait;

    /**
     * @var string
     */
    private $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function getIterator(): \Iterator
    {
        $iterator = new PhpDocumentorIterator($this->dir);
        if (null !== $this->dirs) {
            $iterator->in($this->dirs);
        }

        return $this->applyFilters($iterator);
    }
}
