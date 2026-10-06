# Graph Report - adminv5-graph-corpus  (2026-10-06)

## Corpus Check
- 287 files · ~229,177 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 5 file(s) not represented in the graph (top: (none) 2, .phpold 2, .phpand 1)

## Summary
- 1483 nodes · 2153 edges · 239 communities (34 shown, 205 thin omitted)
- Extraction: 69% EXTRACTED · 31% INFERRED · 0% AMBIGUOUS · INFERRED: 658 edges (avg confidence: 0.85)
- Token cost: 8,000 input · 1,200 output

## Community Hubs (Navigation)
- BD Local Conexion Contabilidad
- Despachos Multi-Sucursal
- Usuarios Centrales DataTable
- Ventas AJAX Controlador
- Categorias Centrales
- Productos Catalogo Transferencia
- Medios Pago Central
- Stock en Transito AJAX
- Usuarios Locales Reportes
- Usuarios Centrales UI
- Catalogo Maestro Modelo
- Clientes Centrales Controlador
- Solicitudes Stock UI
- Instalador SQL Migraciones
- Crear Despacho UI
- BD Central Solicitudes
- Reportes Dashboard
- Clientes Centrales UI
- Categorias Locales
- Despachos UI Estados
- Personalizacion Cotizaciones
- Medios Pago Central UI
- Sucursales Modelo
- Clientes Locales
- Sucursales Controlador Config
- Sucursales AJAX Sync
- Categorias Centrales UI
- Solicitudes Stock Controlador
- Transportador Movil UI
- Sincronizacion Usuarios
- Cotizaciones Controlador
- Salidas Inventario
- Crear Solicitud Stock UI
- Ventas UI
- Catalogo Maestro Controlador
- Medios Pago Locales
- Personalizacion Colores
- personalizacion-colores-simplificado-con
- personalizacion-colores-simplificado.mod
- Sucursales UI Sync
- Trazabilidad Mercancia UI
- Logger.php
- registro-descargas-simple.controlador.ph
- personalizacion-colores.js
- crear-despacho.php
- Despachos y Stock en Tránsito
- solicitudes-stock.ajax.php
- usuarios-sucursales.modelo.php
- stock-transito-unificado.js
- personalizacion-colores.controlador.php
- personalizacion-colores.modelo.php
- personalizacion-colores-simplificado.con
- registro-descargas.controlador.php
- stock-transito.js
- usuarios-central-old.php
- catalogo-maestro.ajax.php
- datatable-despachos.ajax.php
- datatable-stock-transito.ajax.php
- productos-despacho.ajax.php
- cabezote.php
- personalizacion-colores-simplificado-fun
- stock-disponible-sucursales.ajax.php
- productos-stock-sucursales.js
- timezone-bogota.js
- personalizacion-colores-simplificado.php
- personalizacion-colores-con-subida.php
- buscar-stock-transito.ajax.php
- personalizacion-colores.ajax.php
- personalizacion-cotizaciones.ajax.php
- plantilla.controlador.php
- solicitudes-stock-buscar.ajax.php
- transferencias.ajax.php
- datatable-contabilidad.ajax.php
- datatable-entradas.ajax.php
- obtener_stock_productos.php
- stock-transito-clean.js
- crear-cotizacion.php
- FormaPago.php
- MedioPago.php
- obtener-sucursal-actual-sin-sesion.ajax.

## God Nodes (most connected - your core abstractions)
1. `ConexionCentral` - 192 edges
2. `Conexion` - 158 edges
3. `ModeloCatalogoMaestro` - 49 edges
4. `ModeloSucursales` - 49 edges
5. `ModeloContabilidad` - 45 edges
6. `ModeloUsuariosCentral` - 39 edges
7. `ModeloVentas` - 38 edges
8. `ModeloMediosPagoCentral` - 36 edges
9. `ModeloSolicitudesStock` - 32 edges
10. `ModeloClientesCentral` - 29 edges

