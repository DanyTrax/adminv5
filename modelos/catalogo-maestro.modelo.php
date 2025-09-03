<?php
require_once "conexion.php";

class ModeloCatalogoMaestro {
    
    /*=============================================
    CONEXIÓN A BASE CENTRAL
    =============================================*/
    static private function conectarCentral() {
        try {
            $servidor = "localhost";
            $nombreBD = "epicosie_central";
            $usuario = "epicosie_central";
            $password = "=Nf?M#6A'QU&.6c";
            
            $link = new PDO(
                "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password
            );
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $link;
        } catch (PDOException $e) {
            die("Error de conexión central: " . $e->getMessage());
        }
    }
    
    /*=============================================
    MOSTRAR CATÁLOGO MAESTRO
    =============================================*/
    static public function mdlMostrarCatalogoMaestro($item, $valor) {
        $db = self::conectarCentral();
        
        if($item != null) {
            $stmt = $db->prepare("
                SELECT cm.*, c.categoria as nombre_categoria 
                FROM catalogo_maestro cm 
                LEFT JOIN categorias c ON cm.id_categoria = c.id 
                WHERE cm.$item = :$item AND cm.activo = 1
            ");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = $db->prepare("
                SELECT cm.*, c.categoria as nombre_categoria,
                       mitad.descripcion as descripcion_mitad,
                       mitad.precio_venta as precio_mitad,
                       tercio.descripcion as descripcion_tercio,
                       tercio.precio_venta as precio_tercio,
                       cuarto.descripcion as descripcion_cuarto,
                       cuarto.precio_venta as precio_cuarto
                FROM catalogo_maestro cm 
                LEFT JOIN categorias c ON cm.id_categoria = c.id 
                LEFT JOIN catalogo_maestro mitad ON cm.codigo_hijo_mitad = mitad.codigo
                LEFT JOIN catalogo_maestro tercio ON cm.codigo_hijo_tercio = tercio.codigo
                LEFT JOIN catalogo_maestro cuarto ON cm.codigo_hijo_cuarto = cuarto.codigo
                WHERE cm.activo = 1 
                ORDER BY CAST(cm.codigo AS UNSIGNED) ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $stmt = null;
    }
    
    /*=============================================
    CREAR PRODUCTO EN CATÁLOGO MAESTRO
    =============================================*/
    static public function mdlCrearProductoMaestro($datos) {
        $db = self::conectarCentral();
        
        $stmt = $db->prepare("
            INSERT INTO catalogo_maestro (codigo, descripcion, id_categoria, precio_venta, imagen, es_divisible, codigo_hijo_mitad, codigo_hijo_tercio, codigo_hijo_cuarto) 
            VALUES (:codigo, :descripcion, :id_categoria, :precio_venta, :imagen, :es_divisible, :codigo_hijo_mitad, :codigo_hijo_tercio, :codigo_hijo_cuarto)
        ");

        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
        $stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
        $stmt->bindParam(":es_divisible", $datos["es_divisible"], PDO::PARAM_INT);
        $stmt->bindParam(":codigo_hijo_mitad", $datos["codigo_hijo_mitad"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_hijo_tercio", $datos["codigo_hijo_tercio"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_hijo_cuarto", $datos["codigo_hijo_cuarto"], PDO::PARAM_STR);

        if($stmt->execute()) {
            // Marcar productos hijos como hijos y asignar padre
            self::mdlMarcarComoHijo($datos["codigo_hijo_mitad"], $datos["codigo"], 'mitad');
            self::mdlMarcarComoHijo($datos["codigo_hijo_tercio"], $datos["codigo"], 'tercio');
            self::mdlMarcarComoHijo($datos["codigo_hijo_cuarto"], $datos["codigo"], 'cuarto');
            return "ok";
        } else {
            return "error";
        }
        $stmt = null;
    }
    
    /*=============================================
    MARCAR PRODUCTO COMO HIJO
    =============================================*/
    static private function mdlMarcarComoHijo($codigoHijo, $codigoPadre, $tipoDivision) {
        if (!empty($codigoHijo)) {
            $db = self::conectarCentral();
            $stmt = $db->prepare("
                UPDATE catalogo_maestro 
                SET es_hijo = 1, codigo_padre = :codigo_padre, tipo_division = :tipo_division 
                WHERE codigo = :codigo_hijo
            ");
            $stmt->bindParam(":codigo_hijo", $codigoHijo, PDO::PARAM_STR);
            $stmt->bindParam(":codigo_padre", $codigoPadre, PDO::PARAM_STR);
            $stmt->bindParam(":tipo_division", $tipoDivision, PDO::PARAM_STR);
            $stmt->execute();
            $stmt = null;
        }
    }
    
/*=============================================
MOSTRAR PRODUCTOS MAESTROS - MEJORADO
=============================================*/

public static function mdlMostrarProductosMaestros($item, $valor) {
    
    try {
        
        if($item != null && $valor != null) {
            
            $stmt = self::conectarCentral()->prepare("
                SELECT 
                    cm.*,
                    c.categoria,
                    cm.es_divisible,
                    cm.codigo_hijo_mitad,
                    cm.codigo_hijo_tercio,
                    cm.codigo_hijo_cuarto
                FROM catalogo_maestro cm 
                LEFT JOIN categorias c ON cm.id_categoria = c.id 
                WHERE cm.$item = :valor 
                AND cm.activo = 1
            ");
            
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } else {
            
            $stmt = self::conectarCentral()->prepare("
                SELECT 
                    cm.*,
                    c.categoria,
                    cm.es_divisible,
                    cm.codigo_hijo_mitad,
                    cm.codigo_hijo_tercio,
                    cm.codigo_hijo_cuarto
                FROM catalogo_maestro cm 
                LEFT JOIN categorias c ON cm.id_categoria = c.id 
                WHERE cm.activo = 1 
                ORDER BY cm.codigo ASC
            ");
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
    } catch (Exception $e) {
        
        error_log("Error al mostrar productos maestros: " . $e->getMessage());
        return false;
        
    } finally {
        
        $stmt = null;
    }
}
    
/*=============================================
OBTENER SIGUIENTE CÓDIGO AUTOMÁTICO
=============================================*/

public static function mdlObtenerSiguienteCodigo() {
    
    try {
        
        $stmt = self::conectarCentral()->prepare("
            SELECT LPAD(IFNULL(MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)), 0) + 1, 4, '0') as siguiente_codigo
            FROM catalogo_maestro 
            WHERE codigo REGEXP '^PROD[0-9]+$' 
            AND activo = 1
        ");
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        if($resultado) {
            $numeroSiguiente = $resultado["siguiente_codigo"];
            return "PROD" . $numeroSiguiente;
        } else {
            return "PROD0001"; // Primer producto
        }
        
    } catch (Exception $e) {
        
        error_log("Error al obtener siguiente código: " . $e->getMessage());
        return "PROD0001";
        
    } finally {
        
        $stmt = null;
    }
}

/*=============================================
BUSCAR PRODUCTOS PARA HIJOS
=============================================*/

public static function mdlBuscarProductosHijos($termino) {
    
    try {
        
        $termino = "%" . $termino . "%";
        
        $stmt = self::conectarCentral()->prepare("
            SELECT codigo, descripcion, precio_venta 
            FROM catalogo_maestro 
            WHERE (descripcion LIKE :termino OR codigo LIKE :termino) 
            AND activo = 1
            ORDER BY descripcion ASC 
            LIMIT 10
        ");
        
        $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        
        error_log("Error al buscar productos hijos: " . $e->getMessage());
        return array();
        
    } finally {
        
        $stmt = null;
    }
}
    
/*=============================================
SINCRONIZAR A PRODUCTOS LOCALES CON DIVISIONES - VERSIÓN FINAL CORREGIDA
=============================================*/
static public function mdlSincronizarAProductosLocales() {
    
    try {
        
        $dbCentral = self::conectarCentral();
        $dbLocal = Conexion::conectar();
        
        $dbLocal->beginTransaction();
        
        // Obtener todos los productos del catálogo maestro activos
        $stmtCentral = $dbCentral->prepare("
            SELECT cm.*, c.categoria 
            FROM catalogo_maestro cm 
            LEFT JOIN categorias c ON cm.id_categoria = c.id 
            WHERE cm.activo = 1
            ORDER BY cm.codigo ASC
        ");
        $stmtCentral->execute();
        $productosMaestros = $stmtCentral->fetchAll(PDO::FETCH_ASSOC);
        
        $sincronizados = 0;
        $actualizados = 0;
        
        foreach ($productosMaestros as $productoMaestro) {
            
            // Datos base del producto
            $codigo = $productoMaestro['codigo'];
            $descripcion = $productoMaestro['descripcion'];
            $precio_venta = $productoMaestro['precio_venta'];
            $id_categoria = $productoMaestro['id_categoria'];
            $imagen = $productoMaestro['imagen'] ?? '';
            $es_divisible = $productoMaestro['es_divisible'] ?? 0;
            
            // Inicializar datos de división como vacío
            $nombre_mitad = '';
            $precio_mitad = 0;
            $nombre_tercio = '';
            $precio_tercio = 0;
            $nombre_cuarto = '';
            $precio_cuarto = 0;
            
            // Si es divisible, buscar información de productos hijos
            if ($es_divisible == 1) {
                
                // Buscar información para MITAD
                if (!empty($productoMaestro['codigo_hijo_mitad'])) {
                    $infoMitad = self::mdlBuscarInformacionHijo($dbCentral, $productoMaestro['codigo_hijo_mitad']);
                    if ($infoMitad) {
                        $nombre_mitad = $infoMitad['descripcion'];
                        $precio_mitad = $infoMitad['precio_venta'];
                    }
                }
                
                // Buscar información para TERCIO
                if (!empty($productoMaestro['codigo_hijo_tercio'])) {
                    $infoTercio = self::mdlBuscarInformacionHijo($dbCentral, $productoMaestro['codigo_hijo_tercio']);
                    if ($infoTercio) {
                        $nombre_tercio = $infoTercio['descripcion'];
                        $precio_tercio = $infoTercio['precio_venta'];
                    }
                }
                
                // Buscar información para CUARTO
                if (!empty($productoMaestro['codigo_hijo_cuarto'])) {
                    $infoCuarto = self::mdlBuscarInformacionHijo($dbCentral, $productoMaestro['codigo_hijo_cuarto']);
                    if ($infoCuarto) {
                        $nombre_cuarto = $infoCuarto['descripcion'];
                        $precio_cuarto = $infoCuarto['precio_venta'];
                    }
                }
            }
            
            // Verificar si el producto ya existe en la base local
            $stmtExiste = $dbLocal->prepare("SELECT id, stock FROM productos WHERE codigo = :codigo");
            $stmtExiste->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmtExiste->execute();
            $productoLocal = $stmtExiste->fetch();
            
            if ($productoLocal) {
                
                // ACTUALIZAR producto existente (preservar stock)
                $stmtUpdate = $dbLocal->prepare("
                    UPDATE productos SET 
                    descripcion = :descripcion,
                    precio_venta = :precio_venta,
                    id_categoria = :id_categoria,
                    imagen = :imagen,
                    es_divisible = :es_divisible,
                    nombre_mitad = :nombre_mitad,
                    precio_mitad = :precio_mitad,
                    nombre_tercio = :nombre_tercio,
                    precio_tercio = :precio_tercio,
                    nombre_cuarto = :nombre_cuarto,
                    precio_cuarto = :precio_cuarto
                    WHERE codigo = :codigo
                ");
                
                $stmtUpdate->bindParam(":codigo", $codigo, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":precio_venta", $precio_venta, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
                $stmtUpdate->bindParam(":imagen", $imagen, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":es_divisible", $es_divisible, PDO::PARAM_INT);
                $stmtUpdate->bindParam(":nombre_mitad", $nombre_mitad, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":precio_mitad", $precio_mitad, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":nombre_tercio", $nombre_tercio, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":precio_tercio", $precio_tercio, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":nombre_cuarto", $nombre_cuarto, PDO::PARAM_STR);
                $stmtUpdate->bindParam(":precio_cuarto", $precio_cuarto, PDO::PARAM_STR);
                
                if($stmtUpdate->execute()) {
                    $actualizados++;
                }
                
            } else {
                
                // CREAR nuevo producto local
                $stmtInsert = $dbLocal->prepare("
                    INSERT INTO productos (
                        codigo, descripcion, id_categoria, stock, precio_venta, 
                        ventas, imagen, es_divisible, nombre_mitad, precio_mitad, 
                        nombre_tercio, precio_tercio, nombre_cuarto, precio_cuarto
                    ) VALUES (
                        :codigo, :descripcion, :id_categoria, 0, :precio_venta, 
                        0, :imagen, :es_divisible, :nombre_mitad, :precio_mitad, 
                        :nombre_tercio, :precio_tercio, :nombre_cuarto, :precio_cuarto
                    )
                ");
                
                $stmtInsert->bindParam(":codigo", $codigo, PDO::PARAM_STR);
                $stmtInsert->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
                $stmtInsert->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
                $stmtInsert->bindParam(":precio_venta", $precio_venta, PDO::PARAM_STR);
                $stmtInsert->bindParam(":imagen", $imagen, PDO::PARAM_STR);
                $stmtInsert->bindParam(":es_divisible", $es_divisible, PDO::PARAM_INT);
                $stmtInsert->bindParam(":nombre_mitad", $nombre_mitad, PDO::PARAM_STR);
                $stmtInsert->bindParam(":precio_mitad", $precio_mitad, PDO::PARAM_STR);
                $stmtInsert->bindParam(":nombre_tercio", $nombre_tercio, PDO::PARAM_STR);
                $stmtInsert->bindParam(":precio_tercio", $precio_tercio, PDO::PARAM_STR);
                $stmtInsert->bindParam(":nombre_cuarto", $nombre_cuarto, PDO::PARAM_STR);
                $stmtInsert->bindParam(":precio_cuarto", $precio_cuarto, PDO::PARAM_STR);
                
                if($stmtInsert->execute()) {
                    $sincronizados++;
                }
            }
        }
        
        $dbLocal->commit();
        
        return [
            'success' => true,
            'message' => "Sincronización completada. Productos nuevos: $sincronizados, Actualizados: $actualizados",
            'sincronizados' => $sincronizados,
            'actualizados' => $actualizados
        ];
        
    } catch (Exception $e) {
        
        if (isset($dbLocal)) {
            $dbLocal->rollBack();
        }
        
        error_log("Error en sincronización: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Error en la sincronización: ' . $e->getMessage(),
            'sincronizados' => 0,
            'actualizados' => 0,
            'error_detalle' => $e->getMessage()
        ];
    }
}

/*=============================================
BUSCAR INFORMACIÓN DE PRODUCTO HIJO - MÉTODO COMPLETO
=============================================*/
private static function mdlBuscarInformacionHijo($dbCentral, $codigoHijo) {
    
    if (empty($codigoHijo)) {
        return null;
    }
    
    try {
        
        $stmt = $dbCentral->prepare("
            SELECT descripcion, precio_venta 
            FROM catalogo_maestro 
            WHERE codigo = :codigo AND activo = 1
        ");
        
        $stmt->bindParam(":codigo", $codigoHijo, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado : null;
        
    } catch (Exception $e) {
        
        error_log("Error al buscar información del hijo '$codigoHijo': " . $e->getMessage());
        return null;
        
    } finally {
        
        $stmt = null;
    }
}
    
    /*=============================================
    OBTENER CATEGORÍAS CENTRALES
    =============================================*/
    static public function mdlMostrarCategoriasCentrales() {
        $db = self::conectarCentral();
        $stmt = $db->prepare("SELECT * FROM categorias ORDER BY categoria ASC");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt = null;
    }
    
/*=============================================
EDITAR PRODUCTO MAESTRO - VERSIÓN SIMPLIFICADA
=============================================*/

public static function mdlEditarProductoMaestro($datos) {
    
    try {
        
        $stmt = self::conectarCentral()->prepare("
            UPDATE catalogo_maestro 
            SET descripcion = :descripcion,
                id_categoria = :id_categoria,
                precio_venta = :precio_venta,
                imagen = :imagen,
                es_divisible = :es_divisible,
                codigo_hijo_mitad = :codigo_hijo_mitad,
                codigo_hijo_tercio = :codigo_hijo_tercio,
                codigo_hijo_cuarto = :codigo_hijo_cuarto,
                fecha_actualizacion = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
        $stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
        $stmt->bindParam(":es_divisible", $datos["es_divisible"], PDO::PARAM_INT);
        
        // ✅ BIND SIMPLE - PERMITE CADENAS VACÍAS
        $stmt->bindParam(":codigo_hijo_mitad", $datos["codigo_hijo_mitad"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_hijo_tercio", $datos["codigo_hijo_tercio"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_hijo_cuarto", $datos["codigo_hijo_cuarto"], PDO::PARAM_STR);
        
        // Debug para verificar
        error_log("=== MODELO DEBUG ===");
        error_log("ID: " . $datos["id"]);
        error_log("Mitad: '" . $datos["codigo_hijo_mitad"] . "'");
        error_log("Tercio: '" . $datos["codigo_hijo_tercio"] . "'");
        error_log("Cuarto: '" . $datos["codigo_hijo_cuarto"] . "'");
        
        if($stmt->execute()) {
            return "ok";
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Error SQL: " . implode(" - ", $errorInfo));
            return "error";
        }
        
    } catch (Exception $e) {
        
        error_log("Error al editar producto maestro: " . $e->getMessage());
        return "error: " . $e->getMessage();
        
    } finally {
        
        $stmt = null;
    }
}
    
    /*=============================================
    ACTUALIZAR RELACIONES PADRE-HIJO
    =============================================*/
    static private function mdlActualizarRelacionesHijos($datos) {
        // Limpiar relaciones anteriores
        self::mdlLimpiarRelacionesHijo($datos["id"]);
        
        // Establecer nuevas relaciones
        self::mdlMarcarComoHijo($datos["codigo_hijo_mitad"], $datos["codigo"], 'mitad');
        self::mdlMarcarComoHijo($datos["codigo_hijo_tercio"], $datos["codigo"], 'tercio');
        self::mdlMarcarComoHijo($datos["codigo_hijo_cuarto"], $datos["codigo"], 'cuarto');
    }
    
    /*=============================================
    LIMPIAR RELACIONES HIJO
    =============================================*/
    static private function mdlLimpiarRelacionesHijo($idPadre) {
        $db = self::conectarCentral();
        
        // Obtener código del padre
        $stmtPadre = $db->prepare("SELECT codigo FROM catalogo_maestro WHERE id = :id");
        $stmtPadre->bindParam(":id", $idPadre, PDO::PARAM_INT);
        $stmtPadre->execute();
        $padre = $stmtPadre->fetch();
        
        if ($padre) {
            // Limpiar hijos que tengan este padre
            $stmt = $db->prepare("
                UPDATE catalogo_maestro 
                SET es_hijo = 0, codigo_padre = NULL, tipo_division = NULL 
                WHERE codigo_padre = :codigo_padre
            ");
            $stmt->bindParam(":codigo_padre", $padre['codigo'], PDO::PARAM_STR);
            $stmt->execute();
        }
        $stmt = null;
    }
    
/*=============================================
ELIMINAR PRODUCTO MAESTRO
=============================================*/

public static function mdlEliminarProductoMaestro($datos) {
    
    $stmt = self::conectarCentral()->prepare("
        UPDATE catalogo_maestro 
        SET activo = 0,
            fecha_actualizacion = NOW()
        WHERE id = :id
    ");
    
    $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        return "ok";
    } else {
        return "error";
    }
    
    $stmt->close();
    $stmt = null;
}
    
    /*=============================================
    IMPORTAR DESDE EXCEL
    =============================================*/
    static public function mdlImportarDesdeExcel($datosExcel) {
        $db = self::conectarCentral();
        $db->beginTransaction();
        
        try {
            $insertados = 0;
            $errores = [];
            
            foreach ($datosExcel as $fila) {
                // Generar código automáticamente si no viene
                $codigo = !empty($fila['codigo']) ? $fila['codigo'] : self::mdlObtenerSiguienteCodigo();
                
                // Validar que la categoría existe
                $stmtCategoria = $db->prepare("SELECT id FROM categorias WHERE id = :id_categoria");
                $stmtCategoria->bindParam(":id_categoria", $fila['id_categoria'], PDO::PARAM_INT);
                $stmtCategoria->execute();
                
                if (!$stmtCategoria->fetch()) {
                    $errores[] = "Categoría no existe para: " . $fila['descripcion'];
                    continue;
                }
                
                $stmt = $db->prepare("
                    INSERT INTO catalogo_maestro (codigo, descripcion, id_categoria, precio_venta) 
                    VALUES (:codigo, :descripcion, :id_categoria, :precio_venta)
                ");
                
                $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion", $fila['descripcion'], PDO::PARAM_STR);
                $stmt->bindParam(":id_categoria", $fila['id_categoria'], PDO::PARAM_INT);
                $stmt->bindParam(":precio_venta", $fila['precio_venta'], PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $insertados++;
                } else {
                    $errores[] = "Error insertando: " . $fila['descripcion'];
                }
            }
            
            $db->commit();
            return ["insertados" => $insertados, "errores" => $errores];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ["insertados" => 0, "errores" => [$e->getMessage()]];
        }
    }
    
    /*=============================================
    GENERAR PLANTILLA EXCEL PARA DESCARGA
    =============================================*/
    static public function mdlGenerarPlantillaExcel() {
        $db = self::conectarCentral();
        
        // Obtener categorías para referencia
        $stmt = $db->prepare("SELECT id, categoria FROM categorias ORDER BY categoria ASC");
        $stmt->execute();
        $categorias = $stmt->fetchAll();
        
        // Generar datos de ejemplo
        $plantilla = [
            [
                'codigo' => '001',
                'descripcion' => 'Ejemplo Producto 1',
                'id_categoria' => $categorias[0]['id'] ?? 1,
                'categoria_nombre' => $categorias[0]['categoria'] ?? 'Ejemplo',
                'precio_venta' => '10000'
            ],
            [
                'codigo' => '002',
                'descripcion' => 'Ejemplo Producto 2',
                'id_categoria' => $categorias[0]['id'] ?? 1,
                'categoria_nombre' => $categorias[0]['categoria'] ?? 'Ejemplo',
                'precio_venta' => '15000'
            ]
        ];
        
        return [
            'plantilla' => $plantilla,
            'categorias' => $categorias
        ];
        $stmt = null;
    }
    
    /*=============================================
    OBTENER INFO PARA DIVISIÓN EN PRODUCTOS.PHP
    =============================================*/
    static public function mdlObtenerInfoDivision($codigoMaestro) {
        $db = self::conectarCentral();
        
        $stmt = $db->prepare("
            SELECT 
                cm.*,
                mitad.codigo as codigo_mitad,
                mitad.descripcion as descripcion_mitad,
                mitad.precio_venta as precio_mitad,
                tercio.codigo as codigo_tercio,
                tercio.descripcion as descripcion_tercio,
                tercio.precio_venta as precio_tercio,
                cuarto.codigo as codigo_cuarto,
                cuarto.descripcion as descripcion_cuarto,
                cuarto.precio_venta as precio_cuarto
            FROM catalogo_maestro cm
            LEFT JOIN catalogo_maestro mitad ON cm.codigo_hijo_mitad = mitad.codigo
            LEFT JOIN catalogo_maestro tercio ON cm.codigo_hijo_tercio = tercio.codigo
            LEFT JOIN catalogo_maestro cuarto ON cm.codigo_hijo_cuarto = cuarto.codigo
            WHERE cm.codigo = :codigo AND cm.activo = 1
        ");
        
        $stmt->bindParam(":codigo", $codigoMaestro, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
        $stmt = null;
    }
    /*=============================================
ACTUALIZAR PRODUCTO MAESTRO DESDE IMPORTACIÓN
=============================================*/

public static function mdlActualizarProductoMaestro($datos) {
    
    $stmt = self::conectarCentral()->prepare("
        UPDATE catalogo_maestro 
        SET descripcion = :descripcion,
            id_categoria = :id_categoria,
            precio_venta = :precio_venta,
            es_divisible = :es_divisible,
            codigo_hijo_mitad = :codigo_hijo_mitad,
            codigo_hijo_tercio = :codigo_hijo_tercio,
            codigo_hijo_cuarto = :codigo_hijo_cuarto,
            fecha_actualizacion = NOW()
        WHERE id = :id AND activo = 1
    ");
    
    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
    $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
    $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
    $stmt->bindParam(":es_divisible", $datos["es_divisible"], PDO::PARAM_INT);
    $stmt->bindParam(":codigo_hijo_mitad", $datos["codigo_hijo_mitad"], PDO::PARAM_STR);
    $stmt->bindParam(":codigo_hijo_tercio", $datos["codigo_hijo_tercio"], PDO::PARAM_STR);
    $stmt->bindParam(":codigo_hijo_cuarto", $datos["codigo_hijo_cuarto"], PDO::PARAM_STR);
    
    if($stmt->execute()) {
        return "ok";
    } else {
        return "error";
    }
    
    $stmt->close();
    $stmt = null;
}

/*=============================================
VERIFICAR SI EXISTE PRODUCTO POR ID
=============================================*/

public static function mdlVerificarProductoMaestroPorId($id) {
    
    $stmt = self::conectarCentral()->prepare("
        SELECT id, codigo, descripcion 
        FROM catalogo_maestro 
        WHERE id = :id AND activo = 1
    ");
    
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
    
    $stmt->close();
    $stmt = null;
}

/*=============================================
OBTENER ESTADÍSTICAS DEL CATÁLOGO MAESTRO
=============================================*/

public static function mdlEstadisticasCatalogoMaestro() {
    
    $stmt = self::conectarCentral()->prepare("
        SELECT 
            COUNT(*) as total_productos,
            COUNT(CASE WHEN es_divisible = 1 THEN 1 END) as productos_divisibles,
            COUNT(CASE WHEN codigo_hijo_mitad != '' THEN 1 END) as con_hijo_mitad,
            COUNT(CASE WHEN codigo_hijo_tercio != '' THEN 1 END) as con_hijo_tercio,
            COUNT(CASE WHEN codigo_hijo_cuarto != '' THEN 1 END) as con_hijo_cuarto,
            AVG(precio_venta) as precio_promedio,
            MAX(fecha_actualizacion) as ultima_actualizacion
        FROM catalogo_maestro 
        WHERE activo = 1
    ");
    
    $stmt->execute();
    
    return $stmt->fetch();
    
    $stmt->close();
    $stmt = null;
}

/*=============================================
BUSCAR PRODUCTOS POR CÓDIGO O DESCRIPCIÓN (PARA IMPORTACIÓN)
=============================================*/

public static function mdlBuscarProductoParaImportacion($termino) {
    
    $stmt = self::conectarCentral()->prepare("
        SELECT id, codigo, descripcion, precio_venta
        FROM catalogo_maestro 
        WHERE activo = 1 
        AND (codigo LIKE :termino OR descripcion LIKE :termino)
        ORDER BY codigo ASC
        LIMIT 10
    ");
    
    $termino = "%" . $termino . "%";
    $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll();
    
    $stmt->close();
    $stmt = null;
}

/*=============================================
ACTUALIZACIÓN MASIVA DE PRECIOS
=============================================*/

public static function mdlActualizarPreciosMasivo($porcentaje, $categoria = null) {
    
    $sql = "UPDATE catalogo_maestro 
            SET precio_venta = precio_venta * (1 + :porcentaje/100),
                fecha_actualizacion = NOW()
            WHERE activo = 1";
    
    if($categoria) {
        $sql .= " AND id_categoria = :categoria";
    }
    
    $stmt = self::conectarCentral()->prepare($sql);
    $stmt->bindParam(":porcentaje", $porcentaje, PDO::PARAM_STR);
    
    if($categoria) {
        $stmt->bindParam(":categoria", $categoria, PDO::PARAM_INT);
    }
    
    if($stmt->execute()) {
        return $stmt->rowCount(); // Retorna número de filas afectadas
    } else {
        return false;
    }
    
    $stmt->close();
    $stmt = null;
}

/*=============================================
SINCRONIZAR PRODUCTO ESPECÍFICO A TABLA LOCAL
=============================================*/

public static function mdlSincronizarProductoEspecifico($codigoMaestro) {
    
    // Primero obtener datos del catálogo maestro
    $stmtMaestro = self::conectarCentral()->prepare("
        SELECT * FROM catalogo_maestro 
        WHERE codigo = :codigo AND activo = 1
    ");
    $stmtMaestro->bindParam(":codigo", $codigoMaestro, PDO::PARAM_STR);
    $stmtMaestro->execute();
    $productoMaestro = $stmtMaestro->fetch();
    
    if(!$productoMaestro) {
        return false;
    }
    
    // Conectar a base local
    $conexionLocal = Conexion::conectar();
    
    // Verificar si existe producto local
    $stmtLocal = $conexionLocal->prepare("
        SELECT id, stock FROM productos 
        WHERE codigo_maestro = :codigo_maestro
    ");
    $stmtLocal->bindParam(":codigo_maestro", $codigoMaestro, PDO::PARAM_STR);
    $stmtLocal->execute();
    $productoLocal = $stmtLocal->fetch();
    
    if($productoLocal) {
        // Actualizar producto existente (manteniendo stock)
        $stmtUpdate = $conexionLocal->prepare("
            UPDATE productos 
            SET descripcion = :descripcion,
                id_categoria = :id_categoria,
                precio_venta = :precio_venta,
                imagen = :imagen,
                es_divisible = :es_divisible
            WHERE codigo_maestro = :codigo_maestro
        ");
        
        $stmtUpdate->bindParam(":descripcion", $productoMaestro["descripcion"], PDO::PARAM_STR);
        $stmtUpdate->bindParam(":id_categoria", $productoMaestro["id_categoria"], PDO::PARAM_INT);
        $stmtUpdate->bindParam(":precio_venta", $productoMaestro["precio_venta"], PDO::PARAM_STR);
        $stmtUpdate->bindParam(":imagen", $productoMaestro["imagen"], PDO::PARAM_STR);
        $stmtUpdate->bindParam(":es_divisible", $productoMaestro["es_divisible"], PDO::PARAM_INT);
        $stmtUpdate->bindParam(":codigo_maestro", $codigoMaestro, PDO::PARAM_STR);
        
        return $stmtUpdate->execute();
        
    } else {
        // Crear nuevo producto local
        $stmtInsert = $conexionLocal->prepare("
            INSERT INTO productos (codigo, codigo_maestro, descripcion, id_categoria, precio_venta, imagen, stock, es_divisible)
            VALUES (:codigo, :codigo_maestro, :descripcion, :id_categoria, :precio_venta, :imagen, 0, :es_divisible)
        ");
        
        $stmtInsert->bindParam(":codigo", $productoMaestro["codigo"], PDO::PARAM_STR);
        $stmtInsert->bindParam(":codigo_maestro", $codigoMaestro, PDO::PARAM_STR);
        $stmtInsert->bindParam(":descripcion", $productoMaestro["descripcion"], PDO::PARAM_STR);
        $stmtInsert->bindParam(":id_categoria", $productoMaestro["id_categoria"], PDO::PARAM_INT);
        $stmtInsert->bindParam(":precio_venta", $productoMaestro["precio_venta"], PDO::PARAM_STR);
        $stmtInsert->bindParam(":imagen", $productoMaestro["imagen"], PDO::PARAM_STR);
        $stmtInsert->bindParam(":es_divisible", $productoMaestro["es_divisible"], PDO::PARAM_INT);
        
        return $stmtInsert->execute();
    }
    
    $stmtMaestro->close();
    $stmtLocal->close();
    $stmtMaestro = null;
    $stmtLocal = null;
}

/*=============================================
LOGS DE CAMBIOS EN CATÁLOGO MAESTRO
=============================================*/

public static function mdlRegistrarCambio($accion, $codigo_producto, $usuario, $detalles = null) {
    
    $stmt = self::conectarCentral()->prepare("
        INSERT INTO logs_catalogo_maestro (accion, codigo_producto, usuario, detalles, fecha)
        VALUES (:accion, :codigo_producto, :usuario, :detalles, NOW())
    ");
    
    $stmt->bindParam(":accion", $accion, PDO::PARAM_STR);
    $stmt->bindParam(":codigo_producto", $codigo_producto, PDO::PARAM_STR);
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->bindParam(":detalles", $detalles, PDO::PARAM_STR);
    
    return $stmt->execute();
    
    $stmt->close();
    $stmt = null;
}
/*=============================================
OBTENER DATOS PROCESADOS PARA SINCRONIZACIÓN MULTI-SUCURSAL (IGUAL A LOCAL)
=============================================*/
static public function mdlObtenerDatosParaSincronizacion() {
    
    try {
        
        // ✅ USAR LA CONEXIÓN DEL API DIRECTAMENTE
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $dbCentral = ConexionCentral::conectar();
        
        // ✅ DEBUG: Log de conexión
        error_log("DEBUG: Conectando a BD central usando API (epicosie_central)");
        
        // ✅ USAR LA MISMA QUERY EXACTA QUE TU SINCRONIZACIÓN LOCAL
        $stmtCentral = $dbCentral->prepare("
            SELECT cm.*, c.categoria 
            FROM catalogo_maestro cm 
            LEFT JOIN categorias c ON cm.id_categoria = c.id 
            WHERE cm.activo = 1
            ORDER BY cm.codigo ASC
        ");
        $stmtCentral->execute();
        $productosMaestros = $stmtCentral->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ DEBUG: Verificar cuántos productos obtuvimos
        error_log("DEBUG: Productos obtenidos del catálogo maestro: " . count($productosMaestros));
        
        if (empty($productosMaestros)) {
            error_log("DEBUG: No se encontraron productos en catálogo maestro");
            return false;
        }
        
        $datosParaEnvio = [];
        
        // ✅ PROCESAR CADA PRODUCTO CON LA LÓGICA EXACTA DE TU SINCRONIZACIÓN LOCAL
        foreach ($productosMaestros as $index => $productoMaestro) {
            
            // ✅ DEBUG: Log cada producto procesado
            if ($index < 5) { // Solo los primeros 5 para no llenar el log
                error_log("DEBUG: Procesando producto " . ($index + 1) . ": " . $productoMaestro['codigo']);
            }
            
            // ✅ DATOS BASE DEL PRODUCTO (IGUAL QUE LOCAL)
            $codigo = $productoMaestro['codigo'];
            $descripcion = $productoMaestro['descripcion'];
            $precio_venta = $productoMaestro['precio_venta'];
            $id_categoria = $productoMaestro['id_categoria'];
            $imagen = $productoMaestro['imagen'] ?? '';
            $es_divisible = $productoMaestro['es_divisible'] ?? 0;
            
            // ✅ INICIALIZAR DATOS DE DIVISIÓN COMO VACÍO (IGUAL QUE LOCAL)
            $nombre_mitad = '';
            $precio_mitad = 0;
            $nombre_tercio = '';
            $precio_tercio = 0;
            $nombre_cuarto = '';
            $precio_cuarto = 0;
            
            // ✅ SI ES DIVISIBLE, BUSCAR INFORMACIÓN DE PRODUCTOS HIJOS (LÓGICA EXACTA)
            if ($es_divisible == 1) {
                
                // Buscar información para MITAD
                if (!empty($productoMaestro['codigo_hijo_mitad'])) {
                    $infoMitad = self::mdlBuscarInformacionHijoAPI($dbCentral, $productoMaestro['codigo_hijo_mitad']);
                    if ($infoMitad) {
                        $nombre_mitad = $infoMitad['descripcion'];
                        $precio_mitad = $infoMitad['precio_venta'];
                    }
                }
                
                // Buscar información para TERCIO
                if (!empty($productoMaestro['codigo_hijo_tercio'])) {
                    $infoTercio = self::mdlBuscarInformacionHijoAPI($dbCentral, $productoMaestro['codigo_hijo_tercio']);
                    if ($infoTercio) {
                        $nombre_tercio = $infoTercio['descripcion'];
                        $precio_tercio = $infoTercio['precio_venta'];
                    }
                }
                
                // Buscar información para CUARTO
                if (!empty($productoMaestro['codigo_hijo_cuarto'])) {
                    $infoCuarto = self::mdlBuscarInformacionHijoAPI($dbCentral, $productoMaestro['codigo_hijo_cuarto']);
                    if ($infoCuarto) {
                        $nombre_cuarto = $infoCuarto['descripcion'];
                        $precio_cuarto = $infoCuarto['precio_venta'];
                    }
                }
            }
            
            // ✅ PREPARAR DATOS PARA ENVÍO CON LA ESTRUCTURA EXACTA DE TU TABLA LOCAL
            $datosParaEnvio[] = [
                'id_categoria' => $id_categoria,
                'codigo' => $codigo,
                'codigo_maestro' => $codigo, // Mapeo para codigo_maestro
                'descripcion' => $descripcion,
                'imagen' => $imagen,
                'stock' => 0, // Valor por defecto para stock
                'precio_venta' => $precio_venta,
                'ventas' => 0, // Valor por defecto para ventas
                'es_divisible' => $es_divisible,
                'nombre_mitad' => $nombre_mitad,
                'precio_mitad' => $precio_mitad,
                'nombre_tercio' => $nombre_tercio,
                'precio_tercio' => $precio_tercio,
                'nombre_cuarto' => $nombre_cuarto,
                'precio_cuarto' => $precio_cuarto
            ];
        }
        
        // ✅ DEBUG: Verificar datos finales
        error_log("DEBUG: Total productos procesados para envío: " . count($datosParaEnvio));
        if (count($datosParaEnvio) > 0) {
            error_log("DEBUG: Primer producto preparado: " . json_encode($datosParaEnvio[0]));
        }
        
        return $datosParaEnvio;
        
    } catch (Exception $e) {
        error_log("ERROR en mdlObtenerDatosParaSincronizacion: " . $e->getMessage());
        return false;
    }
}

/*=============================================
BUSCAR INFORMACIÓN DE PRODUCTO HIJO USANDO CONEXIÓN API (IGUAL QUE LOCAL)
=============================================*/
static private function mdlBuscarInformacionHijoAPI($dbCentral, $codigoHijo) {
    
    if (empty($codigoHijo)) {
        return null;
    }
    
    try {
        
        $stmt = $dbCentral->prepare("
            SELECT descripcion, precio_venta 
            FROM catalogo_maestro 
            WHERE codigo = :codigo AND activo = 1
        ");
        
        $stmt->bindParam(":codigo", $codigoHijo, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado : null;
        
    } catch (Exception $e) {
        
        error_log("Error al buscar información del hijo '$codigoHijo' (API): " . $e->getMessage());
        return null;
        
    } finally {
        
        $stmt = null;
    }
}
}
?>