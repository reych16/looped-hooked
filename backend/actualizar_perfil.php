<?php
session_start();
include("../backend/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['usuario_id'];

// =========================
// 🔹 FORMULARIO USUARIO
// =========================
if (isset($_POST['nombre'])) {

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $username = $_POST['username'];
    $telefono = $_POST['telefono'];
    $password_actual = $_POST['password_actual'];

    // Traer usuario actual
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Usuario no encontrado";
        exit();
    }

    // 🔐 Verificar contraseña
    if (!password_verify($password_actual, $user['password_hash'])) {
        $_SESSION['errores'] = ["Contraseña actual incorrecta"];
        header("Location: ../frontend/perfil.php");
        exit();
    }

    $rutaFoto = $user['foto_perfil']; // mantener la actual por defecto

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {

        $carpeta = "../frontend/img/perfiles/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES['foto_perfil']['name']);
        $rutaArchivo = $carpeta . $nombreArchivo;

        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaArchivo);

        // ruta que se guarda en BD
        $rutaFoto = "img/perfiles/" . $nombreArchivo;
    }

    // ✅ Update correcto
    $stmt = $conexion->prepare("
        UPDATE usuarios 
        SET nombre_completo = ?, correo = ?, username = ?, telefono = ?, foto_perfil = ?
        WHERE id = ?
    ");

    $stmt->execute([$nombre, $correo, $username, $telefono, $rutaFoto, $id]);

    $_SESSION['username'] = $username;

    header("Location: ../frontend/perfil.php");
    exit();
}


// =========================
// 🔹 FORMULARIO ARTISTA
// =========================
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'artista' && isset($_POST['especialidad'])) {

    $especialidad = $_POST['especialidad'] ?? null;
    $costo_materiales = $_POST['costo_materiales'] ?? 0;
    $costo_mano_obra = $_POST['costo_mano_obra'] ?? 0;
    $costo_herramientas = $_POST['costo_herramientas'] ?? 0;
    $costo_empaque = $_POST['costo_empaque'] ?? 0;
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    // Verificar si existe
    $stmt = $conexion->prepare("SELECT id FROM artistas WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->fetch()) {

        $update = $conexion->prepare("
            UPDATE artistas SET
                especialidad = ?,
                costo_materiales = ?,
                costo_mano_obra = ?,
                costo_herramientas = ?,
                costo_empaque = ?,
                disponible = ?
            WHERE id = ?
        ");

        $update->execute([
            $especialidad,
            $costo_materiales,
            $costo_mano_obra,
            $costo_herramientas,
            $costo_empaque,
            $disponible,
            $id
        ]);
    } else {

        $insert = $conexion->prepare("
            INSERT INTO artistas (
                id, especialidad, costo_materiales,
                costo_mano_obra, costo_herramientas,
                costo_empaque, disponible
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $id,
            $especialidad,
            $costo_materiales,
            $costo_mano_obra,
            $costo_herramientas,
            $costo_empaque,
            $disponible
        ]);
    }

    header("Location: ../frontend/perfil.php");
    exit();
}