## Surprising Connections (you probably didn't know these)
- `obtenerConfiguracionLocal()` --calls--> `Conexion`  [INFERRED]
  obtener-sucursal-actual-sin-sesion.ajax.php → conexion.php
- `actualizarImagenEnBD()` --calls--> `ConexionCentral`  [INFERRED]
  personalizacion-colores.ajax.php → conexion-central.php
- `actualizarImagenEnBD()` --calls--> `ConexionCentral`  [INFERRED]
  personalizacion-cotizaciones.ajax.php → conexion-central.php
- `SQL Migraciones Instalación` --conceptually_related_to--> `Arquitectura Central + Sucursales`  [INFERRED]
  sql/README.md → README.md
- `aplicarCambiosSQLCentral()` --calls--> `ConexionCentral`  [INFERRED]
  instalador-nuevo.php → conexion-central.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Documentación flujo multi-sucursal** — readme_arquitectura_central_sucursales, documentacion_despachos_stock, resumen_flujos_operativos [INFERRED 0.75]

## Communities (239 total, 205 thin omitted)

### Community 0 - "BD Local Conexion Contabilidad"
Cohesion: 0.07
Nodes (3): Conexion, ControladorContabilidad, ModeloContabilidad

### Community 1 - "Despachos Multi-Sucursal"
Cohesion: 0.05
Nodes (3): AjaxCrearDespachosSucursales, ControladorDespachos, ModeloDespachos

### Community 2 - "Usuarios Centrales DataTable"
Cohesion: 0.06
Nodes (3): AjaxTablaUsuariosCentral, ControladorUsuariosCentral, ModeloUsuariosCentral

### Community 3 - "Ventas AJAX Controlador"
Cohesion: 0.06
Nodes (3): AjaxVentas, ControladorVentas, ModeloVentas

### Community 4 - "Categorias Centrales"
Cohesion: 0.07
Nodes (4): AjaxCategoriasCentral, ControladorCategoriasCentral, ModeloCategoriasCentral, AjaxCategoriasOriginal

### Community 5 - "Productos Catalogo Transferencia"
Cohesion: 0.06
Nodes (6): TablaProductosCatalogo, TablaProductosTransferencia, TablaProductosVentas, AjaxProductos, ControladorProductos, ModeloProductos

### Community 7 - "Stock en Transito AJAX"
Cohesion: 0.07
Nodes (3): AjaxStockTransito, ControladorStockTransito, ModeloStockTransito

### Community 8 - "Usuarios Locales Reportes"
Cohesion: 0.07
Nodes (5): AjaxUsuarios, ControladorUsuarios, ModeloUsuarios, ControladorUsuariosSucursales, numberFormat()

### Community 9 - "Usuarios Centrales UI"
Cohesion: 0.14
Nodes (31): abrirModalUsuario(), asignarSucursales(), cargarEstadisticas(), cargarSucursalesAsignacion(), cargarSucursalesAsignadas(), cargarSucursalesDisponibles(), cargarSucursalesEliminacion(), cargarUsuariosCentrales() (+23 more)

### Community 12 - "Solicitudes Stock UI"
Cohesion: 0.12
Nodes (28): actualizarContadorProductos(), actualizarListaProductosSeleccionados(), actualizarResumen(), actualizarTotalesProducto(), agregarProductoALista(), buscarRemisiones(), cargarHistorialEnModal(), cargarProductosEnModal() (+20 more)

### Community 13 - "Instalador SQL Migraciones"
Cohesion: 0.10
Nodes (24): ejecutarSQLConComparacion(), actualizarSucursalesSeleccionadas(), aplicarCambiosSQLCentral(), cargarSucursalesActivas(), conectarBD(), confirmarSeleccion(), crearTablasBD(), deseleccionarTodas() (+16 more)

