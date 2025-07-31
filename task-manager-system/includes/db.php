<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'gestor_tarefas';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação à base de dados: " . $conn->connect_error);
}
?>