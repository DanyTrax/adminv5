<?php
  // Obtenemos el medio de pago seleccionado actualmente de la URL
  $medioPagoSeleccionado = isset($_GET['medioPago']) ? $_GET['medioPago'] : null;

  // Llamamos al controlador para obtener la lista desde la tabla medios_pago
  $mediosPago = ControladorMediosPago::ctrMostrarMediosPago();
?>

<select class="btn pull-right" name="filter-medioPago" id="filter-medioPago" style="margin-left: 10px;">

  <option value="">Medio de Pago</option>
  <option value="">Todos</option>

  <?php foreach ($mediosPago as $medio) : ?>
    <option value="<?= $medio['nombre'] ?>" <?= $medioPagoSeleccionado === $medio['nombre'] ? 'selected' : '' ?>>
        <?= $medio['nombre'] ?>
    </option>
  <?php endforeach; ?>

</select>