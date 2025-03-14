<?php

$host = "ddev-d7icbf-db";
$port = 3306;
$driver = "mysql";

$settings['migrate_source_connection'] = 'migrate';
$settings['migrate_source_version'] = '7';
$settings['migrate_file_public_path'] = '/var/www/html/web';
$settings['migrate_file_private_path'] = '';

$databases['migrate']['default'] = [
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'host' => $host,
  'driver' => $driver,
  'port' => $port,
  'prefix' => '',
];
$settings['config_sync_directory'] = '../config/sync';
