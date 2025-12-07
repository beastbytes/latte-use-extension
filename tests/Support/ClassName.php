<?php

declare(strict_types=1);

namespace BeastBytes\Latte\Extensions\Use\Tests\Support;

class ClassName
{
    public const CLASS_NAME = 'class-name';

    public function getClassName(): string
    {
        return self::CLASS_NAME;
    }
}