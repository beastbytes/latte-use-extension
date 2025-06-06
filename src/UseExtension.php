<?php

declare(strict_types=1);

namespace BeastBytes\Latte\Extensions\Use;

use BeastBytes\Latte\Extensions\Use\Nodes\UseNode;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Php\Expression\ClassConstantFetchNode;
use Latte\Compiler\Nodes\Php\Expression\NewNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\NodeTraverser;
use Latte\Extension;

final class UseExtension extends Extension
{
    private array $use = [];

    public function getTags(): array
    {
        return [
            'use' => UseNode::create(...),
        ];
    }

    public function getPasses(): array
    {
        return [
            'useTagPass' => self::order($this->useTagPass(...), before: '*'),
        ];
    }

    public function useTagPass(TemplateNode $templateNode): void
    {
        (new NodeTraverser)->traverse(
            $templateNode,
            enter: function (Node $node): void {
                if ($node instanceof UseNode) {
                    $fqcn = '\\' . $node->fqcn->content;

                    if ($node->alias instanceof TextNode) {
                        $resolved = $node->alias->content;
                    } else {
                        $resolved = mb_substr($fqcn, mb_strrpos($fqcn, '\\') + 1);
                    }

                    $this->use[$resolved] = $fqcn;
                } elseif (
                    ($node instanceof ClassConstantFetchNode || $node instanceof NewNode)
                    && in_array($node->class->name, array_keys($this->use), true)
                ) {
                    $node->class->name = $this->use[$node->class->name];
                }
            },
        );
    }
}