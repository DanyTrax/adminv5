<?php
/*=============================================
CREAR ARCHIVOS REGISTRO DE DESCARGAS SIMPLE
=============================================*/

echo "🔧 Creando archivos del módulo Registro de Descargas Simple\n";
echo "======================================================\n\n";

// Crear directorios si no existen
$directorios = [
    "modelos",
    "controladores", 
    "ajax",
    "vistas/modulos",
    "vistas/js"
];

foreach ($directorios as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "📁 Directorio creado: $dir\n";
    }
}

// 1. Crear modelo
$modelo = '<?php
/*=============================================
MODELO REGISTRO DE DESCARGAS SIMPLE
=============================================*/

class ModeloRegistroDescargasSimple {

    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    static public function mdlRegistrarDescarga($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO registro_descargas_stock_transito (
                    codigo_producto, descripcion_producto, cantidad_descargada,
                    usuario_id, usuario_nombre, sucursal_id, sucursal_nombre,
                    transportador_id, transportador_nombre, numero_despacho,
                    observaciones, ip_usuario, user_agent
                ) VALUES (
                    :codigo_producto, :descripcion_producto, :cantidad_descargada,
                    :usuario_id, :usuario_nombre, :sucursal_id, :sucursal_nombre,
                    :transportador_id, :transportador_nombre, :numero_despacho,
                    :observaciones, :ip_usuario, :user_agent
                )
            ");

            $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad_descargada", $datos["cantidad_descargada"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_nombre", $datos["usuario_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
            $stmt->bindParam(":sucursal_nombre", $datos["sucursal_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $datos["transportador_id"], PDO::PARAM_INT);
            $stmt->bindParam(":transportador_nombre", $datos["transportador_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":ip_usuario", $datos["ip_usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":user_agent", $datos["user_agent"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

            $stmt->close();
            $stmt = null;

        } catch (Exception $e) {
            error_log("Error en mdlRegistrarDescarga: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
    OBTENER TODAS LAS DESCARGAS
    =============================================*/
    static public function mdlObtenerTodasDescargas($filtros = []) {
        try {
            $where = "1=1";
            $params = [];

            // Filtro por fecha
            if (!empty($filtros["fecha_desde"])) {
                $where .= " AND DATE(fecha_descarga) >= :fecha_desde";
                $params[":fecha_desde"] = $filtros["fecha_desde"];
            }
            if (!empty($filtros["fecha_hasta"])) {
                $where .= " AND DATE(fecha_descarga) <= :fecha_hasta";
                $params[":fecha_hasta"] = $filtros["fecha_hasta"];
            }

            // Filtro por usuario
            if (!empty($filtros["usuario_id"])) {
                $where .= " AND usuario_id = :usuario_id";
                $params[":usuario_id"] = $filtros["usuario_id"];
            }

            // Filtro por sucursal
            if (!empty($filtros["sucursal_id"])) {
                $where .= " AND sucursal_id = :sucursal_id";
                $params[":sucursal_id"] = $filtros["sucursal_id"];
            }

            // Filtro por código de producto
            if (!empty($filtros["codigo_producto"])) {
                $where .= " AND codigo_producto LIKE :codigo_producto";
                $params[":codigo_producto"] = "%" . $filtros["codigo_producto"] . "%";
            }

            // Búsqueda general
            if (!empty($filtros["busqueda_general"])) {
                $busqueda = "%" . $filtros["busqueda_general"] . "%";
                $where .= " AND (
                    codigo_producto LIKE :busqueda1 OR 
                    descripcion_producto LIKE :busqueda2 OR 
                    usuario_nombre LIKE :busqueda3 OR 
                    sucursal_nombre LIKE :busqueda4 OR 
                    transportador_nombre LIKE :busqueda5 OR 
                    numero_despacho LIKE :busqueda6
                )";
                $params[":busqueda1"] = $busqueda;
                $params[":busqueda2"] = $busqueda;
                $params[":busqueda3"] = $busqueda;
                $params[":busqueda4"] = $busqueda;
                $params[":busqueda5"] = $busqueda;
                $params[":busqueda6"] = $busqueda;
            }

            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM registro_descargas_stock_transito 
                WHERE $where 
                ORDER BY fecha_descarga DESC
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error en mdlObtenerTodasDescargas: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE DESCARGAS
    =============================================*/
    static public function mdlObtenerEstadisticasDescargas($filtros = []) {
        try {
            $where = "1=1";
            $params = [];

            // Aplicar mismos filtros que en mdlObtenerTodasDescargas
            if (!empty($filtros["fecha_desde"])) {
                $where .= " AND DATE(fecha_descarga) >= :fecha_desde";
                $params[":fecha_desde"] = $filtros["fecha_desde"];
            }
            if (!empty($filtros["fecha_hasta"])) {
                $where .= " AND DATE(fecha_descarga) <= :fecha_hasta";
                $params[":fecha_hasta"] = $filtros["fecha_hasta"];
            }

            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_descargas,
                    SUM(cantidad_descargada) as total_cantidad,
                    COUNT(DISTINCT codigo_producto) as productos_unicos,
                    COUNT(DISTINCT usuario_id) as usuarios_unicos,
                    COUNT(DISTINCT sucursal_id) as sucursales_unicas
                FROM registro_descargas_stock_transito 
                WHERE $where
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadisticasDescargas: " . $e->getMessage());
            return [
                "total_descargas" => 0,
                "total_cantidad" => 0,
                "productos_unicos" => 0,
                "usuarios_unicos" => 0,
                "sucursales_unicas" => 0
            ];
        }
    }
}
?>';

file_put_contents("modelos/registro-descargas-simple.modelo.php", $modelo);
echo "✅ Modelo creado: modelos/registro-descargas-simple.modelo.php\n";

// 2. Crear controlador
$controlador = '<?php
/*=============================================
CONTROLADOR REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar modelo solo si no está cargado
if (!class_exists("ModeloRegistroDescargasSimple")) {
    require_once "modelos/registro-descargas-simple.modelo.php";
}

class ControladorRegistroDescargasSimple {

    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    public function ctrRegistrarDescarga() {
        if(isset($_POST["registrarDescarga"])) {
            $datos = array(
                "codigo_producto" => $_POST["codigo_producto"],
                "descripcion_producto" => $_POST["descripcion_producto"],
                "cantidad_descargada" => $_POST["cantidad_descargada"],
                "usuario_id" => $_POST["usuario_id"],
                "usuario_nombre" => $_POST["usuario_nombre"],
                "sucursal_id" => $_POST["sucursal_id"],
                "sucursal_nombre" => $_POST["sucursal_nombre"],
                "transportador_id" => $_POST["transportador_id"] ?? null,
                "transportador_nombre" => $_POST["transportador_nombre"] ?? null,
                "numero_despacho" => $_POST["numero_despacho"] ?? null,
                "observaciones" => $_POST["observaciones"] ?? "",
                "ip_usuario" => $_SERVER["REMOTE_ADDR"] ?? "",
                "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? ""
            );

            $respuesta = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos);

            if($respuesta == "ok") {
                echo json_encode(["success" => true, "message" => "Descarga registrada exitosamente"]);
            } else {
                echo json_encode(["success" => false, "error" => "Error al registrar la descarga"]);
            }
        }
    }

    /*=============================================
    OBTENER REGISTRO DE DESCARGAS
    =============================================*/
    public function ctrObtenerRegistro($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerTodasDescargas($filtros);
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    public function ctrObtenerEstadisticas($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerEstadisticasDescargas($filtros);
        return $respuesta;
    }
}
?>';

file_put_contents("controladores/registro-descargas-simple.controlador.php", $controlador);
echo "✅ Controlador creado: controladores/registro-descargas-simple.controlador.php\n";

// 3. Crear AJAX principal
$ajax = '<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

if(isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "registrar_descarga":
            $registroDescargas->ctrRegistrarDescarga();
            break;
            
        case "obtener_registro":
            $filtros = [
                "fecha_desde" => $_POST["fecha_desde"] ?? "",
                "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
                "usuario_id" => $_POST["usuario_id"] ?? "",
                "sucursal_id" => $_POST["sucursal_id"] ?? "",
                "codigo_producto" => $_POST["codigo_producto"] ?? "",
                "busqueda_general" => $_POST["busqueda_general"] ?? ""
            ];
            
            $registros = $registroDescargas->ctrObtenerRegistro($filtros);
            echo json_encode($registros);
            break;
            
        case "obtener_estadisticas":
            $filtros = [
                "fecha_desde" => $_POST["fecha_desde"] ?? "",
                "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
                "usuario_id" => $_POST["usuario_id"] ?? "",
                "sucursal_id" => $_POST["sucursal_id"] ?? ""
            ];
            
            $estadisticas = $registroDescargas->ctrObtenerEstadisticas($filtros);
            echo json_encode($estadisticas);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "error" => "No se especificó acción"]);
}
?>';

file_put_contents("ajax/registro-descargas-simple.ajax.php", $ajax);
echo "✅ AJAX creado: ajax/registro-descargas-simple.ajax.php\n";

// 4. Crear AJAX DataTable
$datatable = '<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

// Obtener parámetros de DataTable
$start = $_POST["start"] ?? 0;
$length = $_POST["length"] ?? 10;
$search = $_POST["search"]["value"] ?? "";

// Filtros adicionales
$filtros = [
    "fecha_desde" => $_POST["fecha_desde"] ?? "",
    "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
    "usuario_id" => $_POST["usuario_id"] ?? "",
    "sucursal_id" => $_POST["sucursal_id"] ?? "",
    "codigo_producto" => $_POST["codigo_producto"] ?? "",
    "busqueda_general" => $search
];

// Obtener registros
$registros = $registroDescargas->ctrObtenerRegistro($filtros);

// Preparar datos para DataTable
$data = [];
foreach($registros as $registro) {
    $data[] = [
        $registro["codigo_producto"],
        $registro["descripcion_producto"],
        $registro["cantidad_descargada"],
        $registro["usuario_nombre"],
        $registro["sucursal_nombre"],
        $registro["transportador_nombre"] ?? "N/A",
        $registro["numero_despacho"] ?? "N/A",
        $registro["observaciones"],
        date("d/m/Y H:i", strtotime($registro["fecha_descarga"]))
    ];
}

// Respuesta para DataTable
echo json_encode([
    "draw" => intval($_POST["draw"]),
    "recordsTotal" => count($registros),
    "recordsFiltered" => count($registros),
    "data" => $data
]);
?>';

file_put_contents("ajax/datatable-registro-descargas-simple.ajax.php", $datatable);
echo "✅ DataTable AJAX creado: ajax/datatable-registro-descargas-simple.ajax.php\n";

echo "\n🎉 ¡Archivos del módulo creados exitosamente!\n";
echo "\n🔧 PRÓXIMOS PASOS:\n";
echo "1. Ejecutar: php crear-tabla-registro-descargas-simple.php\n";
echo "2. Probar el módulo: registro-descargas-simple\n";
echo "3. Verificar que no haya errores HTTP 500\n";
?>
