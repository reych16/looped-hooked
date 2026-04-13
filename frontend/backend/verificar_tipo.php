<?php
session_start();

echo json_encode([
    "esArtista" => isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'artista'
]);