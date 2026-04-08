<?php
if (getenv("MYSQL_ADDON_HOST")) {
    // 🌍 Clever Cloud
    $host = getenv("MYSQL_ADDON_HOST");
    $db   = getenv("MYSQL_ADDON_DB");
    $port = getenv("MYSQL_ADDON_PORT");
    $user = getenv("MYSQL_ADDON_USER");
    $pass = getenv("MYSQL_ADDON_PASSWORD");

} else {
    // 💻 Local (XAMPP)
    $host = "localhost";
    $db   = "looped_hooked";
    $port = "3306";
    $user = "root";
    $pass = "root1234";
}

try {
    $conexion = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
        $user,
        $pass
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>