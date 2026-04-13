<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config/conexion.php';

function validarCedula($cedula) {
    return preg_match('/^[0-9]{9,12}$/', $cedula);
}

function validarPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

$cedula = trim($_POST['cedula'] ?? '');
$nuevaPassword = $_POST['nuevaPassword'] ?? '';
$confirmarPassword = $_POST['confirmarPassword'] ?? '';

if ($cedula === '' || $nuevaPassword === '' || $confirmarPassword === '') {
    echo "Todos los campos son obligatorios";
    exit;
}

if (!validarCedula($cedula)) {
    echo "La cédula no es válida";
    exit;
}

if (!validarPassword($nuevaPassword)) {
    echo "La nueva contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial";
    exit;
}

if ($nuevaPassword !== $confirmarPassword) {
    echo "La confirmación de contraseña no coincide";
    exit;
}

$sqlBuscar = "SELECT id, password_hash FROM usuarios WHERE cedula = :cedula AND estado = 'activo'";
$stmtBuscar = $conexion->prepare($sqlBuscar);
$stmtBuscar->bindParam(':cedula', $cedula);
$stmtBuscar->execute();

$usuario = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "No se encontró un usuario activo con esa cédula";
    exit;
}

if (password_verify($nuevaPassword, $usuario['password_hash'])) {
    echo "La nueva contraseña no puede ser igual a la actual";
    exit;
}

$passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

$sqlUpdate = "UPDATE usuarios SET password_hash = :password WHERE cedula = :cedula";
$stmtUpdate = $conexion->prepare($sqlUpdate);
$stmtUpdate->bindParam(':password', $passwordHash);
$stmtUpdate->bindParam(':cedula', $cedula);

if ($stmtUpdate->execute()) {
    echo "OK";
} else {
    echo "No se pudo actualizar la contraseña";
}
?>