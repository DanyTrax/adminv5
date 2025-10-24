<?php
/**
 * Script para modificar el código de sincronización
 * para usar la tabla 'categorias' original en lugar de 'categorias_central'
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Modificar Sincronización para Tabla Original</h1>";

// 1. Modificar el modelo de categorías central
echo "<h3>📝 1. Modificando modelo de categorías central</h3>";

$modeloPath = __DIR__ . "/modelos/categorias-central.modelo.php";
if (file_exists($modeloPath)) {
    $contenidoModelo = file_get_contents($modeloPath);
    
    // Reemplazar referencias a categorias_central por categorias
    $contenidoModelo = str_replace('categorias_central', 'categorias', $contenidoModelo);
    
    // Agregar comentario explicativo
    $contenidoModelo = "<?php\n// MODIFICADO: Usar tabla 'categorias' original en lugar de 'categorias_central'\n" . substr($contenidoModelo, 5);
    
    file_put_contents($modeloPath, $contenidoModelo);
    echo "<p>✅ Modelo modificado: $modeloPath</p>";
} else {
    echo "<p>⚠️ No se encontró el archivo del modelo</p>";
}

// 2. Crear nuevo modelo que use tabla categorias original
echo "<h3>📝 2. Creando nuevo modelo para tabla categorias original</h3>";

$nuevoModelo = '<?php
/**
 * Modelo para categorías usando tabla original
 * Usa tabla "categorias" en lugar de "categorias_central"
 */

require_once "conexion.php";

class ModeloCategoriasOriginal {

    /*=============================================
    OBTENER TODAS LAS CATEGORÍAS
    =============================================*/
    static public function mdlObtenerCategorias($soloActivas = false) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $sql = "SELECT * FROM categorias";
            $params = [];
            
            if ($soloActivas) {
                // Si la tabla tiene campo activo, usarlo
                $sql .= " WHERE activo = 1";
            }
            
            $sql .= " ORDER BY categoria ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                "success" => true,
                "message" => "Categorías obtenidas correctamente",
                "data" => $categorias
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCategorias: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Error al obtener categorías: " . $e->getMessage(),
                "data" => []
            ];
        }
    }

    /*=============================================
    CREAR CATEGORÍA
    =============================================*/
    static public function mdlCrearCategoria($datos) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("
                INSERT INTO categorias (categoria, fecha) 
                VALUES (?, NOW())
            ");
            
            $resultado = $stmt->execute([
                $datos["categoria"]
            ]);
            
            if ($resultado) {
                return [
                    "success" => true,
                    "message" => "Categoría creada correctamente",
                    "id" => $pdo->lastInsertId()
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Error al crear la categoría"
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearCategoria: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Error de base de datos: " . $e->getMessage()
            ];
        }
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    static public function mdlSincronizarCategoriasSucursales() {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Obtener todas las categorías de la tabla original
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
                    $dsn = "mysql:host={$sucursal["host_bd"]};port={$sucursal["puerto_bd"]};dbname={$sucursal["nombre_bd"]}";
                    $pdoSucursal = new PDO($dsn, $sucursal["usuario_bd"], $sucursal["password_bd"]);
                    $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Limpiar categorías existentes en la sucursal
                    $stmt = $pdoSucursal->prepare("DELETE FROM categorias");
                    $stmt->execute();
                    
                    // Insertar categorías centrales en la sucursal
                    $stmt = $pdoSucursal->prepare("
                        INSERT INTO categorias (id, categoria, fecha) 
                        VALUES (?, ?, NOW())
                    ");
                    
                    foreach ($categorias as $categoria) {
                        $stmt->execute([
                            $categoria["id"],
                            $categoria["categoria"]
                        ]);
                    }
                    
                    $sucursalesSincronizadas++;
                    
                } catch (Exception $e) {
                    $errores[] = "Error en sucursal {$sucursal["nombre"]}: " . $e->getMessage();
                }
            }
            
            return [
                "success" => true,
                "message" => "Sincronización completada. {$sucursalesSincronizadas} sucursales actualizadas.",
                "sucursales_sincronizadas" => $sucursalesSincronizadas,
                "total_sucursales" => count($sucursales),
                "errores" => $errores
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlSincronizarCategoriasSucursales: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Error al sincronizar categorías: " . $e->getMessage()
            ];
        }
    }
}
?>';

file_put_contents(__DIR__ . "/modelos/categorias-original.modelo.php", $nuevoModelo);
echo "<p>✅ Nuevo modelo creado: modelos/categorias-original.modelo.php</p>";

// 3. Crear nuevo controlador
echo "<h3>📝 3. Creando nuevo controlador</h3>";

$nuevoControlador = '<?php
/**
 * Controlador para categorías usando tabla original
 */

require_once __DIR__ . "/../modelos/categorias-original.modelo.php";

class ControladorCategoriasOriginal {

    /*=============================================
    OBTENER CATEGORÍAS
    =============================================*/
    static public function ctrObtenerCategorias($soloActivas = false) {
        $respuesta = ModeloCategoriasOriginal::mdlObtenerCategorias($soloActivas);
        return $respuesta;
    }

