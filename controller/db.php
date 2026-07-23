<?php

$servername = 'localhost';
$username = 'root';
$dbPassword = '';
$dbname = 'docuflow_db';

$conn = new mysqli($servername, $username, $dbPassword, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
