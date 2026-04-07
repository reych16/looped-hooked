<?php
session_start();
require 'conexion.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE username = :username AND estado = 'activo'";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $password === $user['password_hash']) {

    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['rol'] = $user['rol'];

    echo "OK";

} else {
    echo "ERROR";
}
?>