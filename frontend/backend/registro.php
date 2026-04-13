<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config/conexion.php';

function validarNombre($nombre) {
    return preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,100}$/u", $nombre);
}

function validarCedula($cedula) {
    return preg_match('/^[0-9]{9,12}$/', $cedula);
}

function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL);
}

function validarUsername($username) {
    return preg_match('/^[A-Za-z0-9_.]{4,20}$/', $username);
}

function validarTelefono($telefono) {
    return preg_match('/^[0-9]{8,15}$/', $telefono);
}

function validarPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

function validarEspecialidad($especialidad) {
    return preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s,]{3,100}$/u", $especialidad);
}

function validarMonto($valor) {
    return $valor === '' || (is_numeric($valor) && $valor >= 0);
}

$tipo = trim($_POST['tipo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$username = trim($_POST['username'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';

$especialidad = trim($_POST['especialidad'] ?? '');
$materiales = trim($_POST['materiales'] ?? '');
$manoObra = trim($_POST['manoObra'] ?? '');
$herramientas = trim($_POST['herramientas'] ?? '');
$empaque = trim($_POST['empaque'] ?? '');

if (empty($tipo) || empty($nombre) || empty($cedula) || empty($correo) || empty($username) || empty($telefono) || $password === '') {
    echo "Todos los campos obligatorios deben completarse";
    exit;
}

if (!in_array($tipo, ['cliente', 'artista'])) {
    echo "El tipo de usuario no es válido";
    exit;
}

if (!validarNombre($nombre)) {
    echo "El nombre completo debe tener entre 3 y 100 caracteres y solo contener letras y espacios";
    exit;
}

if (!validarCedula($cedula)) {
    echo "La cédula debe contener solo números y tener entre 9 y 12 dígitos";
    exit;
}

if (!validarCorreo($correo)) {
    echo "El correo electrónico no es válido";
    exit;
}

if (!validarUsername($username)) {
    echo "El nombre de usuario debe tener entre 4 y 20 caracteres y solo contener letras, números, punto o guion bajo";
    exit;
}

if (!validarTelefono($telefono)) {
    echo "El teléfono debe contener solo números y tener entre 8 y 15 dígitos";
    exit;
}

if (!validarPassword($password)) {
    echo "La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial";
    exit;
}

if ($tipo === "artista") {
    if (empty($especialidad)) {
        echo "La especialidad es obligatoria para artistas";
        exit;
    }

    if (!validarEspecialidad($especialidad)) {
        echo "La especialidad solo puede contener letras, espacios y comas";
        exit;
    }

    if (!validarMonto($materiales) || !validarMonto($manoObra) || !validarMonto($herramientas) || !validarMonto($empaque)) {
        echo "Los costos del artista deben ser números válidos y no negativos";
        exit;
    }
}

$sqlCheck = "SELECT id FROM usuarios WHERE username = :username OR correo = :correo OR cedula = :cedula";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->bindParam(':username', $username);
$stmtCheck->bindParam(':correo', $correo);
$stmtCheck->bindParam(':cedula', $cedula);
$stmtCheck->execute();

if ($stmtCheck->fetch()) {
    echo "La cédula, el correo o el nombre de usuario ya están registrados";
    exit;
}

$rol = ($tipo === "artista") ? "artista" : "cliente";
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$materiales = ($materiales === '') ? 0 : $materiales;
$manoObra = ($manoObra === '') ? 0 : $manoObra;
$herramientas = ($herramientas === '') ? 0 : $herramientas;
$empaque = ($empaque === '') ? 0 : $empaque;

try {
    $conexion->beginTransaction();

    $sql = "INSERT INTO usuarios 
    (nombre_completo, cedula, correo, username, telefono, password_hash, rol, estado)
    VALUES 
    (:nombre, :cedula, :correo, :username, :telefono, :password, :rol, 'activo')";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':password', $passwordHash);
    $stmt->bindParam(':rol', $rol);

    $stmt->execute();

    $user_id = $conexion->lastInsertId();

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

    $conexion->commit();
    echo "OK";

} catch (PDOException $e) {
    $conexion->rollBack();
    echo "Error al registrar";
}
?>