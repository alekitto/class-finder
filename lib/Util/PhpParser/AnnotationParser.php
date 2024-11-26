<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util\PhpParser;

use Closure;
use Doctrine\Common\Annotations\DocLexer;
use PhpParser\Node;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;

use function assert;
use function class_exists;
use function in_array;
use function strlen;
use function strpos;
use function substr;
use function trim;

if (! class_exists(NodeVisitorAbstract::class)) {
    return;
}

class AnnotationParser extends NodeVisitorAbstract
{
    private const CLASS_IDENTIFIERS = [
        DocLexer::T_IDENTIFIER,
        DocLexer::T_TRUE,
        DocLexer::T_FALSE,
        DocLexer::T_NULL,
    ];

    private readonly DocLexer $lexer;
    private Closure $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->lexer = new DocLexer();
        $this->nameResolver = (fn (Node\Name $name): Node\Name => $this->resolveClassName($name)) /** @phpstan-ignore-line */
            ->bindTo($nameResolver, NameResolver::class);
    }

    public function leaveNode(Node $node): Node|null
    {
        if (! $node instanceof Node\Stmt\ClassLike) {
            return null;
        }

        $docblock = $node->getDocComment();
        if ($docblock === null) {
            return null;
        }

        $input = $docblock->getReformattedText();
        $this->lexer->setInput(trim(substr($input, $this->findInitialTokenPosition($input) ?? 0), '* /'));
        $this->lexer->moveNext();

        $annotations = [];
        while ($this->lexer->lookahead !== null) {
            if ($this->lexer->lookahead['type'] !== DocLexer::T_AT) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is preceded by non-catchable pattern
            if (
                $this->lexer->token !== null &&
                $this->lexer->lookahead['position'] === $this->lexer->token['position'] + strlen(
                    $this->lexer->token['value'],
                )
            ) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is followed by either a namespace separator, or
            // an identifier token
            $peek = $this->lexer->glimpse();
            if (
                ($peek === null)
                || $peek['position'] !== $this->lexer->lookahead['position'] + 1
                || ($peek['type'] !== DocLexer::T_NAMESPACE_SEPARATOR && ! in_array(
                    $peek['type'],
                    self::CLASS_IDENTIFIERS,
                    true,
                ))
            ) {
                $this->lexer->moveNext();
                continue;
            }

            $annot = $this->annotation();
            if ($annot === false) {
                continue;
            }

            $annotations[] = $annot;
        }

        if (! $annotations) {
            return null;
        }

        $node->setAttribute('annotations', $annotations);

        return null;
    }

    private function findInitialTokenPosition(string $input): int|null
    {
        $pos = 0;

        // search for first valid annotation
        while (($pos = strpos($input, '@', $pos)) !== false) {
            $preceding = $input[$pos - 1];

            // if the @ is preceded by a space, a tab or * it is valid
            if ($pos === 0 || $preceding === ' ' || $preceding === '*' || $preceding === "\t") {
                return $pos;
            }

            $pos++;
        }

        return null;
    }

    private function annotation(): false|Node\Name
    {
        assert($this->lexer->isNextToken(DocLexer::T_AT));
        $this->lexer->moveNext();

        // check if we have an annotation
        $name = $this->identifier();
        if ($name === null) {
            return false;
        }

        if (
            $this->lexer->isNextToken(DocLexer::T_MINUS)
            && $this->lexer->nextTokenIsAdjacent()
        ) {
            // Annotations with dashes, such as "@foo-" or "@foo-bar", are to be discarded
            return false;
        }

        $name = ($this->nameResolver)($name);
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
            $level = 1;
            while ($level) {
                $this->lexer->moveNext();
                if ($this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
                    ++$level;
                } elseif ($this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
                    --$level;
                }
            }
        }

        return $name;
    }

    private function identifier(): Node\Name|null
    {
        // check if we have an annotation
        if (! $this->lexer->isNextTokenAny(self::CLASS_IDENTIFIERS)) {
            return null;
        }

        $this->lexer->moveNext();
        $className = $this->lexer->token['value']; /** @phpstan-ignore-line */

        while (
            $this->lexer->lookahead !== null &&
            $this->lexer->lookahead['position'] === ($this->lexer->token['position'] + /** @phpstan-ignore-line */
                strlen($this->lexer->token['value'])) && /** @phpstan-ignore-line */
            $this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)
        ) {
            if (! $this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)) {
                return null;
            }

            $this->lexer->moveNext();

            if (! $this->lexer->isNextTokenAny(self::CLASS_IDENTIFIERS)) {
                return null;
            }

            $this->lexer->moveNext();
            $className .= '\\' . $this->lexer->token['value']; /** @phpstan-ignore-line */
        }

        return new Node\Name($className);
    }
}
