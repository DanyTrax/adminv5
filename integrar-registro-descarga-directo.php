<?php
/*=============================================
INTEGRAR REGISTRO DE DESCARGA DIRECTO
=============================================*/

echo "🔧 Integrando registro de descarga directo\n";
echo "========================================\n\n";

// 1. Modificar el archivo de descarga para incluir registro directo
$archivoDescarga = "vistas/js/stock-transito-unificado.js";

if (file_exists($archivoDescarga)) {
    $contenido = file_get_contents($archivoDescarga);
    
    // Buscar la función de descarga exitosa y agregar registro directo
    $buscar = 'if(respuesta.success) {
                // 🔗 HOOK - Registrar descarga en el sistema
                registrarDescargaSimple(codigoProducto, cantidadDescargar, observaciones);
                
                swal({';
    
    $reemplazar = 'if(respuesta.success) {
                // 🔗 REGISTRO DIRECTO - Registrar descarga en la tabla
                registrarDescargaDirecta(codigoProducto, cantidadDescargar, observaciones);
                
                swal({';
    
    $contenidoModificado = str_replace($buscar, $reemplazar, $contenido);
    
    // Agregar función de registro directo al final del archivo
    $funcionRegistro = '

/*=============================================
REGISTRAR DESCARGA DIRECTA
=============================================*/
function registrarDescargaDirecta(codigoProducto, cantidad, observaciones) {
    // Obtener datos del usuario actual desde la sesión
    var usuarioId = 1; // Valor por defecto
    var usuarioNombre = "Usuario";
    var sucursalId = 1;
    var sucursalNombre = "Sucursal";
    
    // Intentar obtener datos del DOM
    if ($("#usuarioId").length > 0) usuarioId = $("#usuarioId").val() || 1;
    if ($("#usuarioNombre").length > 0) usuarioNombre = $("#usuarioNombre").val() || "Usuario";
    if ($("#sucursalId").length > 0) sucursalId = $("#sucursalId").val() || 1;
    if ($("#sucursalNombre").length > 0) sucursalNombre = $("#sucursalNombre").val() || "Sucursal";
    
    // Obtener información del producto desde stockSeleccionado
    var descripcionProducto = "";
    var transportadorId = null;
    var transportadorNombre = null;
    var numeroDespacho = null;
    
    if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
        descripcionProducto = stockSeleccionado.descripcion || "";
        transportadorId = stockSeleccionado.transportador_id || null;
        transportadorNombre = stockSeleccionado.transportador_nombre || null;
        numeroDespacho = stockSeleccionado.numero_despacho_origen || null;
    }
    
    // Enviar registro directo por AJAX
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "registrar_descarga",
            codigo_producto: codigoProducto,
            descripcion_producto: descripcionProducto,
            cantidad_descargada: cantidad,
            usuario_id: usuarioId,
            usuario_nombre: usuarioNombre,
            sucursal_id: sucursalId,
            sucursal_nombre: sucursalNombre,
            transportador_id: transportadorId,
            transportador_nombre: transportadorNombre,
            numero_despacho: numeroDespacho,
            observaciones: observaciones
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                console.log("✅ Descarga registrada en la tabla:", codigoProducto);
            } else {
                console.error("❌ Error al registrar descarga:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX al registrar descarga:", error);
        }
    });
}';
    
    // Agregar la función al final del archivo
    $contenidoFinal = $contenidoModificado . $funcionRegistro;
    
    // Guardar el archivo modificado
    file_put_contents($archivoDescarga, $contenidoFinal);
    echo "✅ Archivo de descarga modificado: $archivoDescarga\n";
} else {
    echo "❌ No se encontró el archivo: $archivoDescarga\n";
}

// 2. Crear endpoint AJAX para registro directo
$ajaxRegistro = '<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE - REGISTRO DIRECTO
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

if(isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "registrar_descarga":
            // Registrar descarga directamente
            $datos = array(
                "codigo_producto" => $_POST["codigo_producto"],
                "descripcion_producto" => $_POST["descripcion_producto"] ?? "",
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

file_put_contents("ajax/registro-descargas-simple.ajax.php", $ajaxRegistro);
echo "✅ AJAX actualizado con registro directo\n";

echo "\n🎉 ¡Registro directo integrado exitosamente!\n";
echo "\n📊 FUNCIONALIDADES:\n";
echo "- ✅ Registro automático en cada descarga\n";
echo "- ✅ Datos completos del producto y usuario\n";
echo "- ✅ Información de transportador y despacho\n";
echo "- ✅ Fecha, hora, IP y User Agent\n";

echo "\n🔧 PRÓXIMOS PASOS:\n";
echo "1. Probar una descarga de producto\n";
echo "2. Verificar que se registre en la tabla\n";
echo "3. Consultar en 'Registro de Descargas'\n";
?>