### Community 14 - "Crear Despacho UI"
Cohesion: 0.12
Nodes (24): actualizarCantidadProducto(), actualizarInventarioLocal(), actualizarVistaProductosDespacho(), buscarProductosEnTodasLasSucursales(), buscarSolicitudesStock(), cargarProductosDesdeSolicitud(), cargarProductosDeSolicitud(), cargarProductosInventario() (+16 more)

### Community 15 - "BD Central Solicitudes"
Cohesion: 0.11
Nodes (3): ConexionCentral, ModeloSolicitudesStock, TrazabilidadAjax

### Community 17 - "Clientes Centrales UI"
Cohesion: 0.12
Nodes (18): abrirModalCliente(), abrirModalSincronizacionBidireccional(), cargarClientesCentrales(), cargarEstadisticas(), cargarSucursalesAsignadas(), cargarSucursalesBidireccional(), confirmarBorrarClientes(), confirmarCopiarASucursal() (+10 more)

### Community 18 - "Categorias Locales"
Cohesion: 0.11
Nodes (4): AjaxCategorias, ControladorCategorias, ModeloCategorias, TablaProductos

### Community 19 - "Despachos UI Estados"
Cohesion: 0.16
Nodes (19): aceptarDespachoDirecto(), aceptarDespachoModal(), cancelarDespachoDirecto(), cancelarDespachoModal(), cargarProductosDespacho(), cargarTimelineDespacho(), configurarBotonesExportacion(), configurarBotonesModalDespacho() (+11 more)

### Community 21 - "Medios Pago Central UI"
Cohesion: 0.15
Nodes (14): abrirModalAsignarSucursales(), actualizarMedioPagoCentral(), cargarEstadoMediosSucursal(), cargarMediosPagoCentral(), cargarSucursalesDestinoAsignar(), confirmarAsignacionSucursales(), crearMedioPagoCentral(), desactivarEnTodasLasSucursales() (+6 more)

### Community 23 - "Clientes Locales"
Cohesion: 0.12
Nodes (3): AjaxClientes, ControladorClientes, ModeloClientes

### Community 26 - "Categorias Centrales UI"
Cohesion: 0.22
Nodes (13): actualizarTextoInfo(), cargarCategorias(), cargarSucursales(), crearCategoria(), editarCategoria(), ejecutarSincronizacion(), eliminarCategoria(), inicializarInterfaz() (+5 more)

### Community 28 - "Transportador Movil UI"
Cohesion: 0.26
Nodes (14): abrirDetalleSolicitudMovil(), ajaxTransportador(), cancelarDespachoMovil(), cargarDescargas(), cargarDespachos(), cargarResumen(), cargarSolicitudes(), cargarStock() (+6 more)

### Community 32 - "Crear Solicitud Stock UI"
Cohesion: 0.29
Nodes (11): actualizarContadorProductos(), actualizarEstadoBotonesAgregar(), actualizarListaProductosSeleccionados(), agregarProductoALista(), buscarRemisiones(), crearSolicitud(), eliminarProducto(), habilitarBotonCrear() (+3 more)

### Community 33 - "Ventas UI"
Cohesion: 0.18
Nodes (5): actualizarListaClientes(), buscarClientes(), mostrarSugerencias(), seleccionarCliente(), sumarTotalPrecios()

### Community 40 - "Sucursales UI Sync"
Cohesion: 0.25
Nodes (6): cargarEstadoSucursalActual(), iniciarSincronizacionCatalogo(), mostrarModalProgreso(), mostrarNotificacionSincronizacion(), ocultarModalProgreso(), sincronizarSucursalesBidireccional()

### Community 41 - "Trazabilidad Mercancia UI"
Cohesion: 0.38
Nodes (10): buscarGeneral(), buscarPorDespacho(), buscarPorProducto(), cargarReportes(), formatearFecha(), mostrarAlerta(), mostrarReportes(), mostrarResultadosDespacho() (+2 more)

