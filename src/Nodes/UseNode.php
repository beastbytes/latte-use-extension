<?php

declare(strict_types=1);

namespace BeastBytes\Latte\Extensions\Use\Nodes;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\Token;

class UseNode extends AreaNode
{
    public NopNode|TextNode $alias;
    public NopNode|TextNode $fqcn;

    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = $tag->node = new UseNode();
        $stream = $tag->parser->stream;

        $node->fqcn = new TextNode($stream->consume(Token::Php_NameQualified)->text);
        $alias = $stream->tryConsume(Token::Php_Identifier);
        $node->alias = $alias instanceof Token ? new TextNode($alias->text) : new NopNode();

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