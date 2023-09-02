<?php

function createDatabaseConnection()
{
    // Load the database configuration from config/database.php
    // $databaseConfig = require_once  'db-config.php';

    $databaseConfig = [
        'host' => '127.0.0.1',
        'name' => 'book_app',
        'user' => 'gpz0wlayf67y',
        'pass' => 'I6fQbn@W4p@w'
    ];
    
    // Get the database connection details from the configuration
    $dbHost = $databaseConfig['host'];
    $dbName = $databaseConfig['name'];
    $dbUser = $databaseConfig['user'];
    $dbPass = $databaseConfig['pass'];

    try {
        $dsn = "mysql:host=$dbHost;dbname=book_app";
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        // Set PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        die();
    }
    return $pdo;
}
