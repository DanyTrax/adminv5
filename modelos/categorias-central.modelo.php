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
            
            // Formatear datos para tabla categorias original
            foreach ($categorias as &$categoria) {
                // La tabla categorias original solo tiene: id, categoria, fecha
                $categoria['activo'] = true; // Todas las categorías están activas
                $categoria['sincronizado'] = true; // Asumir que están sincronizadas
                $categoria['descripcion'] = ''; // Campo no existe en tabla original
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
                    'message' => 'Error al eliminar la categoría'
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
    CARGAR CATEGORÍAS DESDE SUCURSALES
    =============================================*/
    static public function mdlCargarCategoriasDesdeSucursales() {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Obtener sucursales activas
            $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE activo = 1");
            $stmt->execute();
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $categoriasUnicas = [];
            
            foreach ($sucursales as $sucursal) {
                try {
                    // Conectar a la sucursal
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
                    $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
                    $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Obtener categorías de la sucursal
                    $stmt = $pdoSucursal->prepare("SELECT DISTINCT categoria FROM categorias ORDER BY categoria");
                    $stmt->execute();
                    $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Agregar categorías únicas
                    foreach ($categoriasSucursal as $categoria) {
                        $nombreCategoria = trim($categoria['categoria']);
                        if (!empty($nombreCategoria) && !in_array($nombreCategoria, $categoriasUnicas)) {
                            $categoriasUnicas[] = $nombreCategoria;
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("Error cargando categorías desde sucursal {$sucursal['nombre']}: " . $e->getMessage());
                }
            }
            
            // Insertar categorías únicas en la BD central
            $stmt = $pdo->prepare("INSERT INTO categorias (categoria, fecha) VALUES (?, NOW())");
            foreach ($categoriasUnicas as $categoria) {
                $stmt->execute([$categoria]);
            }
            
            return [
                'success' => true,
                'message' => "Cargadas " . count($categoriasUnicas) . " categorías desde sucursales",
                'categorias_cargadas' => count($categoriasUnicas)
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlCargarCategoriasDesdeSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error cargando categorías: ' . $e->getMessage()
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
            
            // Primero, cargar categorías desde sucursales hacia central si está vacía
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categorias");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total'] == 0) {
                // La tabla central está vacía, cargar desde sucursales
                self::mdlCargarCategoriasDesdeSucursales();
            }
            
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
                // La tabla categorias original solo tiene: id, categoria, fecha
                $categoria['activo'] = true; // Todas las categorías están activas
                $categoria['sincronizado'] = true; // Asumir que están sincronizadas
                $categoria['descripcion'] = ''; // Campo no existe en tabla original
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
