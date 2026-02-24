<?php
/**
 * Botones de atajo para el tablero de inicio
 * Muestra todas las funciones habilitadas según el perfil del usuario
 */

// Definir los módulos disponibles con sus iconos y descripciones
$modulos_disponibles = [
    "usuarios" => [
        "icono" => "fa-users",
        "titulo" => "Usuarios",
        "descripcion" => "Gestión de usuarios del sistema",
        "color" => "bg-blue",
        "perfiles" => ["Administrador"]
    ],
    "ventas" => [
        "icono" => "fa-shopping-cart",
        "titulo" => "Ventas",
        "descripcion" => "Gestión de ventas y facturación",
        "color" => "bg-green",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "crear-venta" => [
        "icono" => "fa-plus-circle",
        "titulo" => "Crear Venta",
        "descripcion" => "Registrar nueva venta",
        "color" => "bg-green",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "productos" => [
        "icono" => "fa-cube",
        "titulo" => "Productos",
        "descripcion" => "Catálogo de productos",
        "color" => "bg-yellow",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "clientes" => [
        "icono" => "fa-user",
        "titulo" => "Clientes",
        "descripcion" => "Base de datos de clientes",
        "color" => "bg-purple",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "categorias" => [
        "icono" => "fa-tags",
        "titulo" => "Categorías",
        "descripcion" => "Clasificación de productos",
        "color" => "bg-orange",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "reportes" => [
        "icono" => "fa-bar-chart",
        "titulo" => "Reportes",
        "descripcion" => "Reportes y estadísticas",
        "color" => "bg-red",
        "perfiles" => ["Administrador", "Especial"]
    ],
    "reporte-detallado" => [
        "icono" => "fa-file-text",
        "titulo" => "Reporte Detallado",
        "descripcion" => "Reportes detallados del sistema",
        "color" => "bg-red",
        "perfiles" => ["Administrador", "Especial"]
    ],
    "medios-pago" => [
        "icono" => "fa-credit-card",
        "titulo" => "Medios de Pago",
        "descripcion" => "Gestión de formas de pago",
        "color" => "bg-teal",
        "perfiles" => ["Administrador", "Especial"]
    ],
    "contabilidad" => [
        "icono" => "fa-calculator",
        "titulo" => "Contabilidad",
        "descripcion" => "Módulo contable",
        "color" => "bg-maroon",
        "perfiles" => ["Administrador", "Especial"]
    ],
    "entradas" => [
        "icono" => "fa-arrow-down",
        "titulo" => "Entradas",
        "descripcion" => "Registro de entradas",
        "color" => "bg-navy",
        "perfiles" => ["Administrador", "Contador"]
    ],
    "crear-entradas" => [
        "icono" => "fa-plus",
        "titulo" => "Crear Entrada",
        "descripcion" => "Registrar nueva entrada",
        "color" => "bg-navy",
        "perfiles" => ["Administrador", "Contador"]
    ],
    "gastos" => [
        "icono" => "fa-money",
        "titulo" => "Gastos",
        "descripcion" => "Registro de gastos",
        "color" => "bg-red",
        "perfiles" => ["Administrador", "Contador"]
    ],
    "crear-gastos" => [
        "icono" => "fa-plus-circle",
        "titulo" => "Crear Gasto",
        "descripcion" => "Registrar nuevo gasto",
        "color" => "bg-red",
        "perfiles" => ["Administrador", "Contador"]
    ],
    "salidas-inventario" => [
        "icono" => "fa-sign-out",
        "titulo" => "Salidas Inventario",
        "descripcion" => "Control de salidas de inventario",
        "color" => "bg-purple",
        "perfiles" => ["Administrador", "Especial", "Vendedor"]
    ],
    "sucursales" => [
        "icono" => "fa-building",
        "titulo" => "Sucursales",
        "descripcion" => "Gestión de sucursales",
        "color" => "bg-blue",
        "perfiles" => ["Administrador"]
    ],
    "usuarios-central" => [
        "icono" => "fa-users",
        "titulo" => "Usuarios Central",
        "descripcion" => "Usuarios del sistema central",
        "color" => "bg-blue",
        "perfiles" => ["Administrador"]
    ],
    "clientes-central" => [
        "icono" => "fa-user-circle",
        "titulo" => "Clientes Central",
        "descripcion" => "Clientes centralizados",
        "color" => "bg-purple",
        "perfiles" => ["Administrador"]
    ],
    "categorias-central" => [
        "icono" => "fa-tags",
        "titulo" => "Categorías Central",
        "descripcion" => "Categorías centralizadas",
        "color" => "bg-orange",
        "perfiles" => ["Administrador"]
    ],
    "personalizacion-colores-simplificado" => [
        "icono" => "fa-paint-brush",
        "titulo" => "Personalización",
        "descripcion" => "Personalizar colores del sistema",
        "color" => "bg-pink",
        "perfiles" => ["Administrador"]
    ],
    "despachos" => [
        "icono" => "fa-truck",
        "titulo" => "Despachos",
        "descripcion" => "Gestión de despachos",
        "color" => "bg-green",
        "perfiles" => ["Administrador", "Vendedor", "Contador", "Transportador"]
    ],
    "crear-despacho" => [
        "icono" => "fa-plus-square",
        "titulo" => "Crear Despacho",
        "descripcion" => "Crear nuevo despacho",
        "color" => "bg-green",
        "perfiles" => ["Administrador", "Especial", "Vendedor"]
    ],
    "stock-transito" => [
        "icono" => "fa-exchange",
        "titulo" => "Stock en Tránsito",
        "descripcion" => "Productos en movimiento",
        "color" => "bg-yellow",
        "perfiles" => ["Administrador", "Vendedor", "Contador", "Transportador"]
    ],
    "solicitudes-stock" => [
        "icono" => "fa-clipboard",
        "titulo" => "Solicitudes Stock",
        "descripcion" => "Solicitudes de productos",
        "color" => "bg-orange",
        "perfiles" => ["Administrador", "Vendedor", "Contador", "Transportador"]
    ],
    "crear-solicitud-stock" => [
        "icono" => "fa-plus",
        "titulo" => "Crear Solicitud de Stock",
        "descripcion" => "Nueva solicitud de stock",
        "color" => "bg-orange",
        "perfiles" => ["Administrador", "Vendedor", "Contador"]
    ],
    "registro-descargas-funcional" => [
        "icono" => "fa-download",
        "titulo" => "Registro Descargas",
        "descripcion" => "Registro de descargas",
        "color" => "bg-teal",
        "perfiles" => ["Administrador", "Vendedor", "Contador", "Transportador"]
    ]
];

// Obtener el perfil del usuario actual
$perfil_usuario = $_SESSION["perfil"] ?? "Limitado";

// Filtrar módulos disponibles para el perfil actual
$modulos_perfil = [];
foreach ($modulos_disponibles as $ruta => $modulo) {
    if (in_array($perfil_usuario, $modulo["perfiles"])) {
        $modulos_perfil[$ruta] = $modulo;
    }
}

// Agrupar módulos por categorías para mejor organización
$categorias = [
    "Ventas" => ["ventas", "crear-venta", "clientes"],
    "Inventario" => ["productos", "categorias", "salidas-inventario"],
    "Logística" => [
        0 => "solicitudes-stock",
        1 => "crear-solicitud-stock", 
        2 => "despachos",
        3 => "crear-despacho",
        4 => "stock-transito",
        5 => "registro-descargas-funcional"
    ],
    "Administración" => ["usuarios", "sucursales", "usuarios-central", "clientes-central", "categorias-central", "personalizacion-colores-simplificado"],
    "Contabilidad" => ["entradas", "crear-entradas", "gastos", "crear-gastos", "reportes", "reporte-detallado", "medios-pago", "contabilidad"]
];

?>

<!-- Botones de Atajo -->
<div class="row">
    <div class="col-lg-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-rocket"></i> Acceso Rápido
                </h3>
                <div class="box-tools pull-right">
                    <span class="label label-info"><?php echo count($modulos_perfil); ?> módulos disponibles</span>
                </div>
            </div>
            <div class="box-body">
                
                <?php foreach ($categorias as $nombre_categoria => $modulos_categoria): ?>
                    <?php 
                    // Filtrar módulos de esta categoría que estén disponibles para el perfil
                    $modulos_categoria_disponibles = array_intersect_key($modulos_perfil, array_flip($modulos_categoria));
                    if (empty($modulos_categoria_disponibles)) continue;
                    ?>
                    
                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <h4 style="color: #3c8dbc; border-bottom: 2px solid #3c8dbc; padding-bottom: 5px;">
                            <i class="fa fa-folder"></i> <?php echo $nombre_categoria; ?>
                        </h4>
                        
                        <div class="row">
                            <?php 
                            // Para Logística, mantener el orden específico
                            if ($nombre_categoria == "Logística") {
                                $orden_logistica = ["solicitudes-stock", "crear-solicitud-stock", "despachos", "crear-despacho", "stock-transito", "registro-descargas-funcional"];
                                foreach ($orden_logistica as $ruta) {
                                    if (isset($modulos_categoria_disponibles[$ruta])) {
                                        $modulo = $modulos_categoria_disponibles[$ruta];
                                        ?>
                                        <div class="col-lg-3 col-md-4 col-sm-6" style="margin-bottom: 15px;">
                                            <a href="<?php echo $url . $ruta; ?>" class="btn btn-app" style="width: 100%; height: 80px; padding: 10px;">
                                                <span class="badge <?php echo $modulo['color']; ?>" style="font-size: 20px; padding: 8px;">
                                                    <i class="fa <?php echo $modulo['icono']; ?>"></i>
                                                </span>
                                                <div style="margin-top: 5px;">
                                                    <strong style="font-size: 12px;"><?php echo $modulo['titulo']; ?></strong>
                                                </div>
                                            </a>
                                        </div>
                                        <?php
                                    }
                                }
                            } else {
                                // Para otras categorías, usar el orden normal
                                foreach ($modulos_categoria_disponibles as $ruta => $modulo): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6" style="margin-bottom: 15px;">
                                        <a href="<?php echo $url . $ruta; ?>" class="btn btn-app" style="width: 100%; height: 80px; padding: 10px;">
                                            <span class="badge <?php echo $modulo['color']; ?>" style="font-size: 20px; padding: 8px;">
                                                <i class="fa <?php echo $modulo['icono']; ?>"></i>
                                            </span>
                                            <div style="margin-top: 5px;">
                                                <strong style="font-size: 12px;"><?php echo $modulo['titulo']; ?></strong>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach;
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Módulos adicionales que no están en categorías específicas -->
                <?php 
                $modulos_categorizados = [];
                foreach ($categorias as $modulos_categoria) {
                    $modulos_categorizados = array_merge($modulos_categorizados, $modulos_categoria);
                }
                $modulos_adicionales = array_diff_key($modulos_perfil, array_flip($modulos_categorizados));
                
                if (!empty($modulos_adicionales)): ?>
                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <h4 style="color: #3c8dbc; border-bottom: 2px solid #3c8dbc; padding-bottom: 5px;">
                            <i class="fa fa-plus-circle"></i> Otros Módulos
                        </h4>
                        
                        <div class="row">
                            <?php foreach ($modulos_adicionales as $ruta => $modulo): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6" style="margin-bottom: 15px;">
                                    <a href="<?php echo $url . $ruta; ?>" class="btn btn-app" style="width: 100%; height: 80px; padding: 10px;">
                                        <span class="badge <?php echo $modulo['color']; ?>" style="font-size: 20px; padding: 8px;">
                                            <i class="fa <?php echo $modulo['icono']; ?>"></i>
                                        </span>
                                        <div style="margin-top: 5px;">
                                            <strong style="font-size: 12px;"><?php echo $modulo['titulo']; ?></strong>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<style>
.btn-app {
    border-radius: 3px;
    position: relative;
    padding: 15px 5px;
    margin: 0 0 10px 10px;
    min-width: 80px;
    height: 60px;
    text-align: center;
    color: #666;
    border: 1px solid #ddd;
    background-color: #f4f4f4;
    font-size: 12px;
    transition: all 0.3s ease;
}

.btn-app:hover {
    background: #e6e6e6;
    color: #333;
    border-color: #adadad;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.btn-app .badge {
    position: absolute;
    top: -3px;
    right: -3px;
    font-size: 0.75em;
    padding: 3px 7px;
    border-radius: 50%;
}

.badge.bg-blue { background-color: #3c8dbc !important; }
.badge.bg-green { background-color: #00a65a !important; }
.badge.bg-yellow { background-color: #f39c12 !important; }
.badge.bg-red { background-color: #dd4b39 !important; }
.badge.bg-purple { background-color: #605ca8 !important; }
.badge.bg-maroon { background-color: #d81b60 !important; }
.badge.bg-teal { background-color: #39cccc !important; }
.badge.bg-navy { background-color: #001f3f !important; }
.badge.bg-orange { background-color: #ff851b !important; }
</style>
