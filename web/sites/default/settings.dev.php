<?php

$driver = "mysql";

$settings['migrate_source_connection'] = 'migrate';
$settings['migrate_source_version'] = '7';
$settings['migrate_file_public_path'] = '/var/www/html/web';
$settings['migrate_file_private_path'] = '';

$databases['migrate']['default'] = [
  'database' => getenv('DB_MIGRATE_NAME'),
  'username' => getenv('DB_MIGRATE_USER'),
  'password' => getenv('DB_MIGRATE_PASSWORD'),
  'host' => getenv('DB_MIGRATE_HOST'),
  'driver' => $driver,
  'port' => getenv('DB_MIGRATE_PORT'),
  'prefix' => '',
];

$databases['default']['default'] = array (
  'database' => getenv('DATABASE_NAME'),
  'username' => getenv('DATABASE_USER'),
  'password' => getenv('DATABASE_PASSWORD'),
  'prefix' => '',
  'host' => getenv('DATABASE_HOST'),
  'port' => getenv('DATABASE_PORT'),
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);
$settings['config_sync_directory'] = '../config/sync';
