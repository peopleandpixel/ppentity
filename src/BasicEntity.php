<?php

namespace ppEntity;

use AllowDynamicProperties;
use ppEntity\DB\DBClass;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;

/**
 * @property int|string $id
 */
#[AllowDynamicProperties]
class BasicEntity {

    private ?OODBBean $bean = null;

    public function __construct(string $name, ?int $id = null) {
        DBClass::connect();
        $this->name = $name;
        $this->bean = R::dispense(strtolower($this->name));
        if ($id) {
            $this->load($id);
        }
    }

    public function isInitialized() : bool {
        return $this->bean !== null;
    }

    private array $properties = [];
    public function __set(string $name, $value): void {
        $this->properties[$name] = $value;
    }

    public function __get(string $name) : mixed {
        return $this->properties[$name] ?? null;
    }

    public function __isset(string $name) : bool {
        return isset($this->properties[$name]);
    }

    public function __unset(string $name) : void {
        unset($this->properties[$name]);
    }

    public function __toString() : string {
        return $this->name ?? '';
    }

    public function __clone() : void {
        foreach ($this->properties as $key => $value) {
            $this->properties[$key] = clone $value;
        }
    }

    public function load(int $id): void {
        $this->bean = R::load(strtolower($this->name), $id);
        foreach ($this->bean as $key => $value) {
            $this->properties[$key] = $value;
        }
        DBClass::disconnect();
    }

    /**
     * @throws SQL
     */
    public function save(): void {
        foreach ($this->properties as $key => $value) {
            $this->bean->$key = $value;
        }

        $this->id = R::store($this->bean);
        DBClass::disconnect();
    }

}
