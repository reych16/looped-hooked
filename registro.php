<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

// ✅ RECIBIR DATOS CORRECTAMENTE
$tipo = $_POST['tipo'];
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$correo = $_POST['correo'];
$username = $_POST['username'];
$telefono = $_POST['telefono'];
$password = $_POST['password'];

$especialidad = $_POST['especialidad'];
$materiales = $_POST['materiales'];
$manoObra = $_POST['manoObra'];
$herramientas = $_POST['herramientas'];
$empaque = $_POST['empaque'];

// 🔍 verificar si ya existe
$sqlCheck = "SELECT id FROM usuarios WHERE username = :username";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->bindParam(':username', $username);
$stmtCheck->execute();

if ($stmtCheck->fetch()) {
    echo "El usuario ya existe";
    exit;
}

// 🧾 insertar usuario
$sql = "INSERT INTO usuarios 
(nombre_completo, cedula, correo, username, telefono, password_hash, rol, estado)
VALUES 
(:nombre, :cedula, :correo, :username, :telefono, :password, :rol, 'activo')";

$stmt = $conexion->prepare($sql);

$rol = ($tipo === "artista") ? "artista" : "cliente";

$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':cedula', $cedula);
$stmt->bindParam(':correo', $correo);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':telefono', $telefono);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':rol', $rol);

if ($stmt->execute()) {

    $user_id = $conexion->lastInsertId();

    // 🎨 si es artista
    if ($tipo === "artista") {
        $sqlArtista = "INSERT INTO artistas 
        (id, especialidad, costo_materiales, costo_mano_obra, costo_herramientas, costo_empaque)
        VALUES 
        (:id, :especialidad, :materiales, :manoObra, :herramientas, :empaque)";

        $stmtArtista = $conexion->prepare($sqlArtista);

        $stmtArtista->bindParam(':id', $user_id);
        $stmtArtista->bindParam(':especialidad', $especialidad);
        $stmtArtista->bindParam(':materiales', $materiales);
        $stmtArtista->bindParam(':manoObra', $manoObra);
        $stmtArtista->bindParam(':herramientas', $herramientas);
        $stmtArtista->bindParam(':empaque', $empaque);

        $stmtArtista->execute();
    }

    echo "OK";

} else {
    echo "Error al registrar";
}
?>