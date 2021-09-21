<?php

require_once __DIR__ . '/../vendor/autoload.php';

$migration = __DIR__ . '/migration.php';

$db = new App\Config\Database();
$connection = $db->getConnection();

$databaseCreateTablesCode = include_once $migration;

foreach($databaseCreateTablesCode as $tableName => $createCode) {

    $prepare = $connection->prepare($createCode);

    if($prepare->execute()) {
        echo $tableName . ' table created' . PHP_EOL;
        continue;
    }

    echo 'faild create ' . $tableName . ' table' . PHP_EOL;
}
