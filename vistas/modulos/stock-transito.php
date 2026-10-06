<?php
// Vista principal de stock en tránsito - Redirigir según perfil
$perfilUsuario = $_SESSION["perfil"];
$idUsuario = $_SESSION["id"];

// Redirigir según el perfil del usuario
if ($perfilUsuario == "Transportador") {
    include __DIR__ . "/stock-transito-transportador.php";
} else {
    include __DIR__ . "/stock-transito-usuarios.php";
}
?>