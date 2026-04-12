<?php
session_start();

echo json_encode([
    "logueado" => isset($_SESSION['usuario'])
]);