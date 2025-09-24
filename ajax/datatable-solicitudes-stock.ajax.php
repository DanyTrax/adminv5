/*=============================================
CREAR SOLICITUD DE STOCK - SOLO AL RECIBIR POST
=============================================*/
static public function ctrCrearSolicitud(){

    // ✅ SOLO EJECUTAR SI SE RECIBIÓ UN POST CON productos_solicitados
    if(isset($_POST["productos_solicitados"]) && $_SERVER['REQUEST_METHOD'] === 'POST'){

        // ✅ LOG COMPLETO DE TODOS LOS DATOS RECIBIDOS
        error_log("=== CONTROLADOR DEBUG - CREANDO SOLICITUD ===");
        error_log("POST: " . json_encode($_POST));
        error_log("SESSION ID: " . (isset($_SESSION["id"]) ? $_SESSION["id"] : 'NO_SESSION'));
        
        // ✅ DEBUG ESPECÍFICO PARA REMISIÓN
        error_log("Tipo de solicitud recibido: " . (isset($_POST["tipo_solicitud"]) ? $_POST["tipo_solicitud"] : 'NO_DEFINIDO'));
        
        if(isset($_POST["tipo_solicitud"]) && $_POST["tipo_solicitud"] == "remision") {
            error_log("📋 SOLICITUD POR REMISIÓN DETECTADA");
            error_log("Código remisión: " . (isset($_POST["codigo_remision"]) ? $_POST["codigo_remision"] : 'NO_ENVIADO'));
            error_log("Nombre cliente: " . (isset($_POST["nombre_cliente_remision"]) ? $_POST["nombre_cliente_remision"] : 'NO_ENVIADO'));
            
            // ✅ VALIDAR QUE HAYA CÓDIGO DE REMISIÓN
            if(!isset($_POST["codigo_remision"]) || empty(trim($_POST["codigo_remision"]))) {
                error_log("❌ ERROR: Falta código de remisión para solicitud por remisión");
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Debe seleccionar una remisión para este tipo de solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
        }
        
        // ✅ OBTENER DATOS DE LA SUCURSAL DESDE BD LOCAL
        $datosSucursal = self::obtenerDatosSucursalLocal();
        
        if(!$datosSucursal) {
            error_log("❌ ERROR: No se pudieron obtener datos de sucursal");
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudieron obtener los datos de la sucursal",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        error_log("Datos de sucursal obtenidos: " . json_encode($datosSucursal));
        
        // ✅ VALIDAR QUE HAYA PRODUCTOS
        $productos = json_decode($_POST["productos_solicitados"], true);
        
        if(empty($productos) || !is_array($productos)) {
            error_log("❌ ERROR: productos_solicitados está vacío o inválido");
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "No hay productos para solicitar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        // ✅ VALIDAR TIPO DE SOLICITUD
        if(!isset($_POST["tipo_solicitud"]) || empty($_POST["tipo_solicitud"])) {
            error_log("❌ ERROR: tipo_solicitud no definido");
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Debe seleccionar un tipo de solicitud",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        // ✅ GENERAR NÚMERO DE SOLICITUD
        $numeroSolicitud = ModeloSolicitudesStock::mdlGenerarNumeroSolicitud("solicitudes_stock");
        
        error_log("Número de solicitud generado: " . $numeroSolicitud);
        
        if(empty($numeroSolicitud)) {
            error_log("❌ ERROR: No se pudo generar número de solicitud");
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error al generar número de solicitud",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        // ✅ PREPARAR DATOS CON INFORMACIÓN DE SUCURSAL DESDE BD LOCAL
        $datos = array(
            "numero_solicitud" => $numeroSolicitud,
            "codigo_sucursal_solicitante" => $datosSucursal["codigo_sucursal"],
            "nombre_sucursal_solicitante" => $datosSucursal["nombre"],
            "usuario_solicitante" => $_SESSION["id"],
            "nombre_usuario_solicitante" => $_SESSION["nombre"],
            "productos_solicitados" => $_POST["productos_solicitados"],
            "tipo_solicitud" => $_POST["tipo_solicitud"],
            "codigo_remision" => isset($_POST["codigo_remision"]) && !empty(trim($_POST["codigo_remision"])) ? trim($_POST["codigo_remision"]) : null,
            "nombre_cliente_remision" => isset($_POST["nombre_cliente_remision"]) && !empty(trim($_POST["nombre_cliente_remision"])) ? trim($_POST["nombre_cliente_remision"]) : null,
            "detalle_adicional" => isset($_POST["detalle_adicional"]) && !empty(trim($_POST["detalle_adicional"])) ? trim($_POST["detalle_adicional"]) : null,
            "total_productos" => count($productos),
            "total_cantidad" => array_sum(array_column($productos, 'cantidad'))
        );

        // ✅ DEBUG: Verificar datos preparados
        error_log("Datos preparados para insertar: " . json_encode($datos));

        // ✅ INTENTAR CREAR SOLICITUD
        $respuesta = ModeloSolicitudesStock::mdlCrearSolicitud("solicitudes_stock", $datos);

        // ✅ DEBUG: Verificar respuesta del modelo
        error_log("Respuesta del modelo: " . $respuesta);

        if($respuesta == "ok"){

            error_log("✅ SOLICITUD CREADA EXITOSAMENTE: " . $numeroSolicitud);
            
            echo '<script>
                swal({
                    type: "success",
                    title: "¡Solicitud creada!",
                    text: "La solicitud ' . $numeroSolicitud . ' se ha creado correctamente",
                    showConfirmButton: false,
                    timer: 2000
                }).then(function(result){
                    window.location = "solicitudes-stock";
                });
            </script>';

        } else {

            // ✅ DEBUG: Error en la creación
            error_log("❌ ERROR AL CREAR SOLICITUD: " . $respuesta);

            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error al crear la solicitud. Revise los logs del servidor para más detalles.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';

        }
        
    }
    // ✅ SI NO HAY POST, NO HACER NADA (no mostrar errores)
}