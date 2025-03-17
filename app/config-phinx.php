<?php
define('APP_NAME',   "Gestores");
define('DB_ADAPTER', "mysql");
// Cambia "database" por "127.0.0.1" si tu base de datos está en el mismo servidor
define('DB_HOST',    "database");
define('DB_NAME',    "grepo");
define('DB_PORT',    "3306");
define('DB_USER',    "root");
define('DB_PASS',    "elaverde");
define('DB_CHARSET', "utf8");
define('DB_COLLATE', "utf8_unicode_ci");
define('DB_PREFIX',  "");

$config = [
    'phinx' =>  [
        'paths' => [
            'migrations' => 'database/migrations'
        ],
        'migration_base_class' => '\Phinx\Migration\AbstractMigration',
        'environments' => [
            'default_environment' => 'dev',
            'dev' => [
                'adapter' => DB_ADAPTER,
                'host'    => DB_HOST,
                'name'    => DB_NAME,
                'user'    => DB_USER,
                'pass'    => DB_PASS,
                'port'    => DB_PORT,
                'charset' => DB_CHARSET,
                // Opcional: 'collation' => DB_COLLATE,
            ]
        ]
    ]
];

return $config['phinx'];
