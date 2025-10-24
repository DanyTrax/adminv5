<?php
// Verificar permisos de administrador
if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    echo '<script>
        swal({
            title: "Acceso Denegado",
            text: "No tienes permisos para acceder a esta sección",
            type: "error",
            confirmButtonText: "Aceptar"
        }).then(function() {
            window.location = "inicio";
        });
    </script>';
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Gestión de Categorías Centrales
            <small>Administrar categorías para todas las sucursales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="gestión-central">Gestión Central</a></li>
            <li class="active">Categorías Centrales</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <!-- ACCIONES DEL SISTEMA -->
            <div class="col-lg-12 col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tags"></i> Acciones del Sistema</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-primary btnCrearCategoriaCentral">
                                    <i class="fa fa-plus"></i> Crear Categoría Central
                                </button>
                                <button class="btn btn-success btnSincronizarCategorias" style="margin-left: 10px;">
                                    <i class="fa fa-refresh"></i> Sincronizar con Sucursales
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <button class="btn btn-default btnFiltrarActivas">
                                        <i class="fa fa-check-circle"></i> Solo Activas
                                    </button>
                                    <button class="btn btn-default btnFiltrarTodas">
                                        <i class="fa fa-list"></i> Todas
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Información:</strong> Las categorías creadas aquí se sincronizarán automáticamente con todas las sucursales activas. 
                                    Los cambios se aplicarán a todas las sucursales cuando uses el botón "Sincronizar con Sucursales".
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE CATEGORÍAS -->
            <div class="col-lg-12 col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tags"></i> Categorías Centrales</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive tablaCategoriasCentral" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Sincronizado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--=====================================
MODAL CREAR CATEGORÍA CENTRAL
======================================-->
<div id="modalCrearCategoriaCentral" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" id="formCrearCategoriaCentral">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Crear Categoría Central</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="categoria">Nombre de la Categoría <span class="text-red">*</span></label>
                            <input type="text" class="form-control input-lg" name="categoria" id="categoria" 
                                   placeholder="Ingrese el nombre de la categoría" required>
                            <div class="help-block text-red" id="error-categoria"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="3" 
                                      placeholder="Descripción opcional de la categoría"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="activo" id="activo" checked>
                                    Categoría activa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=====================================
MODAL EDITAR CATEGORÍA CENTRAL
======================================-->
<div id="modalEditarCategoriaCentral" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" id="formEditarCategoriaCentral">
                <div class="modal-header" style="background:#f39c12; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Editar Categoría Central</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <input type="hidden" name="id" id="idCategoriaEditar">
                        
                        <div class="form-group">
                            <label for="categoriaEditar">Nombre de la Categoría <span class="text-red">*</span></label>
                            <input type="text" class="form-control input-lg" name="categoria" id="categoriaEditar" 
                                   placeholder="Ingrese el nombre de la categoría" required>
                            <div class="help-block text-red" id="error-categoria-editar"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcionEditar">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcionEditar" rows="3" 
                                      placeholder="Descripción opcional de la categoría"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="activo" id="activoEditar">
                                    Categoría activa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=====================================
MODAL CONFIRMAR ELIMINACIÓN
======================================-->
<div id="modalConfirmarEliminacionCategoria" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#d9534f; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <p><strong>¿Estás seguro de que deseas desactivar esta categoría?</strong></p>
                    <p>La categoría <strong id="nombreCategoriaEliminar"></strong> será desactivada y se eliminará de todas las sucursales en la próxima sincronización.</p>
                    <div class="alert alert-warning">
                        <i class="fa fa-warning"></i>
                        <strong>Nota:</strong> Esta acción no se puede deshacer. Los productos asociados a esta categoría podrían verse afectados.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminacionCategoria">Desactivar Categoría</button>
            </div>
        </div>
    </div>
</div>

<!--=====================================
MODAL SINCRONIZAR CATEGORÍAS
======================================-->
<div id="modalSincronizarCategorias" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#5cb85c; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Sincronizar Categorías</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="text-center">
                        <i class="fa fa-refresh fa-3x text-success" style="margin-bottom: 20px;"></i>
                        <h4>¿Sincronizar Categorías con Sucursales?</h4>
                        <p>Esta acción actualizará las categorías en todas las sucursales activas del sistema.</p>
                        
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Información:</strong> Se sincronizarán todas las categorías activas con las sucursales configuradas.
                        </div>
                        
                        <div id="info-sincronizacion" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="fa fa-clock-o"></i>
                                <strong>Sincronizando...</strong> Por favor espera mientras se procesan las categorías.
                            </div>
                        </div>
                        
                        <div id="resultado-sincronizacion" style="display: none;">
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i>
                                <strong>Sincronización Completada</strong>
                                <div id="detalles-sincronizacion"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarSincronizacion">
                    <i class="fa fa-refresh"></i> Sí, Sincronizar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="vistas/js/categorias-central.js"></script>
