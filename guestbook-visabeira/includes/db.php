<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'guestbook';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação à base de dados: " . $conn->connect_error);
}
?>