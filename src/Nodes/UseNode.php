<?php

declare(strict_types=1);

namespace BeastBytes\Latte\Extensions\Use\Nodes;

use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\Token;

class UseNode extends AreaNode
{
    private const AS = 'as';

    public NopNode|TextNode $alias;
    public NopNode|TextNode $fqcn;

    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = $tag->node = new UseNode();
        $stream = $tag->parser->stream;

        $node->fqcn = new TextNode($stream->consume(Token::Php_NameQualified)->text);

        $as = $stream->tryConsume(Token::Php_Identifier);
        if ($as instanceof Token) {
            if ($as->text !== self::AS) {
                throw new CompileException(sprintf(
                    "Syntax error: expected '%s' clause",
                    self::AS
                ));
            }

            $alias = $stream->tryConsume(Token::Php_Identifier);

            if (!$alias instanceof Token) {
                throw new CompileException(sprintf(
                    "Syntax error: expected identifier for '%s' clause",
                    self::AS
                ));
            }

            $node->alias = new TextNode($alias->text);
        } else {
            $node->alias = new NopNode();
        }

        return $node;
    }

    public function &getIterator(): Generator
    {
        yield $this->fqcn;
        yield $this->alias;
    }

    public function print(PrintContext $context): string
    {
        return '';
    }
}