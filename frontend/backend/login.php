<?php
session_start();
require 'config/conexion.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo "ERROR|Debes completar usuario y contraseña";
    exit;
}

$sql = "SELECT * FROM usuarios WHERE username = :username AND estado = 'activo'";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ERROR|No se encontró usuario activo con ese username";
    exit;
}

if (password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['rol'] = $user['rol'];

    echo "OK";
} else {
    echo "ERROR|La contraseña no coincide";
}
?>