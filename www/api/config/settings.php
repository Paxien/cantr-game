<?php

// Define root path
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
defined('ROOT') ?: define('ROOT', DS);

// Load .env file
if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable('.');
    $dotenv->load();
}

return [
    'settings' => [
        'displayErrorDetails'    => getenv('APP_DEBUG') === 'true', // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // App Settings
        'app'                    => [
            'name' => getenv('APP_NAME'),
            'url'  => getenv('APP_URL'),
            'env'  => getenv('APP_ENV'),
        ],

        // Monolog settings
        'logger'                 => [
            'name'  => getenv('APP_NAME'),
            'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Database settings
        'db'               => [
            'driver'    => getenv('DB_CONNECTION'),
            'host'      => getenv('DB_HOST'),
            'database'  => getenv('DB_DATABASE'),
            'username'  => getenv('DB_USERNAME'),
            'password'  => getenv('DB_PASSWORD'),
            'port'      => getenv('DB_PORT'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'cors' => null !== getenv('CORS_ALLOWED_ORIGINS') ? getenv('CORS_ALLOWED_ORIGINS') : '*',

        // jwt settings
        'jwt'  => [
            'secret' => getenv('JWT_SECRET'),
            'secure' => false,
            "header" => "Authorization",
            "regexp" => "/Token\s+(.*)$/i",
            'passthrough' => ['OPTIONS'],
            'expiry' => time() + (86400 * 14), // 1 day x 14
        ],
    ],
];