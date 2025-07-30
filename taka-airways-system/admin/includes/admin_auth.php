<?php
session_start();
if (!isset($_SESSION["utilizador_id"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}
?>