<?php
require 'conexion.php';

$username = "admin";
$password = "Admin123*";

$sql = "SELECT * FROM usuarios WHERE username = :username AND estado = 'activo'";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($user);
echo "</pre>";

if ($user && $password === $user['password_hash']) {
    echo "LOGIN CORRECTO ✅";
} else {
    echo "LOGIN INCORRECTO ❌";
}
?>