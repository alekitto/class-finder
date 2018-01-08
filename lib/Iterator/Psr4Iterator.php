<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\PathNormalizer;

final class Psr4Iterator extends ClassIterator
{
    use PsrIteratorTrait;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $prefixLen;

    public function __construct(string $namespace, string $path, int $flags = 0)
    {
        $this->namespace = $namespace;
        $this->path = PathNormalizer::resolvePath($path);
        $this->prefixLen = strlen($this->path);

        parent::__construct($flags);
    }

    protected function getGenerator(): \Generator
    {
        $pattern = defined('HHVM_VERSION') ? '/\\.(php|hh)$/' : '/\\.php$/';

        foreach ($this->search() as $path => $info) {
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            $class = $this->namespace.ltrim(str_replace('/', '\\', substr($path, $this->prefixLen, -strlen($m[0]))), '\\');
            if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+$/', $class)) {
                continue;
            }

            yield $class => $path;
        }
    }
}