    /*=============================================
    CREAR CATEGORÍA
    =============================================*/
    static public function ctrCrearCategoria() {
        if (isset($_POST["categoria"])) {
            if (empty($_POST["categoria"])) {
                return [
                    "success" => false,
                    "message" => "El nombre de la categoría es requerido"
                ];
            }
            
            $datos = [
                "categoria" => $_POST["categoria"]
            ];
            
            $respuesta = ModeloCategoriasOriginal::mdlCrearCategoria($datos);
            return $respuesta;
        }
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    static public function ctrSincronizarCategoriasSucursales() {
        $respuesta = ModeloCategoriasOriginal::mdlSincronizarCategoriasSucursales();
        return $respuesta;
    }
}
?>';

file_put_contents(__DIR__ . "/controladores/categorias-original.controlador.php", $nuevoControlador);
echo "<p>✅ Nuevo controlador creado: controladores/categorias-original.controlador.php</p>";

// 4. Crear nuevo AJAX
echo "<h3>📝 4. Creando nuevo AJAX</h3>";

$nuevoAjax = '<?php
/**
 * AJAX para categorías usando tabla original
 */

require_once __DIR__ . "/../controladores/categorias-original.controlador.php";

class AjaxCategoriasOriginal {

    /*=============================================
    CREAR CATEGORÍA
    =============================================*/
    public function ajaxCrearCategoria() {
        $respuesta = ControladorCategoriasOriginal::ctrCrearCategoria();
        echo json_encode($respuesta);
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    public function ajaxSincronizarCategoriasSucursales() {
        $respuesta = ControladorCategoriasOriginal::ctrSincronizarCategoriasSucursales();
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER CATEGORÍAS
    =============================================*/
    public function ajaxObtenerCategorias() {
        $soloActivas = isset($_POST["soloActivas"]) ? (bool)$_POST["soloActivas"] : false;
        $respuesta = ControladorCategoriasOriginal::ctrObtenerCategorias($soloActivas);
        echo json_encode($respuesta);
    }
}

/*=============================================
CREAR CATEGORÍA
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "crear") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxCrearCategoria();
}

/*=============================================
SINCRONIZAR CATEGORÍAS CON SUCURSALES
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxSincronizarCategoriasSucursales();
}

/*=============================================
OBTENER CATEGORÍAS
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxObtenerCategorias();
}
?>';

file_put_contents(__DIR__ . "/ajax/categorias-original.ajax.php", $nuevoAjax);
echo "<p>✅ Nuevo AJAX creado: ajax/categorias-original.ajax.php</p>";

// 5. Crear página de prueba
echo "<h3>📝 5. Creando página de prueba</h3>";

$paginaPrueba = '<!DOCTYPE html>
<html>
<head>
    <title>Prueba Sincronización Categorías Original</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .resultado { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>🧪 Prueba Sincronización Categorías Original</h1>
    
    <button onclick="probarObtenerCategorias()">📋 Obtener Categorías</button>
    <button onclick="probarSincronizar()">🔄 Sincronizar</button>
    
    <div id="resultado" class="resultado"></div>

    <script>
    function mostrarResultado(mensaje) {
        $("#resultado").html("<pre>" + mensaje + "</pre>");
    }
    
    function probarObtenerCategorias() {
        $.ajax({
            url: "ajax/categorias-original.ajax.php",
            method: "POST",
            data: { accion: "obtener" },
            dataType: "json",
            success: function(respuesta) {
                mostrarResultado("✅ Categorías obtenidas:\n" + JSON.stringify(respuesta, null, 2));
            },
            error: function(xhr, status, error) {
                mostrarResultado("❌ Error: " + status + " - " + error + "\nResponse: " + xhr.responseText);
            }
        });
    }
    
    function probarSincronizar() {
        if (confirm("¿Estás seguro de que quieres sincronizar las categorías?")) {
            $.ajax({
                url: "ajax/categorias-original.ajax.php",
                method: "POST",
                data: { accion: "sincronizar" },
                dataType: "json",
                success: function(respuesta) {
                    mostrarResultado("✅ Sincronización completada:\n" + JSON.stringify(respuesta, null, 2));
                },
                error: function(xhr, status, error) {
                    mostrarResultado("❌ Error: " + status + " - " + error + "\nResponse: " + xhr.responseText);
                }
            });
        }
    }
    </script>
</body>
</html>';

file_put_contents(__DIR__ . "/prueba-sincronizacion-original.php", $paginaPrueba);
echo "<p>✅ Página de prueba creada: prueba-sincronizacion-original.php</p>";

echo "<h3>✅ Archivos Creados</h3>";
echo "<ul>";
echo "<li>📁 modelos/categorias-original.modelo.php</li>";
echo "<li>📁 controladores/categorias-original.controlador.php</li>";
echo "<li>📁 ajax/categorias-original.ajax.php</li>";
echo "<li>📁 prueba-sincronizacion-original.php</li>";
echo "</ul>";

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Probar la sincronización: <a href='prueba-sincronizacion-original.php' target='_blank'>prueba-sincronizacion-original.php</a></li>";
echo "<li>Si funciona, reemplazar el código de sincronización existente</li>";
echo "<li>Actualizar el JavaScript para usar el nuevo endpoint</li>";
echo "<li>Probar la sincronización desde el panel principal</li>";
echo "</ol>";

echo "<h3>⚠️ Importante</h3>";
echo "<p>Este nuevo código usa la tabla 'categorias' original que ya funciona correctamente.</p>";
echo "<p>La sincronización ahora respetará la estructura real del sistema.</p>";
?>
