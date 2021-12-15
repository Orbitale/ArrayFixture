Orbitale Array Fixture
======================

A Doctrine Data Fixture service that allow you to write your fixtures as PHP arrays.

## Installation

Install the library in your project with [Composer](https://getcomposer.org):

```php
composer require orbitale/array-fixture
```

## Why?

Doctrine Fixtures examples across the web mostly look like this:

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product = new Post();
        $product->setTitle('First post');
        $product->setDescription('Lorem ipsum');

        $manager->persist($product);
        $manager->flush();
    }
}
```

This example contains two main issues:

* It is **very verbose**: you do everything by hand.
* Your entities **must** have setters, and therefore expose an [anemic model](https://en.wikipedia.org/wiki/Anemic_domain_model).

This package provides a Fixture class you can use (with or without the [DoctrineBundle](https://github.com/doctrine/DoctrineBundle)) that allows you to write **PHP arrays** instead of objects and manual instructions.

The biggest advantage is that you can easily get a raw PHP export of your database (such as with PhpMyAdmin) and put it inside the fixture (this requires checking fields names, but search/replace will handily help making this fast), or you can also fetch data from any source (Fixtures are registered as services when using them in Symfony, so you could inject any service in their constructor) and return it as a PHP array (such as fetching from an API, a CSV file, a Yaml file, etc.).

There are some specific features that are however only available in pure PHP mode (like getting references and using a `Closure` to populate the field).

## Usage

Here is an example of a standard fixtures class:

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use App\Entity\Post;
use Orbitale\Component\ArrayFixture\ArrayFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class PostFixtures extends ArrayFixture implements ORMFixtureInterface
{
    public function getEntityClass(): string
    {
        return Post::class;
    }

    public function getObjects(): iterable
    {
        yield ['title' => 'First post', 'description' => 'Lorem ipsum'];
        yield ['title' => 'Second post', 'description' => 'muspi meroL'];
    }
}
```

Let's explain:

* We are extending the `ArrayFixture` class from this package
* You must implement `ORMFixtureInterface` yourself to allow Symfony to autoconfigure this fixture as a service. This is **mandatory when using Symfony and the DoctrineBundle**.
* The `getEntityClass()` method needs to know which Entity will be managed by this Fixture class.
* The `getObjects()` method must return an `iterable`, so it could either be an `array` or a `Generator`, since the fixture will only loop on it.<br>
  Every array in `getObjects()` will be hydrated in an instance of the Entity class, **without the need for setters**. Bye anemic entities!<br>
  Then, new entity is persisted.

After all entities are hydrated and persisted, the Entity Manager will be flushed, and entities will be saved in your database.

### References

Sometimes, an entity depends on another to be populated.

With this Fixture class, it becomes much simpler since a few methods can be overriden in order to simplify the understanding on how our entities are persisted.

**ℹ Note:** For this, you must determine in which order these fixtures must be executed, by implementing either the `Doctrine\Common\DataFixtures\OrderedFixtureInterface` or `Doctrine\Common\DataFixtures\DependentFixtureInterface` interfaces (but not both!).

See the examples:

```php
<?php

namespace App\DataFixtures\ORM;

use App\Entity\Tag;
use Orbitale\Component\ArrayFixture\ArrayFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class TagFixtures extends ArrayFixture implements ORMFixtureInterface
{
    public function getEntityClass(): string
    {
        return Tag::class;
    }

    public function getReferencePrefix(): ?string
    {
        return 'tags-';
    }

    public function getMethodNameForReference(): string
    {
        return 'getName';
    }

    public function getObjects(): array
    {
        return [
            ['name' => 'Some tag'],
        ];
    }
}
```

Thanks to the overrides of `getReferencePrefix` and `getMethodNameForReference`, after each `Tag` is persisted, the Fixture will add a reference of the tag in the memory by calling this:

```php
$this->addReference($this->getReferencePrefix().$tag->getName(), $tag);
```

Then, we can create the `PostFixtures` and fetch the reference just like we would do with any common Fixture:

```php
<?php

namespace App\DataFixtures\ORM;

use App\Entity\Post;
use Orbitale\Component\ArrayFixture\ArrayFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class PostFixtures extends ArrayFixture implements ORMFixtureInterface
{
    public function getEntityClass(): string
    {
        return Post::class;
    }

    public function getObjects(): array
    {
        return [
            [
                'title' => 'First post',
                'tags' => [
                    $this->getReference('tags-Some tag'),
                ],
            ],
        ];
    }
}
```

