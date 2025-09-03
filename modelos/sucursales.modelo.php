<?php

class ModeloSucursales {

    /*=============================================
    OBTENER TODAS LAS SUCURSALES DESDE LA API
    =============================================*/
    static public function mdlObtenerSucursales() {
        try {
            $apiUrl = API_URL . "obtener_sucursales.php";
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 15,
                    'header' => 'User-Agent: AdminV5-Model/1.0'
                ]
            ]);
            
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API de sucursales');
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursales: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al obtener sucursales: ' . $e->getMessage(), 'data' => []];
        }
    }

    /*=============================================
    CREAR NUEVA SUCURSAL EN LA API
    =============================================*/
    static public function mdlCrearSucursal($datos) {
        // Esta función llama a tu API para crear una sucursal.
        // Asumimos que el endpoint es 'crear_sucursal.php'
        return self::enviarDatosApi('crear_sucursal.php', $datos);
    }

    /*=============================================
    ACTUALIZAR SUCURSAL EXISTENTE EN LA API
    =============================================*/
    static public function mdlActualizarSucursal($datos) {
        // Esta función llama a tu API para actualizar una sucursal.
        // Asumimos que el endpoint es 'actualizar_sucursal.php'
        return self::enviarDatosApi('actualizar_sucursal.php', $datos);
    }

    /*=============================================
    ELIMINAR SUCURSAL EN LA API
    =============================================*/
    static public function mdlEliminarSucursal($id) {
        // Esta función llama a tu API para eliminar una sucursal.
        // Asumimos que el endpoint es 'eliminar_sucursal.php'
        return self::enviarDatosApi('eliminar_sucursal.php', ['id' => $id]);
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    static public function mdlProbarConexionSucursal($apiUrl) {
        return self::enviarDatosApi('probar_conexion_sucursal.php', ['api_url' => $apiUrl]);
    }
    
    /*=============================================
    FUNCIÓN PRIVADA PARA ENVIAR DATOS A LA API
    =============================================*/
    private static function enviarDatosApi($endpoint, $datos) {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($datos),
                    'timeout' => 15
                ]
            ]);
            
            $apiUrl = API_URL . $endpoint;
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) throw new Exception('No se pudo conectar con la API en ' . $endpoint);
            
            $resultado = json_decode($response, true);
            
            if (!$resultado || !isset($resultado['success'])) throw new Exception('Respuesta inválida de la API en ' . $endpoint);
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en enviarDatosApi ($endpoint): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de comunicación con la API: ' . $e->getMessage()];
        }
    }
}