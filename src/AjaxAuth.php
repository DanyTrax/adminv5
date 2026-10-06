<?php

/**
 * Guards de sesión/perfil para endpoints AJAX.
 */
class AjaxAuth
{
    public static function requireSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["success" => false, "error" => "Sesión no válida"]);
            exit;
        }
    }

    public static function requireProfiles(array $profiles): void
    {
        self::requireSession();
        $perfil = $_SESSION["perfil"] ?? "";
        if (!in_array($perfil, $profiles, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["success" => false, "error" => "Sin permiso"]);
            exit;
        }
    }
}