### Community 44 - "personalizacion-colores.js"
Cohesion: 0.29
Nodes (6): actualizarTextoColor(), actualizarVistaPrevia(), cargarConfiguracionExistente(), guardarConfiguracion(), inicializarVistaPrevia(), validarDatosConfiguracion()

### Community 45 - "crear-despacho.php"
Cohesion: 0.38
Nodes (7): actualizarCantidadProductoDespacho(), actualizarContadoresEdicion(), agregarFilaProductoDespacho(), cargarProductosDespachoEdicion(), cargarProductosSinStockActual(), confirmarEditarCantidad(), obtenerStockActualProductos()

### Community 46 - "Despachos y Stock en Tránsito"
Cohesion: 0.22
Nodes (9): Despachos y Stock en Tránsito, Perfiles y Permisos, Documentación Técnica AdminV5, Arquitectura Central + Sucursales, Módulos Principales ERP, Sistema Gestión Multi-Sucursal AdminV5, Resumen Completo Software, Flujos Operativos Multi-Sucursal (+1 more)

### Community 49 - "stock-transito-unificado.js"
Cohesion: 0.36
Nodes (7): buscarProductos(), filtrarPorTransportador(), limpiarBusqueda(), limpiarFiltros(), registrarDescargaDirecta(), registrarDescargaOptimizada(), registrarDescargaSimple()

### Community 54 - "stock-transito.js"
Cohesion: 0.43
Nodes (6): actualizarTabla(), cargarTablaStockTransito(), configurarEventosStock(), mostrarModalSolicitarDescarga(), procesarSolicitudDescarga(), verHistorialProducto()

### Community 55 - "usuarios-central-old.php"
Cohesion: 0.36
Nodes (5): cargarUsuariosCentrales(), cargarUsuariosSucursales(), guardarUsuarioCentral(), importarUsuariosSucursales(), mostrarUsuariosSucursales()

### Community 60 - "cabezote.php"
Cohesion: 0.43
Nodes (4): actualizarContadorSeguro(), actualizarListaSegura(), cargarNotificacionesSeguras(), manejarError()

### Community 61 - "personalizacion-colores-simplificado-fun"
Cohesion: 0.38
Nodes (4): actualizarVistaPreviaEditar(), editarConfiguracion(), mostrarError(), subirImagen()

### Community 64 - "productos-stock-sucursales.js"
Cohesion: 0.60
Nodes (4): cargarStockPorSucursales(), escapeHtml(), iconoStock(), renderizarTabla()

### Community 65 - "timezone-bogota.js"
Cohesion: 0.53
Nodes (4): getCurrentDateBogota(), getDateRangeBogota(), updateDashboardBoxes(), updateDateFilters()

## Knowledge Gaps
- **7 isolated node(s):** `FormaPago`, `MedioPago`, `sucursalesDisponibles`, `sucursalesSeleccionadas`, `Perfiles y Permisos` (+2 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 468 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **205 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **What connects `FormaPago`, `MedioPago`, `sucursalesDisponibles` to the rest of the system?**
  _7 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `BD Local Conexion Contabilidad` be split into smaller, more focused modules?**
  _Cohesion score 0.06823529411764706 - nodes in this community are weakly interconnected._
- **Should `Despachos Multi-Sucursal` be split into smaller, more focused modules?**
  _Cohesion score 0.05053191489361702 - nodes in this community are weakly interconnected._
- **Should `Usuarios Centrales DataTable` be split into smaller, more focused modules?**
  _Cohesion score 0.05920444033302498 - nodes in this community are weakly interconnected._
- **Should `Ventas AJAX Controlador` be split into smaller, more focused modules?**
  _Cohesion score 0.05858585858585859 - nodes in this community are weakly interconnected._
- **Should `Categorias Centrales` be split into smaller, more focused modules?**
  _Cohesion score 0.06951219512195123 - nodes in this community are weakly interconnected._
- **Should `Productos Catalogo Transferencia` be split into smaller, more focused modules?**
  _Cohesion score 0.06097560975609756 - nodes in this community are weakly interconnected._