<?php

namespace ppEntity\List;

use ppEntity\BasicEntity;
use ppEntity\DB\DBClass;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class BasicList {

    private static array $list = [];

    public static function getAllCount(string $name): int {
        DBClass::connect();
        $retval = R::count(strtolower($name));
        DBClass::disconnect();
        return $retval;
    }


    public static function findBy(string $name, string $condition): array {
        self::$list = [];
        DBClass::connect();
        $retval = R::find(strtolower($name), $condition);
        self::$list = self::getList($retval);
        DBClass::disconnect();
        return self::$list;

    }
    public static function getAll(string $name): array {
        DBClass::connect();
        $retval = R::findAll(strtolower($name));
        self::$list = self::getList($retval);
        DBClass::disconnect();
        return self::$list;
    }

    private static function getList(array $beansArray): array {
        $list = [];
        foreach ($beansArray as $bean) {
            $list[] = self::get($bean->name, $bean->id);
        }
        return $list;
    }

    private static function get(string $name, int $id): BasicEntity {
        return new BasicEntity($name, $id);
    }

}