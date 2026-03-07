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

    var urlAjax = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'ajax/stock-disponible-sucursales.ajax.php';
    $.ajax({
      url: urlAjax,
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
            '<tr><td colspan="20" class="text-center text-danger">' +
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
          } catch (e) {
            if (xhr.responseText.indexOf('<') !== -1) {
              msg = 'Error del servidor (posible fallo de conexión a la BD central). Reintente o contacte al administrador.';
            }
          }
        }
        $estado.text('Error').removeClass('text-success').addClass('text-danger');
        $('#tbodyStockPorSucursales').html(
          '<tr><td colspan="20" class="text-center text-danger">' + escapeHtml(msg) +
          ' <button type="button" class="btn btn-sm btn-default" id="btnReintentarStockSuc">Reintentar</button></td></tr>'
        );
        $('#btnReintentarStockSuc').off('click').on('click', function () { cargarStockPorSucursales(); });
      }
    });
  }

  function iconoStock(cantidad) {
    var cls, icon;
    if (cantidad <= 0) {
      cls = 'text-danger';
      icon = '<i class="fa fa-times-circle"></i> ';
    } else if (cantidad <= 10) {
      cls = 'text-warning';
      icon = '<i class="fa fa-exclamation-triangle"></i> ';
    } else {
      cls = 'text-success';
      icon = '<i class="fa fa-check-circle"></i> ';
    }
    return { class: cls, icon: icon };
  }

  function renderizarTabla(datos) {
    var sucursales = datos.sucursales;
    var productos = datos.productos;

    var headerHtml = '<th style="width: 10px;">#</th><th>Código</th><th>Descripción</th><th>Categoría</th>';
    sucursales.forEach(function (s) {
      headerHtml += '<th class="text-center" style="min-width: 80px;">' + escapeHtml(s.nombre) + '</th>';
    });
    headerHtml += '<th class="text-center bg-primary" style="min-width: 80px;">Total</th>';
    $('#theadStockSucursales').html(headerHtml);

    var $tbody = $('#tbodyStockPorSucursales');
    $tbody.empty();

    if (productos.length === 0) {
      var cols = 4 + sucursales.length + 1;
      $tbody.append('<tr><td colspan="' + cols + '" class="text-center text-muted">No hay productos para mostrar.</td></tr>');
    } else {
      productos.forEach(function (p, index) {
        var fila = '<tr>';
        fila += '<td class="text-center"><strong>' + (index + 1) + '</strong></td>';
        fila += '<td><code>' + escapeHtml(p.codigo) + '</code></td>';
        fila += '<td>' + escapeHtml(p.descripcion) + '</td>';
        fila += '<td>' + escapeHtml(p.categoria || '') + '</td>';
        sucursales.forEach(function (s) {
          var q = (p.stocks && p.stocks[s.id] !== undefined) ? parseInt(p.stocks[s.id], 10) : 0;
          var o = iconoStock(q);
          fila += '<td class="text-center ' + o.class + '">' + o.icon + '<strong>' + q + '</strong></td>';
        });
        var total = p.total || 0;
        var oTotal = iconoStock(total);
        fila += '<td class="text-center ' + oTotal.class + '"><strong>' + total + '</strong></td>';
        fila += '</tr>';
        $tbody.append(fila);
      });
    }

    if (tablaStockSuc) {
      tablaStockSuc.destroy();
      tablaStockSuc = null;
    }

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
      order: [[3, 'asc'], [1, 'asc']],
      columnDefs: [
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
    // Pequeño retraso para que el beacon de Cloudflare no bloquee la ejecución
    setTimeout(function () { cargarStockPorSucursales(); }, 100);
  }
});
