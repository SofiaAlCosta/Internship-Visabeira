<?php
$servidor = "localhost";
$utilizador = "root";
$senha = "";
$base_dados = "reserva_voos";

$conn = new mysqli($servidor, $utilizador, $senha, $base_dados);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

?>