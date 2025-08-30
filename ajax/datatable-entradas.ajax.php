<?php
// Requerimos los archivos necesarios. Asegúrate que las rutas sean correctas.
require_once "../modelos/contabilidad.modelo.php";
require_once "../modelos/conexion.php";

class TablaEntradasServerSide {

    public function mostrarTabla(){
        // Recibimos los parámetros de DataTables y los filtros personalizados
        $inicio = $_POST["start"];
        $longitud = $_POST["length"];
        $busqueda = $_POST["search"]["value"];
        $fechaInicial = $_POST["fechaInicial"];
        $fechaFinal = $_POST["fechaFinal"];
        $medioPago = $_POST["medioPago"];

        // Llamamos a las nuevas funciones del modelo, pasando los filtros
        $entradas = ModeloContabilidad::mdlMostrarEntradasServerSide("contabilidad", $inicio, $longitud, $busqueda, $fechaInicial, $fechaFinal, $medioPago);
        
        if(count($entradas) == 0){
            echo json_encode(['draw' => intval($_POST['draw']), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        // Para el conteo total, no pasamos filtros de búsqueda para obtener el total general sin filtrar.
        $recordsTotal = ModeloContabilidad::mdlContarEntradasServerSide("contabilidad", "", $fechaInicial, $fechaFinal, $medioPago);
        $recordsFiltered = ModeloContabilidad::mdlContarEntradasServerSide("contabilidad", $busqueda, $fechaInicial, $fechaFinal, $medioPago);

        $datosJson = [];
        foreach ($entradas as $key => $value) {
            $botones = "<div class='btn-group'><button class='btn btn-warning btn-xs btnEditarEntrada' idEntrada='".$value["id"]."'><i class='fa fa-pencil'></i></button><button class='btn btn-danger btn-xs btnEliminarEntrada' idEntrada='".$value["id"]."'><i class='fa fa-times'></i></button></div>";
            
            $datosJson[] = [
                ($key + 1 + $inicio),
                $value["empresa"], // Viene del JOIN, ¡eficiente!
                $value["factura"],
                $value["fecha"],
                $value["detalle"],
                '$ ' . number_format((float)$value["valor"], 2, ',', '.'),
                $value["medio_pago"],
                $value["forma_pago"],
                $botones
            ];
        }

        $json_data = [
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data"            => $datosJson
        ];

        echo json_encode($json_data);
    }
}

$activar = new TablaEntradasServerSide();
$activar->mostrarTabla();