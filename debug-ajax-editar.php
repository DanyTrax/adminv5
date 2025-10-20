<?php
// Script para debuggear el AJAX de editar sucursal
session_start();

// Simular sesión de administrador
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug AJAX Editar Sucursal</h2>";

// Simular la petición AJAX
$_POST["idSucursal"] = "8"; // ID de la sucursal Pruebas

echo "<h3>📋 Simulando petición AJAX para sucursal ID: 8</h3>";

require_once __DIR__ . "/controladores/sucursales.controlador.php";
require_once __DIR__ . "/modelos/sucursales.modelo.php";

// Probar el modelo directamente
echo "<h3>🔧 Probando modelo mdlMostrarSucursal:</h3>";
$sucursal = ModeloSucursales::mdlMostrarSucursal("id", 8);

if ($sucursal) {
    echo "<pre>";
    print_r($sucursal);
    echo "</pre>";
    
    echo "<h3>📊 Campos de conexión específicos:</h3>";
    echo "<ul>";
    echo "<li><strong>usuario_bd:</strong> " . ($sucursal['usuario_bd'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>password_bd:</strong> " . ($sucursal['password_bd'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>nombre_bd:</strong> " . ($sucursal['nombre_bd'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>host_bd:</strong> " . ($sucursal['host_bd'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>puerto_bd:</strong> " . ($sucursal['puerto_bd'] ?? 'NO DEFINIDO') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ No se encontró la sucursal</p>";
}

// Probar el AJAX completo
echo "<h3>🔧 Probando AJAX completo:</h3>";

// Simular la clase AjaxSucursales
class AjaxSucursales {
    public $idSucursal;
    
    public function ajaxEditarSucursal() {
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->idSucursal)) {
            echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
            return;
        }

        try {
            $sucursal = ModeloSucursales::mdlMostrarSucursal("id", $this->idSucursal);
            
            if ($sucursal) {
                echo json_encode([
                    'success' => true,
                    'data' => $sucursal
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxEditarSucursal: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener sucursal: ' . $e->getMessage()
            ]);
        }
    }
}

$ajax = new AjaxSucursales();
$ajax->idSucursal = 8;
$ajax->ajaxEditarSucursal();
?>
