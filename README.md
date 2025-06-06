This package is a [Latte](https://latte.nette.org/) template engine extension that emulates
[PHP's `use` operator](https://www.php.net/manual/en/language.namespaces.importing.php) 
and allows writing cleaner templates.

By default, Latte templates require the use of Fully Qualified CLass Names (FQCN); this can lead to cluttered templates.
The extension defines the `{use}` tag that allows templates to define the FQCN and optionally an alias,
and refer to the _used_ class by the alias if defined, or the base class name if not.

## Requirements
- PHP 8.1 or higher.
- Latte

## Installation
Install the package using [Composer](https://getcomposer.org):

Either:
```shell
composer require beastbytes/latte-use-extension
```
or add the following to the `require` section of your `composer.json`
```json
"beastbytes/latte-use-extension": "<version.constraint>"
```

## Configuration
The extension is added to the Latte Engine using the engine's `addExtension()` method.
```php
$engine = new Engine();
$engine->addExtension(new UseExtension());
```

## Usage
The FQCN must be defined in a `{use}` tag before the base class name or alias is referenced in the template.
The best way to ensure this is to place `{use}` tags at the start of the template.

The extension replaces the alias or base class name defined in the `use` tag with the FQCN in class instantation (`new`)
statements and class constants during compilation; it _does not_ import or alias the class.

#### Differences from PHP
* There is no `as` clause when defining an alias
* Group `use` definitions are _not_ supported.

#### {use} Tag
```latte
{use Framework\Module\NamespacedClass}

<p>The value is {(new NamespacedClass)->getValue()}</p>
<p>The constant is {NamespacedClass::CONSTANT}</p>
```

#### {use} Tag with Alias
```latte
{use Framework\Module\Aliased\NamespacedClass AliasedClass}

<p>The value is {(new AliasedClass)->getValue()}</p>
<p>The constant is {AliasedClass::CONSTANT}</p>
```

#### Multiple {use} Tags
```latte
{use Framework\Module\Aliased\NamespacedClass AliasedClass}
{use Framework\Module\NamespacedClass}

{varType int $arg}
{varType string $testString}

<p>The value is {(new NamespacedClass($arg))->getValue()}</p>
<p>The constant is {NamespacedClass::CONSTANT}</p>
<p>{$testString|replace: AliasedClass::CONSTANT}</p>
```

## License
The BeastBytes Latte Use Extension is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.