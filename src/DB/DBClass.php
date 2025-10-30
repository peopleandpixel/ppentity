<?php

namespace ppEntity\DB;

use Dotenv\Dotenv;
use RedBeanPHP\R;

class DBClass {

    public static function connect(): void {
        if (R::testConnection()) {
            return;
        }
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        switch ($_ENV['DB_TYPE']) {
            case 'mysql':
            case 'mariadb':
                R::setup('mysql:host=' . $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
                break;
            case 'postgresql':
                R::setup('pgsql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
                break;
            case 'sqlite':
                R::setup('sqlite:' . $_ENV['DB_PATH']);
                break;
            case 'cubrid':
                R::setup('cubrid:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
                break;
        }
    }

    public static function disconnect(): void {
        R::close();
    }

}
