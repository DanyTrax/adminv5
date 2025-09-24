<?php

session_start();

require_once "../api-transferencias/conexion-central.php";

class TablaDespachos {

    /*=============================================
    MOSTRAR LA TABLA DE DESPACHOS
    =============================================*/
    public function mostrarTablaDespachos() {

        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    d.*,
                    CASE 
                        WHEN d.estado = 'pendiente' THEN 0
                        WHEN d.estado = 'aceptado' THEN 1
                        WHEN d.estado = 'en_transito' THEN 2
                        WHEN d.estado = 'finalizado' THEN 3
                        ELSE 4
                    END as orden_estado
                FROM despachos d 
                WHERE d.eliminado = 0
                ORDER BY orden_estado ASC, d.fecha_creacion DESC
            ");

            $stmt->execute();
            $despachos = $stmt->fetchAll();

            if(count($despachos) == 0) {
                echo '{"data": []}';
                return;
            }

            $datosJson = '{
                "data": [';

            foreach($despachos as $key => $value) {

                /*=============================================
                BOTONES DE ACCIONES SEGÚN PERFIL
                =============================================*/
                $botones = $this->generarBotonesAccion($value);

                /*=============================================
                ESTADO CON COLOR
                =============================================*/
                $estado = $this->formatearEstado($value["estado"]);

                /*=============================================
                FECHA FORMATEADA
                =============================================*/
                $fechaCreacion = date('d/m/Y H:i', strtotime($value["fecha_creacion"]));

                /*=============================================
                TRANSPORTADOR
                =============================================*/
                $transportador = $value["nombre_transportador_asignado"] ?: 'Sin asignar';

                /*=============================================
                CONSTRUIR FILA JSON
                =============================================*/
                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . $value["numero_despacho"] . '",
                    "' . $value["nombre_sucursal_origen"] . '",
                    "' . $value["nombre_usuario_creador"] . '",
                    "' . $estado . '",
                    "' . $value["total_productos"] . '",
                    "' . number_format($value["total_cantidad"]) . '",
                    "' . $transportador . '",
                    "' . $fechaCreacion . '",
                    "' . $botones . '"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= ']}';

            echo $datosJson;

        } catch(Exception $e) {
            echo '{"data": [], "error": "' . $e->getMessage() . '"}';
        }
    }

    /*=============================================
    GENERAR BOTONES DE ACCIÓN SEGÚN PERFIL
    =============================================*/
    private function generarBotonesAccion($despacho) {

        $botones = '<div class="btn-group">';

        // BOTÓN VER DETALLES - TODOS LOS PERFILES
        $botones .= '<button class="btn btn-info btn-xs btnVerDespacho" 
                            data-toggle="tooltip" 
                            title="Ver detalles" 
                            idDespacho="' . $despacho["id"] . '">
                        <i class="fa fa-eye"></i>
                    </button>';

        // BOTONES SEGÚN ESTADO Y PERFIL
        if($despacho["estado"] == "pendiente") {

            // TRANSPORTADORES Y ADMINISTRADORES PUEDEN ACEPTAR
            if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador") {
                $botones .= '<button class="btn btn-success btn-xs btnAceptarDespacho" 
                                    data-toggle="tooltip" 
                                    title="Aceptar despacho" 
                                    idDespacho="' . $despacho["id"] . '">
                                <i class="fa fa-check"></i>
                            </button>';
            }

            // SOLO ADMINISTRADOR PUEDE EDITAR Y ELIMINAR DESPACHOS PENDIENTES
            if($_SESSION["perfil"] == "Administrador") {
                $botones .= '<button class="btn btn-warning btn-xs btnEditarDespacho" 
                                    data-toggle="tooltip" 
                                    title="Editar despacho" 
                                    idDespacho="' . $despacho["id"] . '">
                                <i class="fa fa-edit"></i>
                            </button>';

                $botones .= '<button class="btn btn-danger btn-xs btnEliminarDespacho" 
                                    data-toggle="tooltip" 
                                    title="Eliminar despacho" 
                                    idDespacho="' . $despacho["id"] . '"
                                    numeroDespacho="' . $despacho["numero_despacho"] . '">
                                <i class="fa fa-trash"></i>
                            </button>';
            }
        }

        // BOTÓN CANCELAR PARA ADMINISTRADOR Y TRANSPORTADOR ASIGNADO
        if($despacho["estado"] != "finalizado" && $despacho["estado"] != "cancelado") {
            
            $puedeCancelar = ($_SESSION["perfil"] == "Administrador") || 
                           ($_SESSION["perfil"] == "Transportador" && $despacho["id_transportador_asignado"] == $_SESSION["id"]);
            
            if($puedeCancelar) {
                $botones .= '<button class="btn btn-danger btn-xs btnCancelarDespacho" 
                                    data-toggle="tooltip" 
                                    title="Cancelar despacho" 
                                    idDespacho="' . $despacho["id"] . '"
                                    estadoDespacho="' . $despacho["estado"] . '">
                                <i class="fa fa-ban"></i>
                            </button>';
            }
        }

        $botones .= '</div>';

        return $botones;
    }

    /*=============================================
    FORMATEAR ESTADO CON COLOR
    =============================================*/
    private function formatearEstado($estado) {

        $colores = [
            'pendiente' => 'label-warning',
            'aceptado' => 'label-success',
            'en_transito' => 'label-info',
            'finalizado' => 'label-default',
            'cancelado' => 'label-danger'
        ];

        $textos = [
            'pendiente' => 'PENDIENTE',
            'aceptado' => 'ACEPTADO',
            'en_transito' => 'EN TRÁNSITO',
            'finalizado' => 'FINALIZADO',
            'cancelado' => 'CANCELADO'
        ];

        $colorClass = $colores[$estado] ?? 'label-default';
        $textoEstado = $textos[$estado] ?? strtoupper($estado);

        return '<span class="label ' . $colorClass . '">' . $textoEstado . '</span>';
    }
}

/*=============================================
ACTIVAR TABLA DE DESPACHOS
=============================================*/
$activarDespachos = new TablaDespachos();
$activarDespachos->mostrarTablaDespachos();
