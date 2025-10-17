<?php

// Validar que solo usuarios permitidos accedan
if($_SESSION["perfil"] == "Transportador") {
    echo '<script>
        window.location = "solicitudes-stock";
    </script>';
    return;
}

?>

<!-- CONTENIDO DE SOLICITAR STOCK -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Nueva Solicitud de Stock
            <small>Crear solicitud de productos entre sucursales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="solicitudes-stock">Solicitudes de Stock</a></li>
            <li class="active">Nueva Solicitud</li>
        </ol>
    </section>

    <section class="content">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Instrucciones:</strong> 
            Seleccione los productos que necesita, especifique las cantidades y complete la información adicional requerida.
        </div>
        
        <!-- Aquí incluiremos el modal como contenido principal -->
        <?php include "solicitudes-stock.php"; ?>
        
        <!-- JavaScript específico para esta vista -->
        <script>
        $(document).ready(function() {
            // Abrir automáticamente el modal de solicitud
            $('#modalSolicitarStock').modal('show');
            
            // Redirigir al cerrar el modal
            $('#modalSolicitarStock').on('hidden.bs.modal', function () {
                window.location = 'solicitudes-stock';
            });
        });
        </script>
        
    </section>
</div>