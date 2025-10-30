# ppentity

A tiny helper around RedBeanPHP to work with very simple, dynamic entities and lists.

It gives you:
- `ppEntity\BasicEntity` — create, load, change, and save an entity in a table (bean) named by you.
- `ppEntity\List\BasicList` — fetch all entities or find by a condition.
- `ppEntity\DB\DBClass` — minimal DB bootstrap using a `.env` file.

The library is intentionally small. The public API is demonstrated in the tests and the examples below.

## Requirements
- PHP 8.3+
- PDO extension (and the driver for your DB)

## Installation
```bash
composer require peopleandpixel/ppentity
```

Autoloading uses PSR-4 (`ppEntity\\` → `src/`).

## Configuration (.env)
Connections are configured through environment variables loaded from a `.env` file in the project root (same folder as `src/`). The following variables are read by `DBClass`:

- `DB_TYPE` — one of: `sqlite`, `mysql`, `mariadb`, `postgresql`, `cubrid`

Depending on the type:

SQLite
- `DB_PATH` — path to the SQLite file (e.g. `data/app.sqlite`)

MySQL/MariaDB
- `DB_HOST` — hostname
- `DB_PORT` — port
- `DB_NAME` — database name
- `DB_USERNAME` — user
- `DB_PASSWORD` — password

PostgreSQL
- `DB_HOST` — hostname
- `DB_NAME` — database name
- `DB_USERNAME` — user
- `DB_PASSWORD` — password

Cubrid
- `DB_HOST` — hostname
- `DB_NAME` — database name
- `DB_USERNAME` — user
- `DB_PASSWORD` — password

There is an example file you can copy:
```bash
cp .env.example .env
# then adjust values
```

## Quick start
Below mirrors what the tests do. It uses a bean/table named `test` and two dynamic fields: `value1` and `value2`.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use ppEntity\BasicEntity;
use ppEntity\List\BasicList;

// 1) Create a new entity in the table (bean) named "test"
$entity = new BasicEntity('test');

// 2) Assign arbitrary dynamic properties
$entity->value1 = 'String 1';
$entity->value2 = 123; // any scalar works; stored via RedBeanPHP

// 3) Persist
$entity->save();
$id = $entity->id; // the auto-assigned id

// 4) Load the same entity later by id
$loaded = new BasicEntity('test', $id);
// Access the values that were stored
assert($loaded->value1 === 'String 1');
// Note: values come back as strings from the DB (e.g. "123")
```

### Work with multiple objects and lists
```php
// Create a few more rows
$e2 = new BasicEntity('test');
$e2->value1 = 'String 2';
$e2->value2 = 456;
$e2->save();

$e3 = new BasicEntity('test');
$e3->value1 = 'String 3';
$e3->value2 = 789;
$e3->save();

$e4 = new BasicEntity('test');
$e4->value1 = 'Special';
$e4->value2 = 999;
$e4->save();

// Count all rows in bean "test"
$total = BasicList::getAllCount('test'); // 4

// Get all as a list of BasicEntity objects (ordered by RedBean default)
$all = BasicList::getAll('test');
// $all[0]->value1 === 'String 1'
// $all[1]->value1 === 'String 2'
// $all[2]->value1 === 'String 3'
// $all[3]->value1 === 'Special'

// Find by a condition (RedBean WHERE condition syntax)
$gt500 = BasicList::findBy('test', 'value2 > 500');
// count($gt500) === 2
// $gt500[0]->value1 === 'String 3'
// $gt500[1]->value1 === 'Special'

$like = BasicList::findBy('test', 'value1 LIKE "String%"');
// count($like) === 3
```

## How it works (short version)
- `BasicEntity` is a thin wrapper around a RedBean `OODBBean`. It:
  - connects via `DBClass::connect()` in the constructor,
  - dispenses a bean for the given name (table) in lowercase,
  - optionally `load($id)` to populate dynamic properties,
  - on `save()`, copies dynamic properties to the bean, stores it (`R::store`) and sets `id`.
- Dynamic properties are handled with PHP magic methods: `__set`, `__get`, `__isset`, `__unset`.
- `BasicList` offers list operations using RedBean find helpers and returns arrays of `BasicEntity` instances.
- Connections are closed via `DBClass::disconnect()` after operations that hit the DB.

## Running tests
This project ships PHPUnit tests that double as usage examples.

```bash
composer install
composer test
# or
vendor/bin/phpunit
```

The tests create a temporary SQLite database at `tests/data/testdb.sqlite` and a local `.env` during the run, then clean up afterward.

## Notes and caveats
- Table names are the lowercase form of the name you pass to `BasicEntity`/`BasicList`.
- Values read back from the DB may be strings depending on the driver (e.g., integers may come back as "123"). The tests assert this behavior.
- This library does not try to be an ORM; it’s a minimal helper for simple use cases or quick scripts.

## License
GPL-3.0-or-later. See `LICENSE`.
