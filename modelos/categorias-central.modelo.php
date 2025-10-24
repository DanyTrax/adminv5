<?php

require_once "conexion.php";

class ModeloCategoriasCentral {

    /*=============================================
    OBTENER TODAS LAS CATEGORÍAS CENTRALES
    =============================================*/
    static public function mdlObtenerCategoriasCentral($soloActivas = false) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $sql = "SELECT * FROM categorias";
            $params = [];
            
            // La tabla categorias original no tiene campo activo, mostrar todas
            
            $sql .= " ORDER BY categoria ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos
            foreach ($categorias as &$categoria) {
                $categoria['activo'] = (bool)$categoria['activo'];
                $categoria['sincronizado'] = (bool)$categoria['sincronizado'];
            }
            
            return [
                'success' => true,
                'message' => 'Categorías obtenidas correctamente',
                'data' => $categorias
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCategoriasCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /*=============================================
    CREAR CATEGORÍA CENTRAL
    =============================================*/
    static public function mdlCrearCategoriaCentral($datos) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("
                INSERT INTO categorias (categoria, fecha) 
                VALUES (?, NOW())
            ");
            
            $resultado = $stmt->execute([
                $datos['categoria']
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Categoría creada correctamente',
                    'id' => $pdo->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la categoría'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearCategoriaCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    EDITAR CATEGORÍA CENTRAL
    =============================================*/
    static public function mdlEditarCategoriaCentral($id, $datos) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("
                UPDATE categorias SET 
                    categoria = ?
                WHERE id = ?
            ");
            
            $resultado = $stmt->execute([
                $datos['categoria'],
                $id
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Categoría actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la categoría'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEditarCategoriaCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ELIMINAR CATEGORÍA CENTRAL
    =============================================*/
    static public function mdlEliminarCategoriaCentral($id) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Eliminar directamente de la tabla categorias original
            $stmt = $pdo->prepare("
                DELETE FROM categorias WHERE id = ?
            ");
            
            $resultado = $stmt->execute([$id]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Categoría eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al desactivar la categoría'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarCategoriaCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    static public function mdlSincronizarCategoriasSucursales() {
        try {
            // Limpiar buffer de salida solo si existe
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Obtener todas las categorías
            $stmt = $pdo->prepare("SELECT * FROM categorias ORDER BY categoria");
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener sucursales activas
            $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE activo = 1");
            $stmt->execute();
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sucursalesSincronizadas = 0;
            $errores = [];
            
            foreach ($sucursales as $sucursal) {
                try {
                    // Conectar a la sucursal
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
                    $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
                    $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Limpiar categorías existentes en la sucursal
                    $stmt = $pdoSucursal->prepare("DELETE FROM categorias");
                    $stmt->execute();
                    
                    // Insertar categorías centrales en la sucursal manteniendo los IDs
                    $stmt = $pdoSucursal->prepare("INSERT INTO categorias (id, categoria, fecha) VALUES (?, ?, NOW())");
                    
                    foreach ($categorias as $categoria) {
                        $stmt->execute([$categoria['id'], $categoria['categoria']]);
                    }
                    
                    $sucursalesSincronizadas++;
                    
                } catch (Exception $e) {
                    $errores[] = "Error en sucursal {$sucursal['nombre']}: " . $e->getMessage();
                }
            }
            
            // La tabla categorias original no tiene campo sincronizado
            
            return [
                'success' => true,
                'message' => "Sincronización completada. {$sucursalesSincronizadas} sucursales actualizadas.",
                'sucursales_sincronizadas' => $sucursalesSincronizadas,
                'total_sucursales' => count($sucursales),
                'errores' => $errores
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlSincronizarCategoriasSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al sincronizar categorías: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    OBTENER CATEGORÍA ESPECÍFICA
    =============================================*/
    static public function mdlObtenerCategoriaCentral($id) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($categoria) {
                $categoria['activo'] = (bool)$categoria['activo'];
                $categoria['sincronizado'] = (bool)$categoria['sincronizado'];
            }
            
            return $categoria;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCategoriaCentral: " . $e->getMessage());
            return null;
        }
    }

    /*=============================================
    VERIFICAR SI CATEGORÍA EXISTE
    =============================================*/
    static public function mdlVerificarCategoriaExistente($categoria, $excluirId = null) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $sql = "SELECT id FROM categorias WHERE categoria = ?";
            $params = [$categoria];
            
            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() !== false;
            
        } catch (Exception $e) {
            error_log("Error en mdlVerificarCategoriaExistente: " . $e->getMessage());
            return false;
        }
    }
}
?>
