<?php
$host = 'localhost';
$user = 'root';
$password = '5522';
$dbname = 'after_house';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
?>
