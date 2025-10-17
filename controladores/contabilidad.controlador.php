<?php

class ControladorContabilidad
{
    /*=============================================
    CREAR GASTO (CORREGIDO)
    =============================================*/
    public static function ctrCrearGasto() {
        // La vista llama a esta función con el nombre "crear", pero el estándar es "ctrCrearGasto"
        // Asegúrate de que el nombre de la función aquí coincida con el que llamas en la vista.
        // Si en la vista dice ControladorContabilidad::crear(), cambia "ctrCrearGasto" por "crear".
        if (isset($_POST["nuevoGasto"])) {
            date_default_timezone_set('America/Bogota');
            $fechaCompleta = $_POST["fecha"] . ' ' . date('H:i:s');
            
            $datos = array(
                "id_vendedor" => $_POST["idVendedor"],
                "fecha" => $fechaCompleta,
                "detalle" => $_POST["detalle"], // Corregido para que coincida con el 'name' del input
                "valor" => str_replace([".", ","], "", $_POST["valor"]),
                "medio_pago" => $_POST["nuevoMedioPago"],
                "tipo" => 'Gasto'
            );

            $respuesta = ModeloContabilidad::save($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El gasto ha sido guardado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) { window.location = "gastos"; }
                    });
                </script>';
            }
        }
    }

    public static function crearEntrada() {
    if (isset($_POST["valor"])) {

        // --- INICIO DE LA PRUEBA FINAL ---

        // Capturamos la hora, el dato original y el dato después de la limpieza
        date_default_timezone_set('America/Bogota');
        $hora = date("Y-m-d H:i:s");
        $datoOriginal = $_POST["valor"];
        $valorLimpio = str_replace(",", "", $_POST["valor"]); // Usamos el método de Ventas

        // Creamos una línea de texto para guardar
        $lineaDeLog = "Hora: " . $hora . " | Valor Original Recibido: " . $datoOriginal . " | Valor Procesado: " . $valorLimpio . "\n";

        // Escribimos esa línea en un nuevo archivo de texto en tu servidor
        file_put_contents("log_de_entradas.txt", $lineaDeLog, FILE_APPEND);

        // Mostramos una alerta para saber que la prueba se ejecutó y no guardamos nada en la base de datos
        echo '<script>
            alert("Prueba realizada. Por favor, revisa el archivo log_de_entradas.txt en tu servidor.");
            window.history.back();
        </script>';

        return; // Detenemos la ejecución para que no intente guardar

        // --- FIN DE LA PRUEBA ---
    }
}

    /*=============================================
    FILTRAR MOVIMIENTOS DE CONTABILIDAD
    =============================================*/
    public static function filterBy($fechaInicial, $fechaFinal, $medioPago, $tipo) {
        return ModeloContabilidad::mdlFilterBy("contabilidad", $fechaInicial, $fechaFinal, $medioPago, $tipo);
    }

    public static function findById($id)
    {
        $respuesta = ModeloContabilidad::findById($id);
        return $respuesta;
    }

    public static function editarGasto()
    {
        if (isset($_POST["editarGasto"])) {
            $datos = array(
                "fecha" => $_POST["fecha"],
                "detalle" => $_POST["detalle"],
                "valor" => $_POST["valor"],
                "medio_pago" => $_POST["nuevoMedioPago"],
            );
            $respuesta = ModeloContabilidad::update($_POST["id"], $datos);
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El gasto ha sido actualizado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "gastos";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function editarEntrada()
    {
        date_default_timezone_set('America/Bogota');
        if (isset($_POST["editarEntrada"])) {
            $datos = array(
                
                "fecha" => $_POST["fecha"],
                "detalle" => $_POST["descripcion"],
                "valor" => $_POST["valor"],
                "medio_pago" => $_POST["nuevoMedioPago"],
            );
            $respuesta = ModeloContabilidad::update($_POST["id"], $datos);
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El entrada ha sido actualizado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "entradas";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function deleteGasto()
    {
        if (isset($_GET["id"])) {
            $respuesta = ModeloContabilidad::delete($_GET["id"]);
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El gasto ha sido eliminado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "gastos";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function deleteEntrada()
    {
        if (isset($_GET["id"])) {
            $respuesta = ModeloContabilidad::delete($_GET["id"]);
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El entrada ha sido eliminado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "entradas";
                            }
                        });
                    </script>';
            }
        }
    }

    public static function reporteGastos()
    {
        $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
        $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
        $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;

        $respuesta = ControladorContabilidad::filterBy($fechaInicial, $fechaFinal, $medioPago, 'Gasto');


        /*=============================================
			CREAMOS EL ARCHIVO DE EXCEL
			=============================================*/

        $Name = 'reporte.xls';

        header('Expires: 0');
        header('Cache-control: private');
        header("Content-type: application/vnd.ms-excel"); // Archivo de Excel
        header("Cache-Control: cache, must-revalidate");
        header('Content-Description: File Transfer');
        header('Last-Modified: ' . date('D, d M Y H:i:s'));
        header("Pragma: public");
        header('Content-Disposition:; filename="' . $Name . '"');
        header("Content-Transfer-Encoding: binary");

        echo utf8_decode("<table border='0'> 

					<tr> 
					<td style='font-weight:bold; border:1px solid #eee;'>CÓDIGO</td> 
					<td style='font-weight:bold; border:1px solid #eee;'>VENDEDOR</td>
					<td style='font-weight:bold; border:1px solid #eee;'>DETALLE</td>
					<td style='font-weight:bold; border:1px solid #eee;'>VALOR</td>
					<td style='font-weight:bold; border:1px solid #eee;'>MEDIO PAGO</td>
					<td style='font-weight:bold; border:1px solid #eee;'>FECHA</td>
					</tr>");

        foreach ($respuesta as $row => $item) {

            $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

            echo utf8_decode("<tr>
			 			<td style='border:1px solid #eee;'>" . $item["id"] . "</td> 
			 			<td style='border:1px solid #eee;'>" . $vendedor["nombre"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["detalle"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["valor"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["medio_pago"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["fecha"] . "</td>
		 			</tr>");
        }


        echo "</table>";
    }

    public static function reporteEntrada()
    {
        $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
        $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
        $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;

        $respuesta = ControladorContabilidad::filterBy($fechaInicial, $fechaFinal, $medioPago, 'Entrada');


        /*=============================================
			CREAMOS EL ARCHIVO DE EXCEL
			=============================================*/

        $Name = 'reporte.xls';

        header('Expires: 0');
        header('Cache-control: private');
        header("Content-type: application/vnd.ms-excel"); // Archivo de Excel
        header("Cache-Control: cache, must-revalidate");
        header('Content-Description: File Transfer');
        header('Last-Modified: ' . date('D, d M Y H:i:s'));
        header("Pragma: public");
        header('Content-Disposition:; filename="' . $Name . '"');
        header("Content-Transfer-Encoding: binary");

        echo utf8_decode("<table border='0'> 

					<tr> 
					<td style='font-weight:bold; border:1px solid #eee;'>#</td> 
					<td style='font-weight:bold; border:1px solid #eee;'>Empresa</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Factura</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Fecha</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Descipci&oacute;n</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Valor</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Medio Pago</td>
					<td style='font-weight:bold; border:1px solid #eee;'>Forma Pago</td>
					</tr>");

        foreach ($respuesta as $key => $item) {

            $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

            echo utf8_decode("<tr>
			 			<td style='border:1px solid #eee;'>" . ($key + 1) . "</td> 
			 			<td style='border:1px solid #eee;'>" . $vendedor["empresa"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["factura"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["fecha"] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item["detalle"] . "</td>
			 			<td style='border:1px solid #eee;'>" . numberFormat($item["valor"]) . "</td>
			 			<td style='border:1px solid #eee;'>" . $item['medio_pago'] . "</td>
			 			<td style='border:1px solid #eee;'>" . $item['forma_pago'] . "</td>
		 			</tr>");
        }


        echo "</table>";
    }
    /*=============================================
    SUMAR ENTRADAS POR MEDIO DE PAGO
    =============================================*/
     static public function ctrSumaEntradasPorMedioPago($fechaInicial, $fechaFinal){
        $tabla = "contabilidad";
        $respuesta = ModeloContabilidad::mdlSumaEntradasPorMedioPago($tabla, $fechaInicial, $fechaFinal);
        return $respuesta;
    }

    public static function sumEntradas()
    {
        $sum = ModeloContabilidad::sumByTipo('Entrada');
        return $sum['total'];
    }

    public static function sumEntradasBy()
    {
        $sum = ModeloContabilidad::sumByTipoAndMedio('Entrada', 'Efectivo');
        return $sum['total'];
    }

    public static function sumGastos()
    {
        $sum = ModeloContabilidad::sumByTipo('Gasto');
        return $sum['total'];
    }

    public static function sumGastosBy()
    {
        $sum = ModeloContabilidad::sumByTipoAndMedio('Gasto', 'Efectivo');
        return $sum['total'];
    }
    /*=============================================
	SUMAR TOTAL DE GASTOS
	=============================================*/
	static public function ctrSumaTotalGastos($fechaInicial, $fechaFinal){
		$tabla = "contabilidad";
		$respuesta = ModeloContabilidad::mdlSumaTotalGastos($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	SUMAR TOTAL POR TIPO Y MEDIO DE PAGO
	=============================================*/
	static public function ctrSumaTotalPorTipoYMedio($tipo, $medioPago, $fechaInicial, $fechaFinal){
		$tabla = "contabilidad";
		$respuesta = ModeloContabilidad::mdlSumaTotalPorTipoYMedio($tabla, $tipo, $medioPago, $fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	SUMAR GASTOS POR MEDIO DE PAGO
	=============================================*/
	static public function ctrSumaGastosPorMedioPago($fechaInicial, $fechaFinal){
		$tabla = "contabilidad";
		$respuesta = ModeloContabilidad::mdlSumaGastosPorMedioPago($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}
    /*=============================================
    SUMAR TOTAL DE ENTRADAS (CORREGIDO)
    =============================================*/
    static public function ctrSumaTotalEntradas($fechaInicial, $fechaFinal){
        $tabla = "contabilidad";
        $respuesta = ModeloContabilidad::mdlSumaTotalEntradas($tabla, $fechaInicial, $fechaFinal);
        
        // Se asegura de devolver 0 si no hay resultados
        if(!$respuesta){
            return ["total" => 0];
        }
        
        return $respuesta;
    }
    /*=============================================
    DESCARGAR REPORTE EN EXCEL reportes(CORREGIDO)
    =============================================*/
    static public function ctrDescargarReporte($fechaInicial, $fechaFinal){
    
        // Se establece el nombre del archivo
        $nombreArchivo = 'reporte-general';
        if ($fechaInicial != null) {
            $nombreArchivo .= '_' . $fechaInicial . '_a_' . $fechaFinal;
        }
    
        header('Expires: 0');
        header('Cache-control: private');
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Cache-Control: cache, must-revalidate"); 
        header('Content-Description: File Transfer');
        header('Last-Modified: ' . date('D, d M Y H:i:s'));
        header("Pragma: public"); 
        header('Content-Disposition:; filename="' . $nombreArchivo . '.xls"');
        header("Content-Transfer-Encoding: binary");
    
        // --- SE OBTIENEN LOS DATOS USANDO LAS CLASES CORRECTAS ---
        $totalEntradas = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
        $totalDeuda = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
        $totalVendido = ControladorVentas::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
        $ventasPorVendedor = ControladorVentas::ctrSumaVentasPorVendedor($fechaInicial, $fechaFinal);
    
        // Se empieza a construir la tabla HTML que se convertirá en Excel
        echo utf8_decode("
        <table border='1'>
            <tr><td colspan='3' style='font-weight:bold; background-color:#3c8dbc; color:white;'>RESUMEN GENERAL</td></tr>
            <tr>
                <td style='font-weight:bold;'>Total Entradas (Dinero Ingresado)</td>
                <td style='font-weight:bold;'>Total por Cobrar (Deuda)</td>
                <td style='font-weight:bold;'>Total Vendido (Generado en Ventas)</td>
            </tr>
            <tr>
                <td>" . number_format($totalEntradas["total"] ?? 0, 2) . "</td>
                <td>" . number_format($totalDeuda["total_deuda"] ?? 0, 2) . "</td>
                <td>" . number_format($totalVendido["total_ventas"] ?? 0, 2) . "</td>
            </tr>
            
            <tr></tr> <tr><td colspan='2' style='font-weight:bold; background-color:#00a65a; color:white;'>TOTAL DE VENTAS POR VENDEDOR</td></tr>
            <tr>
                <td style='font-weight:bold;'>Vendedor</td>
                <td style='font-weight:bold;'>Total Vendido</td>
            </tr>
        ");
    
        foreach ($ventasPorVendedor as $vendedor) {
            echo utf8_decode("
            <tr>
                <td>" . $vendedor['vendedor'] . "</td>
                <td>" . number_format($vendedor['total_vendido'], 2) . "</td>
            </tr>
            ");
        }
    
        echo "</table>";
    }
}