Here, we reuse the `Some tag` tag name and the `tags-` prefix that were specified in the `TagFixtures`!

**🚀Pro tip:** You can also use a **class constant** in `TagFixtures` (or any other class) for the tag name (`"Some tag"` here) and reuse it in your tests, this will allow you to change `Some Tag` to something else without breaking your tests!

### Using a callable to get a self-referenced entity

When you have self-referencing relationships, you may need a reference of an object that may have already been persisted.

For this, first, you should set the `flushEveryXIterations` option to `1` (view below) to allow flushing every entity. **Be careful, because flushing every time is more time-consuming.** Don't do this if you have a lot of entities to process in your fixture (like hundreds).

Next, you can set a `callable` element as the value of your object so you can interact manually with the injected object
 as 1st argument, and the `AbstractFixture` object as 2nd argument.

The `EntityManagerInterface` is also injected as 3rd argument in case you need to do some specific requests or query through another
 table.

Example here:

```php
<?php

namespace App\DataFixtures\ORM;

use App\Entity\Post;
use Orbitale\Component\ArrayFixture\ArrayFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;

class PostFixtures extends ArrayFixture implements ORMFixtureInterface
{
    public function getEntityClass(): string
    {
        return Post::class;
    }

    /**
     * With this, we can retrieve a Post reference with this method:
     *   $this->getReference('posts-1');
     * where '1' is the post id.
     */
    public function getReferencePrefix(): ?string
    {
        return 'posts-';
    }

    protected function flushEveryXIterations(): int
    {
        return 1;
    }

    public function getObjects(): array
    {
        return [
            ['id' => 1, 'title' => 'First post', 'parent' => null],
            [
                'title' => 'Second post',
                'parent' => function(Post $object, ArrayFixture $fixture, EntityManagerInterface $manager) {
                    return $fixture->getReference('posts-1');
                },
            ],
        ];
    }
}
```

This allows perfect synchronicity when dealing with self-referencing relations.

The advantage of closures is that you can also use external references by using a `use (...)` directive in the closure definition, or you can also call the entity itself, the fixture or the entity manager since they are passed as arguments (as in the example) to the closure at runtime.

### Insert primary keys

If you want, you can specify the primary key field of your entity, like this:

```php
public function getObjects(): array
{
    return [
        ['id' => 1, 'title' => 'Post 1'],
        ['id' => 2, 'title' => 'Post 2'],
    ];
}
```

If your primary key name is different than `id`, it's fine: the fixture class uses Doctrine's `Metadata` in order to detect your primary key.

This is really efficient when using `uuid` as field type for your entities, as UUIDs can be generated either from the database or from your code itself, and Doctrine will take it in account, contrary to auto-increment-integer fields that need a feedback from the database to generate the ID.

Note that this feature is not yet compatible with composite primary keys (yet, feel free to help if you know some about this!).

## `ArrayFixture` class reference

The `ArrayFixture` class contains several `protected` methods you can override for your needs:

* `getReferencePrefix()` (default `null`)<br>
  Used to make the Fixture class call `$this->addReference()` after each entity is persisted.<br>
  References are stored as `{referencePrefix}-{id|__toString()|specific method name}`.
* `getMethodNameForReference()` (default `getId`)<br>
  Used to specify which method on the object is used to specify the reference.<br>
  Defaults to `getId`, always falls back to `__toString()` if exists, throws an exception in case method doesn't exist.
* `flushEveryXIterations()` (default `0`)<br>
  Used to flush in batches instead of flushing only once at the end of all fixtures persist.
* `disableLogger()` (default `true`)<br>
  Used to disable SQL queries logging, useful to save memory at runtime when you have hundreds of entities to save.
* `clearEntityManagerOnFlush()` (default `true`)<br>
  Used to make sure the Entity manager is totally cleared after a `flush()`, this saves Doctrine from potential memory leaks or high memory consumption, but at the cost of needing to make more queries if you need to fetch objects from the database, even though this is supposed to be a really rare and edgy case.
* `createNewInstance(array $data)`<br>
  This method is here to automatically instantiate your object **without constructor** and populate its properties **without setters**.<br>
  You can override it in case you have a very custom way of handling object creation, even though this library should be sufficient.

## License and Copyright

This project is licensed under AGPL-3.0. This license implies that if you modify this project, you must share those modifications by making them open-source too. However, the AGPL-3.0 license applies only on this project.

If you don't want to follow this rule or do not want to use AGPL-3.0 licensed software, you must buy commercial licenses.

Contact us for more information.
