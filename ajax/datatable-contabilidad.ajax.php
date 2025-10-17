<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../modelos/contabilidad.modelo.php";
require_once "../modelos/conexion.php";

class TablaContabilidadServerSide {
    public function mostrarTabla(){
        $inicio = $_POST["start"] ?? 0;
        $longitud = $_POST["length"] ?? 10;
        $busqueda = $_POST["search"]["value"] ?? "";
        $fechaInicial = $_POST["fechaInicial"] ?? "";
        $fechaFinal = $_POST["fechaFinal"] ?? "";
        $medioPago = $_POST["medioPago"] ?? "";
        $formaPago = $_POST["formaPago"] ?? "";

        $registros = ModeloContabilidad::mdlMostrarVentasContabilidad($inicio, $longitud, $busqueda, $fechaInicial, $fechaFinal, $medioPago, $formaPago);
        
        $recordsTotal = ModeloContabilidad::mdlContarVentasContabilidad("", "", "", "", "");
        $recordsFiltered = ModeloContabilidad::mdlContarVentasContabilidad($busqueda, $fechaInicial, $fechaFinal, $medioPago, $formaPago);

        if(empty($registros)){
            echo json_encode(['draw' => intval($_POST['draw']), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        $datosJson = [];
        foreach ($registros as $key => $value) {
            $datosJson[] = [
                ($key + 1 + $inicio),
                $value["codigo"],
                $value["cliente"],
                ($value["empresa_abono"] ?? ''),
                $value["vendedor"],
                ($value["vendedor_abono"] ?? ''),
                $value["metodo_pago"],
                '$ ' . number_format((float)($value["neto"] ?? 0), 2, ',', '.'),
                '$ ' . number_format((float)($value["total"] ?? 0), 2, ',', '.'),
                $value["fecha"],
                '$ ' . number_format((float)($value["abono"] ?? 0), 2, ',', '.'),
                '$ ' . number_format((float)($value["ultimo_abono"] ?? 0), 2, ',', '.'),
                ($value["pago"] ?? ''),
                ($value["medio_pago"] ?? '')
            ];
        }

        echo json_encode(["draw" => intval($_POST["draw"]), "recordsTotal" => intval($recordsTotal), "recordsFiltered" => intval($recordsFiltered), "data" => $datosJson]);
    }
}

$activar = new TablaContabilidadServerSide();
$activar->mostrarTabla();