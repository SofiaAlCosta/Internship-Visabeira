<?php
session_start();

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit();
}
?>