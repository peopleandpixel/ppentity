<?php

namespace ppEntity\DB;

use Dotenv\Dotenv;
use RedBeanPHP\R;

/**
 * DBClass is a minimal RedBeanPHP bootstrapper that reads connection
 * parameters from a `.env` file (loaded once per process) and opens/closes
 * the database connection on demand.
 *
 * Supported `DB_TYPE` values and expected environment variables:
 * - sqlite: `DB_PATH`
 * - mysql/mariadb: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`
 * - postgresql: `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`
 * - cubrid: `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`
 */
class DBClass {

    /**
     * Tracks whether the `.env` file was already loaded for this process.
     * Prevents repeated disk I/O when connecting multiple times.
     */
    private static bool $envLoaded = false;

    /**
     * Opens a database connection using environment variables from `.env`.
     *
     * Behavior
     * - Loads `.env` only once per process (guarded by `$envLoaded`).
     * - Reuses an existing connection if already open (`R::testConnection()`).
     * - Configures RedBean with the appropriate DSN based on `DB_TYPE`.
     */
    public static function connect(): void {
        if (!R::testConnection()) {
            if (!self::$envLoaded) {
                $dir = __DIR__;
                $envDir = str_contains($dir, 'vendor') ? explode('vendor/', $dir)[0] : $dir . '/../../';
                $dotenv = Dotenv::createImmutable($envDir);
                $dotenv->load();
                self::$envLoaded = true;
            }
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
    }

    /**
     * Closes the current database connection (if any).
     */
    public static function disconnect(): void {
        R::close();
    }

}
