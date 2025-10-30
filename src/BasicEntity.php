<?php

namespace ppEntity;

use AllowDynamicProperties;
use ppEntity\DB\DBClass;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;

/**
 * BasicEntity is a tiny, dynamic wrapper around a RedBeanPHP `OODBBean`.
 *
 * It lets you:
 * - create a new row for an arbitrary bean/table name (lowercased),
 * - optionally load an existing row by `id`,
 * - assign/read arbitrary dynamic properties via magic methods,
 * - persist the current state using `save()`.
 *
 * Notes
 * - The dynamic `id` property is assigned after `save()` and may be returned as `int` or `string`
 *   depending on the database driver.
 * - Instances connect to the database using `DBClass::connect()` when needed and close
 *   connections using `DBClass::disconnect()` after load/save operations.
 *
 * @property int|string $id Dynamic primary key set after `save()`
 */
#[AllowDynamicProperties]
class BasicEntity {

    /** @var OODBBean|null Underlying RedBean bean being wrapped */
    private ?OODBBean $bean = null;
    /** @var string Cached lowercase table/bean name */
    private string $tableName = '';

    /**
     * Construct a new BasicEntity for the given bean/table name.
     *
     * Behavior
     * - If `$bean` is provided, the instance is initialized from that bean without extra DB calls.
     * - Otherwise, a new bean for the lowercased `$name` is dispensed. If `$id` is provided, the row
     *   is loaded and dynamic properties are populated.
     *
     * Side effects
     * - May open a DB connection via `DBClass::connect()` if a bean has to be dispensed/loaded.
     *
     * @param string $name Arbitrary bean/table name. Will be lowercased internally.
     * @param int|null $id Optional id to load an existing row.
     * @param OODBBean|null $bean Optional existing RedBean bean to wrap (avoids reload).
     */
    public function __construct(string $name, ?int $id = null, ?OODBBean $bean = null) {
        // expose dynamic property for tests/consumers
        $this->name = $name;
        $this->tableName = strtolower($this->name);

        if ($bean instanceof OODBBean) {
            // Use provided bean (no extra DB round-trip)
            $this->bean = $bean;
            $this->beanToProperties();
            return;
        }

        DBClass::connect();
        $this->bean = R::dispense($this->tableName);
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Indicates whether the internal RedBean bean has been initialized.
     *
     * @return bool True if the entity currently wraps a bean; false otherwise.
     */
    public function isInitialized() : bool {
        return $this->bean !== null;
    }

    /** @var array<string, mixed> Internal storage for dynamic properties */
    private array $properties = [];
    /**
     * Assigns a dynamic property on the entity (stored in-memory until `save()`).
     *
     * @param string $name Property name
     * @param mixed $value Arbitrary value; scalars are recommended for DB storage
     */
    public function __set(string $name, $value): void {
        $this->properties[$name] = $value;
    }

    /**
     * Retrieves a dynamic property previously assigned or loaded from the DB.
     *
     * @param string $name Property name
     * @return mixed|null The value or null if not set
     */
    public function __get(string $name) : mixed {
        return $this->properties[$name] ?? null;
    }

    /**
     * Checks whether a dynamic property is set.
     *
     * @param string $name Property name
     * @return bool True if set (and not null), false otherwise
     */
    public function __isset(string $name) : bool {
        return isset($this->properties[$name]);
    }

    /**
     * Unsets a dynamic property from the in-memory state.
     *
     * @param string $name Property name
     */
    public function __unset(string $name) : void {
        unset($this->properties[$name]);
    }

    /**
     * Returns the bean/table name as string representation.
     *
     * @return string The original (provided) name or empty string if not set
     */
    public function __toString() : string {
        return $this->name ?? '';
    }

    /**
     * Deep-clones object values among dynamic properties.
     *
     * Scalars and non-objects are copied by value; objects are cloned.
     */
    public function __clone() : void {
        foreach ($this->properties as $key => $value) {
            $this->properties[$key] = is_object($value) ? clone $value : $value;
        }
    }

    /**
     * Loads an existing row by id and populates dynamic properties.
     *
     * Side effects
     * - Opens a DB connection if not already open and calls `DBClass::disconnect()` when done.
     *
     * @param int $id Primary key to load
     * @return void
     */
    public function load(int $id): void {
        $this->bean = R::load($this->tableName, $id);
        $this->beanToProperties();
        DBClass::disconnect();
    }

    /**
     * @throws SQL
     */
    public function save(): void {
        $this->propertiesToBean();
        $this->id = R::store($this->bean);
        DBClass::disconnect();
    }

    /**
     * Copies all fields from the underlying RedBean bean into the dynamic properties array.
     * Internal helper to keep constructor/load compact.
     */
    private function beanToProperties(): void {
        foreach ($this->bean as $key => $value) {
            $this->properties[$key] = $value;
        }
    }
    
    private function propertiesToBean(): void {
        foreach ($this->properties as $key => $value) {
            $this->bean->$key = $value;
        }
    }



}
