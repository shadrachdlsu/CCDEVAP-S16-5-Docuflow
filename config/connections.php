<?php
declare(strict_types=1);

$databaseHost = getenv('DOCUFLOW_DB_HOST') ?: 'localhost';
$databaseName = getenv('DOCUFLOW_DB_NAME') ?: 'docuflow_db';
$databaseUser = getenv('DOCUFLOW_DB_USER') ?: 'root';
$databasePassword = getenv('DOCUFLOW_DB_PASSWORD') ?: '';

$pdo = new PDO(
    "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
    $databaseUser,
    $databasePassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
