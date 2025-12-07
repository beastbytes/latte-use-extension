<?php

declare(strict_types=1);

namespace BeastBytes\Latte\Extensions\Use\Tests;

use BeastBytes\Latte\Extensions\Use\UseExtension;
use Latte\Engine;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Files\FileHelper;

final class UseExtensionTest extends TestCase
{
    private const LATTE_TEMPLATE_DIR = __DIR__ . '/generated/template';
    private const LATTE_CACHE_DIR = __DIR__ . '/generated/cache';

    protected static Engine $latte;

    #[BeforeClass]
    public static function beforeClass(): void
    {
        FileHelper::ensureDirectory(self::LATTE_CACHE_DIR);
        FileHelper::ensureDirectory(self::LATTE_TEMPLATE_DIR);

        self::$latte = new Engine();
        self::$latte->setTempDirectory(self::LATTE_CACHE_DIR);
        //self::$latte->setStrictTypes(true);
        self::$latte->addExtension(new UseExtension());
    }

    #[AfterClass]
    public static function afterClass(): void
    {
        FileHelper::removeDirectory(self::LATTE_CACHE_DIR);
        FileHelper::removeDirectory(self::LATTE_TEMPLATE_DIR);
    }

    public static function classCallProvider()
    {
        return [
            'className' => [
                'classCall' => 'NamespacedClass',
                'expected' => '(new \\Framework\\Module\\NamespacedClass)',
            ],
            'classNameWithParenthesis' => [
                'classCall' => 'NamespacedClass()',
                'expected' => '(new \\Framework\\Module\\NamespacedClass)',
            ],
            'classNameWithAParameters' => [
                'classCall' => 'NamespacedClass(5)',
                'expected' => '(new \\Framework\\Module\\NamespacedClass(5))',
            ],
            'classNameWithParameters' => [
                'classCall' => 'NamespacedClass(5, $b)',
                'expected' => '(new \\Framework\\Module\\NamespacedClass(5, $b))',
            ],
        ];
    }

    #[Test]
    #[DataProvider('classCallProvider')]
    public function use_tag_new_class(string $classCall, string $expected): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
                {use Framework\Module\NamespacedClass}
                
                <p>The value is {(new %s)->getValue()}</p>
                TEMPLATE,
            $classCall
        );

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    /**
     * Use of an alias is about resolving the fully qualified class name,
     * so no need to test all the replacement scenarios.
     */

    #[Test]
    public function use_tag_class_constant(): void
    {
        $expected = '(\\Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass}
            
            <p>The constant is {NamespacedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function use_tag_in_n_attribute(): void
    {
        $expect = [
            'BeastBytes\\Latte\\Extensions\\Use\\Tests\\Support\\ClassName::CLASS_NAME',
            'BeastBytes\\Latte\\Extensions\\Use\\Tests\\Support\\ClassName::getClassName()'
        ];
        $template = <<<'TEMPLATE'
        {use BeastBytes\Latte\Extensions\Use\Tests\Support\ClassName}

        <p n:class="ClassName::CLASS_NAME">Constant</p>
        <p n:class="ClassName::getClassName()">Method</p>
        TEMPLATE;

        $actual = $this->compile($template);

        foreach ($expect as $expected) {
            $this->assertStringContainsString($expected, $actual);
        }
    }

    #[Test]
    public function use_tag_in_filter(): void
    {
        $expected = '($this->filters->replace)($testString, \\Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass}
            
            {varType string $testString}
            
            <p>{$testString|replace: NamespacedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function use_tag_alias(): void
    {
        $expected = '(\\Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass as AliasedClass}

            <p>The constant is {AliasedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function multiple_use_tags(): void
    {
        $expected = [
            '(new \\Framework\\Module\\NamespacedClass)',
            '(\\Framework\Module\\NamespacedClass::CONSTANT)',
            '($this->filters->replace)($testString, \\Framework\\Module\\Aliased\\NamespacedClass::CONSTANT)',
        ];
        $template = <<<'TEMPLATE'
            {use Framework\Module\Aliased\NamespacedClass as AliasedClass}
            {use Framework\Module\NamespacedClass}
            
            {varType string $testString}
            
            <p>The value is {(new NamespacedClass())->getValue()}</p>
            <p>The constant is {NamespacedClass::CONSTANT}</p>
            <p>{$testString|replace: AliasedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);

        foreach ($expected as $needle) {
            $this->assertStringContainsString($needle, $actual);
        }
    }

    private function compile(string $template): string
    {
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        return self::$latte->compile($templateFile);
    }
}