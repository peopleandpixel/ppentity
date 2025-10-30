<?php

namespace ppEntity\List;

use ppEntity\BasicEntity;
use ppEntity\DB\DBClass;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * BasicList provides simple list operations for `BasicEntity` objects.
 *
 * It offers:
 * - `getAllCount($name)` to count rows for a bean/table.
 * - `getAll($name)` to fetch all rows as `BasicEntity` instances.
 * - `findBy($name, $condition)` to fetch rows matching a RedBean condition.
 *
 * Each method opens a DB connection via `DBClass::connect()` and closes it
 * via `DBClass::disconnect()` once the operation is complete.
 */
class BasicList {

    /**
     * Returns the number of rows for the given bean/table name.
     *
     * Side effects: Opens a DB connection and closes it after counting.
     *
     * @param string $name Bean/table name (will be lowercased internally)
     * @return int Total number of rows
     */
    public static function getAllCount(string $name): int {
        DBClass::connect();
        $retval = R::count(strtolower($name));
        DBClass::disconnect();
        return $retval;
    }

    /**
     * Finds rows by a RedBean condition and returns them as `BasicEntity` objects.
     *
     * Examples of `$condition`:
     * - `value2 > 500`
     * - `value1 LIKE "String%"`
     *
     * Side effects: Opens a DB connection and closes it after fetching.
     *
     * @param string $name Bean/table name (lowercased internally)
     * @param string $condition RedBean WHERE condition (without the `WHERE` keyword)
     * @return array<int, BasicEntity> List of entities in RedBean's default order
     */
    public static function findBy(string $name, string $condition): array {
        DBClass::connect();
        $beans = R::find(strtolower($name), $condition);
        $list = self::mapBeansToEntities($beans);
        DBClass::disconnect();
        return $list;
    }

    /**
     * Returns all rows for the given bean/table as `BasicEntity` objects.
     *
     * Side effects: Opens a DB connection and closes it after fetching.
     *
     * @param string $name Bean/table name (lowercased internally)
     * @return array<int, BasicEntity> List of entities in RedBean's default order
     */
    public static function getAll(string $name): array {
        DBClass::connect();
        $beans = R::findAll(strtolower($name));
        $list = self::mapBeansToEntities($beans);
        DBClass::disconnect();
        return $list;
    }

    /**
     * @param array<int,OODBBean> $beansArray
     * @return array<int,BasicEntity>
     */
    private static function mapBeansToEntities(array $beansArray): array {
        $list = [];
        foreach ($beansArray as $bean) {
            // Avoid extra DB round-trips by reusing the bean
            $list[] = new BasicEntity($bean->name, null, $bean);
        }
        return $list;
    }
}