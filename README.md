# Orderly

[![Build Status](https://img.shields.io/travis/UseMuffin/Orderly/master.svg?style=flat-square)](https://travis-ci.org/UseMuffin/Orderly)
[![Coverage](https://img.shields.io/codecov/c/github/UseMuffin/Orderly.svg?style=flat-square)](https://codecov.io/github/UseMuffin/Orderly)
[![Total Downloads](https://img.shields.io/packagist/dt/muffin/orderly.svg?style=flat-square)](https://packagist.org/packages/muffin/orderly)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Allows setting default order for your tables.

## Install

Using [Composer][composer]:

```
composer require muffin/orderly
```

Then load the plugin using the console command:

```
bin/cake plugin load Muffin/Orderly
```

## What is does

Orderly allow you to set default `ORDER` clause for your table queries, similar
to the functionality provided by `Model::$order` property of CakePHP 2.x.

## Usage

Here's how you can add the `Orderly` behavior for your tables:

```php
// By default orders by display field of table.
$this->addBehavior('Muffin/Orderly.Orderly');

// Default ordering using specified field.
$this->addBehavior('Muffin/Orderly.Orderly', ['order' => $this->aliasField('field_name')]);
```

Value for `order` key can any valid value that `\Cake\ORM\Query::order()` takes.
The default order clause will only be applied to the primary query and when no
custom order clause has already been set for the query.

You can also configure the behavior to apply the default order based on required
condition using `callback` option. The order will be applied if callback returns `true`:

```php
$this->addBehavior('Muffin/Orderly.Orderly', [
    'order' => ['Alias.field_name' => 'DESC'],
    'callback' => function (\Cake\ORM\Query $query, \ArrayObject $options, bool $primary) {
        //return a boolean
    }
]);
```

The behavior can also be configured with multiple orders which are applied based
on return value of their respective callbacks:

```php
$this->addBehavior('Muffin/Orderly.Orderly', [
    [
        'order' => ['Alias.field_name' => 'DESC'],
        'callback' => function (\Cake\ORM\Query $query, \ArrayObject $options, bool $primary) {
            //return a boolean
        }
    ],
    [
        'order' => ['Alias.another_field'],
        'callback' => function (\Cake\ORM\Query $query, \ArrayObject $options, bool $primary) {
            //return a boolean
        }
    ],
]);
```

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of
their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

To ensure your PRs are considered for upstream, you MUST follow the [CakePHP coding standards][standards].

## Bugs & Feedback

http://github.com/usemuffin/orderly/issues

## License

Copyright (c) 2015-Present, [Use Muffin][muffin] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
[muffin]:http://usemuffin.com
[standards]:http://book.cakephp.org/3/en/contributing/cakephp-coding-conventions.html
