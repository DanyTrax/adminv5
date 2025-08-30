<?php

$item = null;
$valor = null;
$orden = "id";

// Se obtienen los últimos 10 productos
$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

?>


<div class="box box-primary">

 <div class="box-header with-border">

  <h3 class="box-title">Productos Agregados Recientemente</h3>

  <div class="box-tools pull-right">

   <button type="button" class="btn btn-box-tool" data-widget="collapse">

    <i class="fa fa-minus"></i>

   </button>

   <button type="button" class="btn btn-box-tool" data-widget="remove">

    <i class="fa fa-times"></i>

   </button>

  </div>

 </div>

 <div class="box-body">

  <ul class="products-list product-list-in-box">

  <?php

    // --- INICIO DE LA CORRECCIÓN: Se usa un bucle más seguro ---
    // Se limita a 10 productos o al total de productos si hay menos de 10
    $limite = min(10, count($productos));

  for($i = 0; $i < $limite; $i++){

   echo '<li class="item">

    <div class="product-img">

     <img src="'.$productos[$i]["imagen"].'" alt="Product Image">

    </div>

    <div class="product-info">

     <a href="" class="product-title">

      '.$productos[$i]["descripcion"].'

      <span class="label label-warning pull-right">$'.$productos[$i]["precio_venta"].'</span>

     </a>
 
   </div>

   </li>';

  }

  ?>

  </ul>

 </div>

 <div class="box-footer text-center">

  <a href="productos" class="uppercase">Ver todos los productos</a>

 </div>

</div>
