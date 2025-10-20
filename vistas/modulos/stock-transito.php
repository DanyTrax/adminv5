<?php
// Vista principal de stock en tránsito - Redirigir según perfil
$perfilUsuario = $_SESSION["perfil"];
$idUsuario = $_SESSION["id"];

// Redirigir según el perfil del usuario
if($perfilUsuario == "Transportador") {
    // Mostrar vista específica para transportadores
    include "vistas/modulos/stock-transito-transportador.php";
} else {
    // Mostrar vista para usuarios (Administrador, Vendedor, etc.)
    include "vistas/modulos/stock-transito-usuarios.php";
}
?>