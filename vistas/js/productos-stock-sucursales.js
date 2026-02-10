/*=============================================
  STOCK POR SUCURSALES - Catálogo maestro y stock por sucursales activas (carga automática)
=============================================*/
$(function () {
  'use strict';

  var tablaStockSuc = null;
  var datosActuales = { sucursales: [], productos: [] };

  function enPaginaStockSucursales() {
    return $('#tbodyStockPorSucursales').length > 0;
  }

  $('#btnActualizarStockSucursales').on('click', function () {
    cargarStockPorSucursales();
  });
  $('#filtroCategoriaStockSuc').on('change', function () {
    cargarStockPorSucursales();
  });

  function cargarStockPorSucursales() {
    var $btn = $('#btnActualizarStockSucursales');
    var $estado = $('#estadoCargaStockSuc');
    var idCategoria = $('#filtroCategoriaStockSuc').val() || '';

    if ($btn.length) $btn.prop('disabled', true);
    $estado.text('Cargando...').removeClass('text-danger text-success').addClass('text-muted');

    $.ajax({
      url: 'ajax/stock-disponible-sucursales.ajax.php',
      method: 'POST',
      data: {
        accion: 'obtener_stock_todas_sucursales',
        id_categoria: idCategoria
      },
      dataType: 'json',
      timeout: 120000,
      success: function (resp) {
        if ($btn.length) $btn.prop('disabled', false);
        if (resp.success) {
          datosActuales = { sucursales: resp.sucursales || [], productos: resp.productos || [] };
          renderizarTabla(datosActuales);
          $estado.text('Actualizado').removeClass('text-danger').addClass('text-success');
          $('#resumenProductosStockSuc').text((datosActuales.productos.length) + ' productos');
        } else {
          $estado.text(resp.message || 'Error').removeClass('text-success').addClass('text-danger');
          $('#tbodyStockPorSucursales').html(
            '<tr><td colspan="10" class="text-center text-danger">' +
            (resp.message || 'Error al cargar datos') + '</td></tr>'
          );
        }
      },
      error: function (xhr, status) {
        if ($btn.length) $btn.prop('disabled', false);
        var msg = status === 'timeout' ? 'Tiempo de espera agotado. El catálogo es muy grande; intente filtrar por categoría.' : 'Error de conexión al servidor.';
        if (xhr && xhr.responseText) {
          try {
            var json = JSON.parse(xhr.responseText);
            if (json.message) msg = json.message;
          } catch (e) {}
        }
        $estado.text('Error').removeClass('text-success').addClass('text-danger');
        $('#tbodyStockPorSucursales').html(
          '<tr><td colspan="10" class="text-center text-danger">' + escapeHtml(msg) + '</td></tr>'
        );
      }
    });
  }

  function renderizarTabla(datos) {
    var sucursales = datos.sucursales;
    var productos = datos.productos;

    var $thead = $('#theadStockSucursales');
    $thead.find('th:not(:first):not(:last)').remove();
    var $thTotal = $thead.find('th.bg-primary');
    sucursales.forEach(function (s, i) {
      $thTotal.before('<th class="text-center">' + escapeHtml(s.nombre) + '</th>');
    });

    var $tbody = $('#tbodyStockPorSucursales');
    $tbody.empty();

    if (productos.length === 0) {
      $tbody.append(
        '<tr><td colspan="' + (2 + sucursales.length + 1) + '" class="text-center text-muted">No hay productos para mostrar.</td></tr>'
      );
    } else {
      productos.forEach(function (p) {
        var cells = [
          '<td><code>' + escapeHtml(p.codigo) + '</code></td>',
          '<td>' + escapeHtml(p.descripcion) + '</td>'
        ];
        sucursales.forEach(function (s) {
          var q = (p.stocks && p.stocks[s.id] !== undefined) ? p.stocks[s.id] : 0;
          var cls = q <= 0 ? 'text-muted' : (q <= 10 ? 'text-warning' : 'text-success');
          cells.push('<td class="text-center ' + cls + '">' + q + '</td>');
        });
        var total = p.total || 0;
        var totalCls = total <= 0 ? 'text-muted' : 'text-primary';
        cells.push('<td class="text-center font-weight-bold ' + totalCls + '">' + total + '</td>');
        $tbody.append('<tr>' + cells.join('') + '</tr>');
      });
    }

    if (tablaStockSuc) {
      tablaStockSuc.destroy();
      tablaStockSuc = null;
    }

    var numCols = 2 + sucursales.length + 1;
    tablaStockSuc = $('#tablaStockPorSucursales').DataTable({
      responsive: true,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json',
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_',
        infoEmpty: '0 registros',
        infoFiltered: '(filtrado de _MAX_)',
        paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
      },
      pageLength: 25,
      order: [[0, 'asc']],
      columnDefs: [
        { orderable: true, targets: 0 },
        { orderable: true, targets: 1 },
        { orderable: true, targets: '_all' }
      ]
    });
  }

  function escapeHtml(text) {
    if (text == null) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  if (enPaginaStockSucursales()) {
    cargarStockPorSucursales();
  }
});
