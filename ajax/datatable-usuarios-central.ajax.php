<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers JSON
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";

class AjaxTablaUsuariosCentral {

    /*=============================================
    MOSTRAR LA TABLA DE USUARIOS CENTRALES
    =============================================*/
    public function mostrarTablaUsuariosCentral() {

        try {
            $usuarios = ModeloUsuariosCentral::mdlObtenerUsuariosCentral();
            
            $datosJson = '{"data": [';
            
            foreach ($usuarios as $key => $value) {
                
                $fechaCreacion = date('d/m/Y H:i', strtotime($value["fecha_creacion"]));
                
                $estadoSincronizacion = $this->formatearEstadoSincronizacion($value["sincronizado"]);
                
                $botones = $this->generarBotonesAccion($value);
                
                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . htmlspecialchars($value["usuario"]) . '",
                    "' . htmlspecialchars($value["nombre"]) . '",
                    "' . $this->formatearPerfil($value["perfil"]) . '",
                    "' . htmlspecialchars($value["nombre_sucursal"] ?? 'N/A') . '",
                    "' . htmlspecialchars($value["telefono"] ?? 'N/A') . '",
                    "' . $estadoSincronizacion . '",
                    "' . $fechaCreacion . '",
                    "' . $botones . '"
                ],';
            }
            
            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= ']}';
            
            echo $datosJson;

        } catch(Exception $e) {
            echo '{"data": [], "error": "' . $e->getMessage() . '"}';
        }
    }

    /*=============================================
    FORMATEAR PERFIL
    =============================================*/
    private function formatearPerfil($perfil) {
        
        $configuraciones = [
            'Administrador' => ['icon' => 'fa-shield', 'color' => 'label-danger'],
            'Vendedor' => ['icon' => 'fa-user', 'color' => 'label-success'],
            'Contador' => ['icon' => 'fa-calculator', 'color' => 'label-info'],
            'Transportador' => ['icon' => 'fa-truck', 'color' => 'label-warning'],
            'Limitado' => ['icon' => 'fa-lock', 'color' => 'label-default']
        ];
        
        $config = $configuraciones[$perfil] ?? ['icon' => 'fa-user', 'color' => 'label-default'];
        
        return '<span class="label ' . $config['color'] . '">' . 
               '<i class="fa ' . $config['icon'] . '"></i> ' . $perfil . 
               '</span>';
    }

    /*=============================================
    FORMATEAR ESTADO DE SINCRONIZACIÓN
    =============================================*/
    private function formatearEstadoSincronizacion($sincronizado) {
        
        if ($sincronizado) {
            return '<span class="label label-success">' . 
                   '<i class="fa fa-check"></i> Sincronizado' . 
                   '</span>';
        } else {
            return '<span class="label label-warning">' . 
                   '<i class="fa fa-clock-o"></i> Pendiente' . 
                   '</span>';
        }
    }

    /*=============================================
    GENERAR BOTONES DE ACCIÓN
    =============================================*/
    private function generarBotonesAccion($usuario) {
        
        $botones = '';
        
        // Botón Editar
        $botones .= '<button class="btn btn-warning btn-xs btnEditarUsuarioCentral" ' .
                   'idUsuario="' . $usuario["id"] . '" ' .
                   'title="Editar usuario">' .
                   '<i class="fa fa-pencil"></i>' .
                   '</button> ';
        
        // Botón Sincronizar (solo si no está sincronizado)
        if (!$usuario["sincronizado"]) {
            $botones .= '<button class="btn btn-info btn-xs btnSincronizarUsuario" ' .
                       'idUsuario="' . $usuario["id"] . '" ' .
                       'title="Sincronizar usuario">' .
                       '<i class="fa fa-refresh"></i>' .
                       '</button> ';
        }
        
        // Botón Eliminar
        $botones .= '<button class="btn btn-danger btn-xs btnEliminarUsuarioCentral" ' .
                   'idUsuario="' . $usuario["id"] . '" ' .
                   'usuario="' . htmlspecialchars($usuario["usuario"]) . '" ' .
                   'title="Eliminar usuario">' .
                   '<i class="fa fa-times"></i>' .
                   '</button>';
        
        return $botones;
    }
}

/*=============================================
EJECUTAR AJAX
=============================================*/
$tablaUsuarios = new AjaxTablaUsuariosCentral();
$tablaUsuarios->mostrarTablaUsuariosCentral();
?>
