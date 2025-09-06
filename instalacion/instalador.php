<?php
// Verificación de autenticación simple
session_start();

if (!isset($_SESSION['instalacion_logueado']) || $_SESSION['instalacion_logueado'] !== true) {
    header('Location: index.php');
    exit;
}

// Verificar tiempo de sesión
if (!isset($_SESSION['instalacion_tiempo']) || (time() - $_SESSION['instalacion_tiempo']) > 3600) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Actualizar tiempo de actividad
$_SESSION['instalacion_tiempo'] = time();

// Botón de logout
echo '
<style>
.logout-btn {
    position: fixed; top: 20px; right: 20px; background: #dc3545; color: white;
    padding: 8px 15px; border: none; border-radius: 5px; text-decoration: none;
    font-size: 12px; z-index: 1000; cursor: pointer;
}
.logout-btn:hover { background: #c82333; color: white; }
</style>
<a href="logout.php" class="logout-btn" onclick="return confirm(\'¿Cerrar sesión?\')">
    🔓 Cerrar Sesión
</a>';

// CONTINÚA CON EL RESTO DEL INSTALADOR...

// ===================================================================
// INSTALADOR DE BASE DE DATOS LOCAL PARA SUCURSALES
// danytrax/adminv5 - Configuración de derivaciones
// ===================================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

$INSTALADOR_VERSION = "1.0";
$FECHA_INSTALACION = date('Y-m-d H:i:s');

// ✅ AGREGAR ESTA FUNCIÓN AQUÍ
/**
 * Función para actualizar físicamente la URL del API en plantilla.php
 */
function actualizarUrlApiEnPlantilla($nuevaUrlApi) {
    $archivoPlantilla = '../vistas/plantilla.php';

    if (!file_exists($archivoPlantilla)) {
        return ['success' => false, 'message' => 'Archivo plantilla.php no encontrado'];
    }

    // Leer el contenido actual
    $contenido = file_get_contents($archivoPlantilla);

    if ($contenido === false) {
        return ['success' => false, 'message' => 'No se pudo leer el archivo plantilla.php'];
    }

    // Formatear la URL (asegurar que termine con /)
    $urlFormateada = rtrim($nuevaUrlApi, '/') . '/';

    // Buscar y reemplazar la línea del apiUrl
    $patron = '/const apiUrl = "[^"]*";/';
    $reemplazo = 'const apiUrl = "' . $urlFormateada . '";';

    $nuevoContenido = preg_replace($patron, $reemplazo, $contenido);

    if ($nuevoContenido === null) {
        return ['success' => false, 'message' => 'Error al procesar la URL del API'];
    }

    // Verificar si realmente se hizo el cambio
    if ($nuevoContenido === $contenido) {
        return ['success' => false, 'message' => 'No se encontró la línea const apiUrl para actualizar'];
    }

    // Escribir el archivo modificado
    $resultado = file_put_contents($archivoPlantilla, $nuevoContenido);

    if ($resultado === false) {
        return ['success' => false, 'message' => 'No se pudo escribir el archivo plantilla.php'];
    }

    return ['success' => true, 'message' => 'URL del API actualizada correctamente en plantilla.php'];
}

/**
 * Función auxiliar para verificar conexión a BD
 */
function verificarConexion($host, $usuario, $password, $bd = null) {
    try {
        $dsn = "mysql:host=$host" . ($bd ? ";dbname=$bd" : "") . ";charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalador BD Local - AdminV5</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .step { background: #f8f9ff; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #667eea; outline: none; }
        .btn { background: #667eea; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #5a67d8; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .success { background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border: 1px solid #b8daff; border-radius: 5px; margin: 10px 0; }
        .progress { background: #e9ecef; height: 20px; border-radius: 10px; overflow: hidden; margin: 20px 0; }
        .progress-bar { background: #667eea; height: 100%; transition: width 0.3s ease; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; border: 1px solid #e9ecef; margin: 10px 0; }
        .config-actual { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
    <script>
        function confirmarInstalacion() {
            return confirm('¿Está seguro de que desea iniciar la instalación? Este proceso modificará la base de datos.');
        }
        
        function copiarConfiguracion() {
            const textarea = document.getElementById('nuevaConfiguracion');
            textarea.select();
            document.execCommand('copy');
            alert('Configuración copiada al portapapeles');
        }
        
        function generarNombreBD() {
            const codigoSucursal = document.getElementById('codigo_sucursal').value.toLowerCase();
            if(codigoSucursal) {
                const nombreBD = 'epicosie_' + codigoSucursal.replace(/[^a-z0-9]/g, '');
                document.getElementById('bd_nombre').value = nombreBD;
            }
        }
    </script>
</head>
<body>

<div class="container">
    
    <div class="header">
        <h1>🚀 Instalador de Base de Datos Local</h1>
        <p>AdminV5 - Sistema de Gestión de Sucursales</p>
        <small>Versión <?php echo $INSTALADOR_VERSION; ?></small>
    </div>
    
    <div class="content">
        
        <?php if (!isset($_POST['action'])): ?>
        
        <!-- ===== DETECCIÓN AUTOMÁTICA DE CONFIGURACIÓN ===== -->
        <div class="step">
            <h2>🔍 Configuración Actual Detectada</h2>
            <div class="config-actual">
                <p><strong>📊 Base de Datos Local Actual:</strong></p>
                <ul>
                    <li><strong>Host:</strong> localhost</li>
                    <li><strong>BD:</strong> epicosie_pruebas</li>
                    <li><strong>Usuario:</strong> epicosie_ricaurte</li>
                    <li><strong>Estructura:</strong> ✅ Tablas existentes detectadas</li>
                </ul>
                <p><em>El instalador configurará una nueva derivación manteniendo la BD central separada.</em></p>
            </div>
        </div>
        
        <form method="POST" id="instaladorForm">
            <input type="hidden" name="action" value="instalar">
            
            <!-- Configuración de Sucursal -->
            <div class="step">
                <h3>🏪 Configuración de Nueva Sucursal</h3>
                
                <div class="form-group">
                    <label for="nombre_sucursal">Nombre de la Sucursal:</label>
                    <input type="text" id="nombre_sucursal" name="nombre_sucursal" required 
                           placeholder="Ej: Sucursal Centro, Sucursal Norte">
                </div>
                
                <div class="form-group">
                    <label for="codigo_sucursal">Código de Sucursal:</label>
                    <input type="text" id="codigo_sucursal" name="codigo_sucursal" required 
                           placeholder="Ej: CENTRO, NORTE, SUR" pattern="[A-Z0-9]{3,10}"
                           onkeyup="generarNombreBD()" style="text-transform: uppercase;">
                    <small>Solo letras mayúsculas y números, 3-10 caracteres</small>
                </div>
            </div>
            
            <!-- Configuración BD -->
            <div class="step">
                <h3>💾 Nueva Base de Datos Local</h3>
                
                <div class="form-group">
                    <label for="bd_host">Host de BD:</label>
                    <input type="text" id="bd_host" name="bd_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="bd_usuario">Usuario de BD (debe tener permisos CREATE):</label>
                    <input type="text" id="bd_usuario" name="bd_usuario" value="epicosie_ricaurte" required>
                </div>
                
                <div class="form-group">
                    <label for="bd_password">Contraseña de BD:</label>
                    <input type="password" id="bd_password" name="bd_password" required
                           placeholder="Contraseña del usuario de BD">
                </div>
                
                <div class="form-group">
                    <label for="bd_nombre">Nombre de la Nueva BD:</label>
                    <input type="text" id="bd_nombre" name="bd_nombre" required 
                           placeholder="Se genera automáticamente" pattern="[a-zA-Z0-9_]{5,50}">
                    <small>Se generará automáticamente: epicosie_[código_sucursal]</small>
                </div>
            </div>
            
            <!-- Opciones -->
            <div class="step">
                <h3>⚙️ Opciones de Instalación</h3>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="verificar_central" checked>
                        Verificar conexión con BD Central
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="crear_usuario_admin" checked>
                        Crear usuario administrador para la sucursal
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="sincronizar_categorias" checked>
                        Sincronizar categorías desde BD Central
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="crear_archivo_conexion" checked>
                        Crear nuevo archivo de conexión
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="registrar_en_central">
                        Registrar sucursal en BD Central
                    </label>
                </div>
            <!-- Importación desde otras sucursales -->
            <div class="step">
                <h3>📊 Importar Datos de Otras Sucursales (Opcional)</h3>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="habilitar_importacion" id="habilitarImportacion" onchange="toggleImportacion()">
                        Importar clientes y usuarios desde otra sucursal existente
                    </label>
                    <small>Selecciona datos de sucursales ya configuradas para copiar a esta nueva instalación</small>
                </div>
                
                <!-- Contenedor de importación (oculto inicialmente) -->
                <div id="contenedorImportacion" style="display: none; background: #f8f9ff; padding: 20px; border-radius: 8px; margin-top: 15px;">
                    
                    <!-- Selección de sucursal origen -->
                    <div class="form-group">
                        <label for="sucursal_origen">Sucursal de origen:</label>
                        <select id="sucursal_origen" name="sucursal_origen" onchange="cargarDatosSucursal()" style="width: 100%; padding: 10px;">
                            <option value="">Seleccionar sucursal...</option>
                            <option value="epicosie_pruebas">Sucursal Principal (epicosie_pruebas)</option>
                            <!-- Las demás sucursales se cargarán dinámicamente -->
                        </select>
                        <small>Selecciona la sucursal desde donde quieres importar datos</small>
                    </div>
                    
                    <!-- Importar clientes -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="importar_clientes" id="importarClientes" onchange="toggleSeccionClientes()">
                            Importar clientes desde la sucursal seleccionada
                        </label>
                    </div>
                    
                    <div id="seccionClientes" style="display: none; background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0;">
                        <h4>👥 Seleccionar Clientes a Importar</h4>
                        <div id="listaClientes">
                            <p><em>Selecciona una sucursal para ver los clientes disponibles</em></p>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="button" onclick="seleccionarTodosClientes()" style="padding: 5px 10px; margin-right: 10px;">Seleccionar Todos</button>
                            <button type="button" onclick="deseleccionarTodosClientes()" style="padding: 5px 10px;">Deseleccionar Todos</button>
                        </div>
                    </div>
                    
                    <!-- Importar usuarios -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="importar_usuarios" id="importarUsuarios" onchange="toggleSeccionUsuarios()">
                            Importar usuarios desde la sucursal seleccionada
                        </label>
                    </div>
                    
                    <div id="seccionUsuarios" style="display: none; background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0;">
                        <h4>👤 Seleccionar Usuarios a Importar</h4>
                        <div class="warning" style="margin-bottom: 10px;">
                            <strong>⚠️ Advertencia:</strong> Los usuarios importados mantendrán sus contraseñas originales. Se recomienda cambiarlas después de la importación.
                        </div>
                        <div id="listaUsuarios">
                            <p><em>Selecciona una sucursal para ver los usuarios disponibles</em></p>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="button" onclick="seleccionarTodosUsuarios()" style="padding: 5px 10px; margin-right: 10px;">Seleccionar Todos</button>
                            <button type="button" onclick="deseleccionarTodosUsuarios()" style="padding: 5px 10px;">Deseleccionar Todos</button>
                        </div>
                    </div>
                    
                    <!-- Resumen de importación -->
                    <div id="resumenImportacion" style="background: #e8f4f8; padding: 15px; border-radius: 5px; margin-top: 15px; display: none;">
                        <h4>📋 Resumen de Importación</h4>
                        <div id="contenidoResumen"></div>
                    </div>
                    
                </div>
            </div>

            <!-- JavaScript para manejar la importación -->
<script>
    function toggleImportacion() {
        const checkbox = document.getElementById('habilitarImportacion');
        const contenedor = document.getElementById('contenedorImportacion');
        
        if(checkbox.checked) {
            contenedor.style.display = 'block';
            cargarSucursalesDisponibles();
        } else {
            contenedor.style.display = 'none';
            // Limpiar selecciones
            document.getElementById('importarClientes').checked = false;
            document.getElementById('importarUsuarios').checked = false;
            toggleSeccionClientes();
            toggleSeccionUsuarios();
        }
    }

    function toggleSeccionClientes() {
        const checkbox = document.getElementById('importarClientes');
        const seccion = document.getElementById('seccionClientes');
        
        seccion.style.display = checkbox.checked ? 'block' : 'none';
        
        if(checkbox.checked) {
            cargarDatosSucursal();
        }
        actualizarResumen();
    }

    function toggleSeccionUsuarios() {
        const checkbox = document.getElementById('importarUsuarios');
        const seccion = document.getElementById('seccionUsuarios');
        
        seccion.style.display = checkbox.checked ? 'block' : 'none';
        
        if(checkbox.checked) {
            cargarDatosSucursal();
        }
        actualizarResumen();
    }

    // ✅ FUNCIÓN MEJORADA PARA CARGAR SUCURSALES DISPONIBLES
    function cargarSucursalesDisponibles() {
        const select = document.getElementById('sucursal_origen');
        
        // Mostrar indicador de carga
        select.innerHTML = '<option value="">Cargando sucursales...</option>';
        
        // Hacer petición AJAX para obtener sucursales
        fetch('ajax-datos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'obtener_sucursales'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Limpiar opciones
            select.innerHTML = '<option value="">Seleccionar sucursal...</option>';
            
            if (data.success && data.sucursales && data.sucursales.length > 0) {
                data.sucursales.forEach(sucursal => {
                    const option = document.createElement('option');
                    option.value = sucursal.bd_nombre;
                    option.textContent = `${sucursal.nombre} (${sucursal.bd_nombre})`;
                    select.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No hay sucursales disponibles";
                select.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error cargando sucursales:', error);
            select.innerHTML = '<option value="">Error cargando sucursales</option>';
        });
    }

    function cargarDatosSucursal() {
        const sucursalOrigen = document.getElementById('sucursal_origen').value;
        const importarClientes = document.getElementById('importarClientes').checked;
        const importarUsuarios = document.getElementById('importarUsuarios').checked;
        
        if(!sucursalOrigen) {
            document.getElementById('listaClientes').innerHTML = '<p><em>Selecciona una sucursal para ver los clientes disponibles</em></p>';
            document.getElementById('listaUsuarios').innerHTML = '<p><em>Selecciona una sucursal para ver los usuarios disponibles</em></p>';
            return;
        }
        
        if(importarClientes) {
            cargarClientes(sucursalOrigen);
        }
        
        if(importarUsuarios) {
            cargarUsuarios(sucursalOrigen);
        }
        
        actualizarResumen();
    }

    function cargarClientes(bdOrigen) {
        const contenedor = document.getElementById('listaClientes');
        contenedor.innerHTML = '<p>⏳ Obteniendo información de clientes...</p>';
        
        fetch('ajax-datos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'obtener_clientes',
                bd_origen: bdOrigen
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if(data.success && data.tipo_respuesta === 'resumen_clientes') {
                
                // ✅ MOSTRAR SOLO RESUMEN Y OPCIÓN DE IMPORTAR TODOS
                let html = `
                    <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
                        
                        <!-- Resumen principal -->
                        <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8f4f8; border-radius: 6px;">
                            <h4 style="margin: 0 0 10px 0; color: #0c5460;">
                                📊 Base de Datos: <code>${bdOrigen}</code>
                            </h4>
                            <div style="font-size: 24px; font-weight: bold; color: #28a745; margin: 10px 0;">
                                ${data.total_clientes} clientes encontrados
                            </div>
                            <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                ${data.mensaje}
                            </p>
                        </div>
                        
                        <!-- Estadísticas detalladas -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
                            <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 18px; font-weight: bold; color: #007bff;">${data.estadisticas.con_email}</div>
                                <div style="font-size: 12px; color: #666;">📧 Con Email</div>
                            </div>
                            <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 18px; font-weight: bold; color: #28a745;">${data.estadisticas.con_telefono}</div>
                                <div style="font-size: 12px; color: #666;">📞 Con Teléfono</div>
                            </div>
                            <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 18px; font-weight: bold; color: #ffc107;">${data.estadisticas.con_direccion}</div>
                                <div style="font-size: 12px; color: #666;">🏠 Con Dirección</div>
                            </div>
                            <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 18px; font-weight: bold; color: #dc3545;">${data.estadisticas.con_compras}</div>
                                <div style="font-size: 12px; color: #666;">🛒 Con Compras</div>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                            <h5 style="margin: 0 0 10px 0; color: #856404;">📅 Información Temporal:</h5>
                            <div style="font-size: 13px; color: #856404;">
                                <strong>Primer cliente:</strong> ${data.estadisticas.primer_cliente}<br>
                                <strong>Último cliente:</strong> ${data.estadisticas.ultimo_cliente}
                            </div>
                        </div>
                        
                        <!-- Opciones de importación -->
                        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 15px;">
                            <h5 style="margin: 0 0 15px 0; color: #155724;">⚙️ Opciones de Importación:</h5>
                            
                            <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="importar_clientes_opcion" value="todos" 
                                       style="margin-right: 10px;" onchange="manejarSeleccionClientes(this)">
                                <div>
                                    <strong>Importar TODOS los clientes</strong> (${data.total_clientes} registros)<br>
                                    <small style="color: #666;">Se importarán todos los clientes de la base de datos origen</small>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="importar_clientes_opcion" value="solo_con_datos" 
                                       style="margin-right: 10px;" onchange="manejarSeleccionClientes(this)">
                                <div>
                                    <strong>Solo clientes con datos completos</strong> (${data.estadisticas.con_email + data.estadisticas.con_telefono} aprox.)<br>
                                    <small style="color: #666;">Solo clientes que tengan email O teléfono registrado</small>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="importar_clientes_opcion" value="ninguno" checked
                                       style="margin-right: 10px;" onchange="manejarSeleccionClientes(this)">
                                <div>
                                    <strong>No importar clientes</strong><br>
                                    <small style="color: #666;">La sucursal empezará sin clientes</small>
                                </div>
                            </label>
                            
                            <!-- Campo oculto con los datos para el POST -->
                            <input type="hidden" name="clientes_importar_data" id="clientesImportarData" value="">
                        </div>
                        
                    </div>
                `;
                
                contenedor.innerHTML = html;
                
                // Guardar datos para importación
                window.datosClientesImportar = data.clientes_para_importar;
                
            } else {
                contenedor.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #666;">
                        <p><strong>ℹ️ Sin clientes</strong></p>
                        <p>No se encontraron clientes en: <code>${bdOrigen}</code></p>
                    </div>
                `;
            }
            
            actualizarResumen();
        })
        .catch(error => {
            console.error('Error cargando clientes:', error);
            contenedor.innerHTML = `
                <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
                    <p><strong>❌ Error cargando información de clientes</strong></p>
                    <p>${error.message}</p>
                    <p style="font-size: 12px;">Verifica la conexión y el archivo ajax-datos.php</p>
                </div>
            `;
        });
    }

    // ✅ FUNCIÓN CORREGIDA PARA MANEJAR SELECCIÓN DE CLIENTES
    function manejarSeleccionClientes(radio) {
        const datosField = document.getElementById('clientesImportarData');
        
        if (radio.value === 'todos' && window.datosClientesImportar) {
            // Preparar todos los IDs para importación
            const todosIds = window.datosClientesImportar.map(cliente => cliente.id);
            datosField.value = JSON.stringify({
                opcion: 'todos',
                ids: todosIds,
                total: todosIds.length
            });
            
            // ✅ FORZAR QUE EL CAMPO SE RECONOZCA COMO LLENO
            datosField.setAttribute('data-has-data', 'true');
            
            console.log('Datos de clientes preparados:', datosField.value); // Debug
            
        } else if (radio.value === 'solo_con_datos' && window.datosClientesImportar) {
            // En este caso, el filtrado se hará en el backend
            datosField.value = JSON.stringify({
                opcion: 'solo_con_datos',
                ids: [],
                total: 0
            });
            
            datosField.setAttribute('data-has-data', 'true');
            
        } else {
            // No importar
            datosField.value = '';
            datosField.removeAttribute('data-has-data');
        }
        
        actualizarResumen();
    }

    function cargarUsuarios(bdOrigen) {
        const contenedor = document.getElementById('listaUsuarios');
        contenedor.innerHTML = '<p>⏳ Cargando usuarios...</p>';
        
        fetch('ajax-datos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'obtener_usuarios',
                bd_origen: bdOrigen
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if(data.success && data.usuarios && data.usuarios.length > 0) {
                // ✅ FORMATO COMPLETO PARA USUARIOS (CON DETALLES)
                let html = '<div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: white;">';
                
                data.usuarios.forEach(usuario => {
                    const esAdmin = usuario.perfil.toLowerCase() === 'administrador';
                    const colorPerfil = esAdmin ? '#dc3545' : '#007bff';
                    const iconoPerfil = esAdmin ? '👑' : '👤';
                    const estadoColor = usuario.estado == 1 ? '#28a745' : '#6c757d';
                    
                    html += `
                        <div style="margin-bottom: 12px; padding: 10px; border: 1px solid #eee; border-radius: 6px; background: #f9f9f9;">
                            <label style="display: flex; align-items: flex-start; cursor: pointer;">
                                <input type="checkbox" name="usuarios_importar[]" value="${usuario.id}" 
                                       style="margin-right: 12px; margin-top: 4px;" onchange="actualizarResumen()">
                                <div style="flex: 1;">
                                    <!-- Nombre y perfil -->
                                    <div style="margin-bottom: 6px;">
                                        <strong style="font-size: 14px; color: #333;">
                                            ${iconoPerfil} ${usuario.nombre}
                                        </strong>
                                        <span style="color: ${colorPerfil}; font-size: 12px; font-weight: bold; margin-left: 8px; background: ${colorPerfil}20; padding: 2px 6px; border-radius: 3px;">
                                            ${usuario.perfil}
                                        </span>
                                    </div>
                                    
                                    <!-- Información principal -->
                                    <div style="font-size: 12px; color: #666; margin-bottom: 4px;">
                                        <strong>👤 Usuario:</strong> ${usuario.usuario} | 
                                        <strong style="color: ${estadoColor};">⚫ ${usuario.estado == 1 ? 'Activo' : 'Inactivo'}</strong>
                                    </div>
                                    
                                    <!-- Información adicional -->
                                    <div style="font-size: 11px; color: #999;">
                                        📅 Último login: ${usuario.ultimo_login} | 
                                        🏢 ${usuario.empresa}<br>
                                        📞 ${usuario.telefono} | 
                                        📧 Registrado: ${usuario.fecha_registro}
                                    </div>
                                </div>
                            </label>
                        </div>
                    `;
                });
                
                html += '</div>';
                html += `<div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                    <strong>📊 Total: ${data.usuarios.length} usuarios activos</strong>
                </div>`;
                
                contenedor.innerHTML = html;
                
            } else {
                contenedor.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #666;">
                        <p><strong>ℹ️ Sin usuarios</strong></p>
                        <p>No se encontraron usuarios activos en: <code>${bdOrigen}</code></p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error cargando usuarios:', error);
            contenedor.innerHTML = `
                <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
                    <p><strong>❌ Error cargando usuarios</strong></p>
                    <p>${error.message}</p>
                    <p style="font-size: 12px;">Verifica la conexión y el archivo ajax-datos.php</p>
                </div>
            `;
        });
    }

    function seleccionarTodosClientes() {
        const checkboxes = document.querySelectorAll('input[name="clientes_importar[]"]');
        checkboxes.forEach(cb => cb.checked = true);
        actualizarResumen();
    }

    function deseleccionarTodosClientes() {
        const checkboxes = document.querySelectorAll('input[name="clientes_importar[]"]');
        checkboxes.forEach(cb => cb.checked = false);
        actualizarResumen();
    }

    function seleccionarTodosUsuarios() {
        const checkboxes = document.querySelectorAll('input[name="usuarios_importar[]"]');
        checkboxes.forEach(cb => cb.checked = true);
        actualizarResumen();
    }

    function deseleccionarTodosUsuarios() {
        const checkboxes = document.querySelectorAll('input[name="usuarios_importar[]"]');
        checkboxes.forEach(cb => cb.checked = false);
        actualizarResumen();
    }

    function actualizarResumen() {
        // Contar usuarios seleccionados (mantener lógica actual)
        const usuariosSeleccionados = document.querySelectorAll('input[name="usuarios_importar[]"]:checked').length;
        
        // Obtener opción de clientes seleccionada
        const opcionClientes = document.querySelector('input[name="importar_clientes_opcion"]:checked');
        let clientesInfo = 'Ninguno';
        
        if (opcionClientes && opcionClientes.value !== 'ninguno') {
            const datosField = document.getElementById('clientesImportarData');
            if (datosField && datosField.value) {
                try {
                    const datos = JSON.parse(datosField.value);
                    if (datos.opcion === 'todos') {
                        clientesInfo = `Todos (${datos.total} clientes)`;
                    } else if (datos.opcion === 'solo_con_datos') {
                        clientesInfo = 'Solo con datos completos';
                    }
                } catch (e) {
                    clientesInfo = opcionClientes.value;
                }
            }
        }
        
        const sucursalOrigen = document.getElementById('sucursal_origen').value;
        const resumen = document.getElementById('resumenImportacion');
        const contenido = document.getElementById('contenidoResumen');
        
        if (usuariosSeleccionados > 0 || (opcionClientes && opcionClientes.value !== 'ninguno')) {
            let html = `<strong>📊 Datos a importar desde:</strong> ${sucursalOrigen}<br>`;
            
            html += `• <strong>Clientes:</strong> ${clientesInfo}<br>`;
            
            if (usuariosSeleccionados > 0) {
                html += `• <strong>Usuarios:</strong> ${usuariosSeleccionados} seleccionado(s)<br>`;
            }
            
            html += '<br><em>Estos datos se importarán después de crear la estructura básica de la sucursal.</em>';
            
            contenido.innerHTML = html;
            resumen.style.display = 'block';
        } else {
            resumen.style.display = 'none';
        }
    }

    // ✅ FUNCIÓN PHP MOVIDA AL LUGAR CORRECTO (DEBE IR FUERA DEL SCRIPT)
    // Esta función debe estar en PHP, no en JavaScript
</script>
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn" onclick="return confirmarInstalacion()">
                    🚀 Iniciar Instalación
                </button>
            </div>
            
        </form>
        
        <?php else: ?>
        
        <!-- ===== PROCESO DE INSTALACIÓN ===== -->
        <?php
        
        $errores = [];
        $pasos_completados = 0;
        $total_pasos = 10;
        
        // Datos del formulario
        $nombre_sucursal = trim($_POST['nombre_sucursal'] ?? '');
        $codigo_sucursal = strtoupper(trim($_POST['codigo_sucursal'] ?? ''));
        $bd_host = trim($_POST['bd_host'] ?? 'localhost');
        $bd_usuario = trim($_POST['bd_usuario'] ?? '');
        $bd_password = $_POST['bd_password'] ?? '';
        $bd_nombre = trim($_POST['bd_nombre'] ?? '');
        
        $verificar_central = isset($_POST['verificar_central']);
        $crear_usuario_admin = isset($_POST['crear_usuario_admin']);
        $sincronizar_categorias = isset($_POST['sincronizar_categorias']);
        $crear_archivo_conexion = isset($_POST['crear_archivo_conexion']);
        $registrar_en_central = isset($_POST['registrar_en_central']);
        
        // Validaciones
        if (empty($nombre_sucursal) || empty($codigo_sucursal) || empty($bd_usuario) || empty($bd_nombre)) {
            $errores[] = "Todos los campos obligatorios deben estar completos";
        }
        
        if (!empty($errores)) {
            echo '<div class="error">';
            echo '<h3>❌ Errores de validación:</h3><ul>';
            foreach ($errores as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul><p><a href="javascript:history.back()">← Volver</a></p></div>';
        } else {
            
            echo '<div class="step">';
            echo '<h2>⚡ Instalando Nueva Sucursal</h2>';
            echo '<p><strong>Sucursal:</strong> ' . htmlspecialchars($nombre_sucursal) . ' (' . $codigo_sucursal . ')</p>';
            echo '<p><strong>Nueva BD:</strong> ' . htmlspecialchars($bd_nombre) . '</p>';
            echo '</div>';
            
            echo '<div class="progress"><div class="progress-bar" id="progressBar" style="width: 0%"></div></div>';
            echo '<div id="pasoActual">Iniciando...</div>';
            
            // ===== PASO 1: VERIFICAR BD CENTRAL =====
            if ($verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 1/10: Verificando BD Central...";</script>';
                echo '<div class="step"><h3>🌐 Paso 1: Verificando BD Central</h3>';
                
                try {
                    require_once "../api-transferencias/conexion-central.php";
                    $dbCentral = ConexionCentral::conectar();
                    
                    $stmt = $dbCentral->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1");
                    $stmt->execute();
                    $resultado = $stmt->fetch();
                    
                    echo '<div class="success">✅ BD Central OK - Productos: ' . $resultado['total'] . '</div>';
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error BD Central: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error BD Central";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "10%";</script>';
                flush();
            }
            
            // ===== PASO 2: CONECTAR A MYSQL =====
            echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 2/10: Conectando a MySQL...";</script>';
            echo '<div class="step"><h3>💾 Paso 2: Conectando a MySQL</h3>';
            
            try {
                $dsn = "mysql:host={$bd_host};charset=utf8mb4";
                $pdo = new PDO($dsn, $bd_usuario, $bd_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="success">✅ Conexión MySQL exitosa</div>';
                $pasos_completados++;
            } catch (Exception $e) {
                echo '<div class="error">❌ Error MySQL: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errores[] = "Error MySQL";
            }
            echo '</div>';
            echo '<script>document.getElementById("progressBar").style.width = "20%";</script>';
            flush();
            
            // ===== PASO 3: CREAR BASE DE DATOS =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 3/10: Creando BD...";</script>';
                echo '<div class="step"><h3>🏗️ Paso 3: Creando Base de Datos</h3>';
                
                try {
                    // Verificar si existe
                    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                    $stmt->execute([$bd_nombre]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="warning">⚠️ BD "' . htmlspecialchars($bd_nombre) . '" ya existe</div>';
                    } else {
                        $pdo->exec("CREATE DATABASE `{$bd_nombre}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        echo '<div class="success">✅ BD "' . htmlspecialchars($bd_nombre) . '" creada</div>';
                    }
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error creando BD: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error creando BD";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "30%";</script>';
                flush();
            }
            
            // ===== PASO 4: CONECTAR A LA NUEVA BD =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 4/10: Conectando a nueva BD...";</script>';
                echo '<div class="step"><h3>🔗 Paso 4: Conectando a Nueva BD</h3>';
                
                try {
                    $dsn_nueva = "mysql:host={$bd_host};dbname={$bd_nombre};charset=utf8mb4";
                    $pdo_nueva = new PDO($dsn_nueva, $bd_usuario, $bd_password);
                    $pdo_nueva->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo '<div class="success">✅ Conectado a: ' . htmlspecialchars($bd_nombre) . '</div>';
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error conectando: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error conectando nueva BD";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "40%";</script>';
                flush();
            }
            
            // ===== PASO 5: CREAR ESTRUCTURA DE TABLAS =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 5/10: Creando tablas...";</script>';
                echo '<div class="step"><h3>🏗️ Paso 5: Creando Estructura</h3>';
                
                // SQL basado en tu estructura actual
                $tablas_sql = [
                    "categorias" => "
                    CREATE TABLE IF NOT EXISTS `categorias` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `categoria` text NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "clientes" => "
                    CREATE TABLE IF NOT EXISTS `clientes` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` text NOT NULL,
                      `documento` int(11) NOT NULL,
                      `email` text NOT NULL,
                      `telefono` text NOT NULL,
                      `direccion` text NOT NULL,
                      `fecha_nacimiento` date NOT NULL,
                      `compras` int(11) NOT NULL,
                      `ultima_compra` datetime NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "productos" => "
                    CREATE TABLE IF NOT EXISTS `productos` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_categoria` int(11) NOT NULL,
                      `parent_id` int(11) DEFAULT NULL,
                      `codigo` varchar(50) NOT NULL,
                      `codigo_maestro` varchar(50) DEFAULT NULL,
                      `descripcion` text NOT NULL,
                      `imagen` text NOT NULL,
                      `stock` int(11) NOT NULL,
                      `precio_venta` float NOT NULL,
                      `ventas` int(11) NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      `es_divisible` tinyint(1) DEFAULT 0,
                      `nombre_mitad` varchar(255) DEFAULT NULL,
                      `precio_mitad` decimal(10,2) DEFAULT NULL,
                      `nombre_tercio` varchar(255) DEFAULT NULL,
                      `precio_tercio` decimal(10,2) DEFAULT NULL,
                      `nombre_cuarto` varchar(255) DEFAULT NULL,
                      `precio_cuarto` decimal(10,2) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uk_codigo` (`codigo`),
                      KEY `idx_codigo_maestro` (`codigo_maestro`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "usuarios" => "
                    CREATE TABLE IF NOT EXISTS `usuarios` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` text NOT NULL,
                      `usuario` text NOT NULL,
                      `password` text NOT NULL,
                      `perfil` text NOT NULL,
                      `foto` text NOT NULL,
                      `estado` int(11) NOT NULL,
                      `ultimo_login` datetime NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      `empresa` text NOT NULL,
                      `telefono` text DEFAULT NULL,
                      `direccion` text DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "ventas" => "
                    CREATE TABLE IF NOT EXISTS `ventas` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo` int(11) NOT NULL,
                      `id_cliente` int(11) NOT NULL,
                      `id_vendedor` int(11) NOT NULL,
                      `productos` text NOT NULL,
                      `impuesto` float NOT NULL,
                      `descuento` int(11) NOT NULL DEFAULT 0,
                      `neto` float NOT NULL,
                      `total` float NOT NULL,
                      `detalle` text NOT NULL,
                      `metodo_pago` text NOT NULL,
                      `fecha_venta` datetime NOT NULL,
                      `id_vend_abono` int(11) NOT NULL,
                      `abono` float NOT NULL,
                      `fecha_abono` datetime NOT NULL,
                      `pago` text NOT NULL,
                      `Ult_abono` float NOT NULL,
                      `medio_pago` varchar(50) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "sucursal_local" => "
                    CREATE TABLE IF NOT EXISTS `sucursal_local` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo_sucursal` varchar(20) NOT NULL,
                      `nombre` varchar(255) NOT NULL,
                      `direccion` text DEFAULT NULL,
                      `telefono` varchar(50) DEFAULT NULL,
                      `email` varchar(255) DEFAULT NULL,
                      `url_base` varchar(255) NOT NULL,
                      `url_api` varchar(255) NOT NULL,
                      `es_principal` tinyint(1) DEFAULT 0,
                      `activo` tinyint(1) DEFAULT 1,
                      `registrada_en_central` tinyint(1) DEFAULT 0,
                      `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
                      `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;",
                    
                    "medios_pago" => "
                    CREATE TABLE IF NOT EXISTS `medios_pago` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` varchar(100) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;",
                    
                    "cotizaciones" => "
                    CREATE TABLE IF NOT EXISTS `cotizaciones` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo` int(11) NOT NULL,
                      `id_cliente` int(11) NOT NULL,
                      `id_vendedor` int(11) NOT NULL,
                      `productos` text NOT NULL,
                      `impuesto` float NOT NULL,
                      `descuento` int(11) NOT NULL DEFAULT 0,
                      `neto` float NOT NULL,
                      `total` float NOT NULL,
                      `detalle` text NOT NULL,
                      `metodo_pago` text NOT NULL,
                      `fecha_venta` datetime NOT NULL,
                      `id_vend_abono` int(11) NOT NULL,
                      `abono` float NOT NULL,
                      `fecha_abono` datetime NOT NULL,
                      `pago` text NOT NULL,
                      `Ult_abono` float NOT NULL,
                      `medio_pago` varchar(50) DEFAULT NULL,
                      `images` text DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "contabilidad" => "
                    CREATE TABLE IF NOT EXISTS `contabilidad` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_vendedor` int(11) NOT NULL,
                      `fecha` datetime NOT NULL,
                      `detalle` text NOT NULL,
                      `valor` varchar(100) NOT NULL,
                      `medio_pago` varchar(50) NOT NULL,
                      `forma_pago` varchar(50) DEFAULT NULL,
                      `factura` varchar(20) DEFAULT NULL,
                      `tipo` varchar(50) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;",
                    
                    "venta_productos" => "
                    CREATE TABLE IF NOT EXISTS `venta_productos` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_venta` int(11) NOT NULL,
                      `descripcion` varchar(255) NOT NULL,
                      `cantidad` int(11) NOT NULL,
                      `total` decimal(10,2) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `id_venta` (`id_venta`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;"
                ];
                
                $tablas_creadas = 0;
                foreach ($tablas_sql as $tabla => $sql) {
                    try {
                        $pdo_nueva->exec($sql);
                        echo "<p>✅ Tabla <strong>{$tabla}</strong> creada</p>";
                        $tablas_creadas++;
                    } catch (Exception $e) {
                        echo "<p>❌ Error tabla <strong>{$tabla}</strong>: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                
                if ($tablas_creadas > 0) {
                    echo '<div class="success">✅ Estructura creada: ' . $tablas_creadas . ' tablas</div>';
                    $pasos_completados++;
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "50%";</script>';
                flush();
            }
            
            // ===== PASO 6: CONFIGURAR SUCURSAL Y ACTUALIZAR PLANTILLA =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 6/10: Configurando sucursal y API...";</script>';
                echo '<div class="step"><h3>⚙️ Paso 6: Configurando Sucursal y API</h3>';
                
                try {
                    // ✅ CORREGIR LA GENERACIÓN DE URL
                    $url_actual = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
                    // Remover '/instalacion' del path si existe
                    $script_dir = dirname($_SERVER['PHP_SELF']);
                    if (strpos($script_dir, '/instalacion') !== false) {
                        $script_dir = str_replace('/instalacion', '', $script_dir);
                    }
                    $url_actual .= $script_dir;
                    
                    // ✅ URL DEL API CORREGIDA
                    $url_api = $url_actual . '/api-transferencias/';
                    
                    // Insertar configuración en la base de datos
                    $stmt = $pdo_nueva->prepare("
                        INSERT INTO sucursal_local 
                        (codigo_sucursal, nombre, url_base, url_api, activo, fecha_registro) 
                        VALUES (?, ?, ?, ?, 1, ?)
                    ");
                    $stmt->execute([$codigo_sucursal, $nombre_sucursal, $url_actual, $url_api, $FECHA_INSTALACION]);
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Sucursal configurada:</strong><br>';
                    echo '• Código: ' . $codigo_sucursal . '<br>';
                    echo '• Nombre: ' . htmlspecialchars($nombre_sucursal) . '<br>';
                    echo '• URL: ' . $url_actual . '<br>';
                    echo '• API: ' . $url_api . '<br>';
                    echo '</div>';
                    
                    // ✅ ACTUALIZAR PLANTILLA.PHP CON LA URL DEL API
                    echo '<div class="info">🔄 Actualizando archivo plantilla.php...</div>';
                    $resultadoPlantilla = actualizarUrlApiEnPlantilla($url_api);
                    
                    if ($resultadoPlantilla['success']) {
                        echo '<div class="success">✅ <strong>Plantilla actualizada:</strong> URL del API configurada correctamente</div>';
                    } else {
                        echo '<div class="warning">⚠️ <strong>Advertencia:</strong> ' . htmlspecialchars($resultadoPlantilla['message']) . '</div>';
                        echo '<div class="info">💡 La aplicación funcionará, pero deberás actualizar manualmente la URL en vistas/plantilla.php</div>';
                    }
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error configurando: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "60%";</script>';
                flush();
            }
            
            // ===== PASO 7: CREAR USUARIO ADMIN =====
            if (empty($errores) && $crear_usuario_admin) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 7/10: Creando usuario admin...";</script>';
                echo '<div class="step"><h3>👤 Paso 7: Creando Usuario Administrador</h3>';
                
                try {
                    // ✅ GENERAR USUARIO SIN GUIÓN BAJO NI CARACTERES ESPECIALES
                    $usuario_admin = 'admin' . strtolower($codigo_sucursal); // Sin guión bajo
                    $password_admin = 'admin123';
                    
                    // ✅ VERIFICAR QUE EL NOMBRE DE USUARIO SEA VÁLIDO (SIN GUIÓN BAJO)
                    if (strpos($usuario_admin, '_') !== false) {
                        // Si tiene guión bajo, reemplazar con código más simple
                        $usuario_admin = 'admin' . str_replace('_', '', strtolower($codigo_sucursal));
                    }
                    
                    // ✅ LIMITAR LONGITUD Y CARACTERES ESPECIALES
                    $usuario_admin = preg_replace('/[^a-z0-9]/', '', $usuario_admin);
                    $usuario_admin = substr($usuario_admin, 0, 15); // Máximo 15 caracteres
                    
                    echo '<div class="info">';
                    echo '🔍 <strong>Generando usuario:</strong> ' . htmlspecialchars($usuario_admin);
                    echo '</div>';
                    
                    // ✅ GENERAR HASH DE CONTRASEÑA USANDO EL MISMO MÉTODO DEL SISTEMA
                    $password_hash = crypt($password_admin, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    
                    // ✅ VERIFICAR QUE EL HASH SE GENERÓ CORRECTAMENTE
                    if (strlen($password_hash) < 30) {
                        throw new Exception("Error generando hash de contraseña - muy corto: " . strlen($password_hash));
                    }
                    
                    // ✅ VERIFICAR SI EL USUARIO YA EXISTE
                    $stmtVerificar = $pdo_nueva->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                    $stmtVerificar->execute([$usuario_admin]);
                    
                    if ($stmtVerificar->rowCount() > 0) {
                        echo '<div class="warning">';
                        echo '⚠️ <strong>Usuario ya existe:</strong> ' . htmlspecialchars($usuario_admin) . ' - Actualizando contraseña...';
                        echo '</div>';
                        
                        // Actualizar usuario existente
                        $stmt = $pdo_nueva->prepare("
                            UPDATE usuarios SET 
                                password = ?, 
                                estado = 1, 
                                ultimo_login = ?,
                                perfil = 'Administrador'
                            WHERE usuario = ?
                        ");
                        $resultado = $stmt->execute([$password_hash, $FECHA_INSTALACION, $usuario_admin]);
                        
                    } else {
                        // ✅ CREAR NUEVO USUARIO
                        $stmt = $pdo_nueva->prepare("
                            INSERT INTO usuarios 
                            (nombre, usuario, password, perfil, foto, estado, ultimo_login, empresa, telefono, direccion) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $resultado = $stmt->execute([
                            'Administrador ' . $nombre_sucursal,
                            $usuario_admin, // ✅ SIN GUIÓN BAJO
                            $password_hash,
                            'Administrador',
                            'vistas/img/usuarios/default/anonymous.png',
                            1,
                            $FECHA_INSTALACION,
                            $nombre_sucursal,
                            '',
                            ''
                        ]);
                    }
                    
                    if ($resultado) {
                        echo '<div class="success">';
                        echo '✅ <strong>Usuario administrador configurado:</strong><br>';
                        echo '• Usuario: <code style="background: #e9ecef; padding: 4px 8px; border-radius: 3px; font-weight: bold;">' . htmlspecialchars($usuario_admin) . '</code><br>';
                        echo '• Contraseña: <code style="background: #e9ecef; padding: 4px 8px; border-radius: 3px; font-weight: bold;">' . $password_admin . '</code><br>';
                        echo '• Perfil: <strong>Administrador</strong><br>';
                        echo '• Estado: <strong>ACTIVO</strong><br>';
                        echo '<em style="color: #856404;">⚠️ Cambiar contraseña después del primer login</em>';
                        echo '</div>';
                        
                        // ✅ PROBAR INMEDIATAMENTE EL LOGIN
                        echo '<h4 style="margin-top: 20px;">🧪 Verificando credenciales generadas:</h4>';
                        
                        // Buscar el usuario recién creado
                        $stmtPrueba = $pdo_nueva->prepare("SELECT id, usuario, password, estado, perfil FROM usuarios WHERE usuario = ?");
                        $stmtPrueba->execute([$usuario_admin]);
                        $usuarioPrueba = $stmtPrueba->fetch();
                        
                        if ($usuarioPrueba) {
                            
                            // Probar que el hash coincida
                            $hashPrueba = crypt($password_admin, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                            
                            echo '<div class="info">';
                            echo '🔍 <strong>Verificación de usuario:</strong><br>';
                            echo '• ID: ' . $usuarioPrueba['id'] . '<br>';
                            echo '• Usuario: <strong>' . htmlspecialchars($usuarioPrueba['usuario']) . '</strong><br>';
                            echo '• Perfil: <strong>' . htmlspecialchars($usuarioPrueba['perfil']) . '</strong><br>';
                            echo '• Estado: ' . ($usuarioPrueba['estado'] == 1 ? '<span style="color: #28a745;">✅ ACTIVO</span>' : '<span style="color: #dc3545;">❌ INACTIVO</span>') . '<br>';
                            echo '• Hash válido: ' . (strlen($usuarioPrueba['password']) > 30 ? '<span style="color: #28a745;">✅ SÍ</span>' : '<span style="color: #dc3545;">❌ NO</span>');
                            echo '</div>';
                            
                            if ($usuarioPrueba['password'] === $hashPrueba) {
                                echo '<div class="success">';
                                echo '✅ <strong>Verificación exitosa:</strong> El login debería funcionar correctamente';
                                echo '</div>';
                            } else {
                                echo '<div class="warning">';
                                echo '⚠️ <strong>Advertencia:</strong> Los hashes no coinciden exactamente<br>';
                                echo '<small>Esto puede ser normal debido a diferencias en el salt</small>';
                                echo '</div>';
                            }
                            
                        } else {
                            echo '<div class="error">';
                            echo '❌ <strong>Error:</strong> No se encontró el usuario recién creado';
                            echo '</div>';
                        }
                        
                        $pasos_completados++;
                        $usuario_admin_creado = true;
                        
                    } else {
                        throw new Exception("No se pudo crear/actualizar el usuario administrador");
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">';
                    echo '❌ <strong>Error creando usuario administrador:</strong><br>' . htmlspecialchars($e->getMessage());
                    echo '<br><br><strong>Detalles técnicos:</strong>';
                    echo '<ul>';
                    echo '<li>Usuario propuesto: ' . htmlspecialchars($usuario_admin ?? 'No definido') . '</li>';
                    echo '<li>Longitud hash: ' . (isset($password_hash) ? strlen($password_hash) : 'No generado') . '</li>';
                    echo '<li>Verificar permisos de BD y tabla usuarios</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "70%";</script>';
                flush();
            }
            
            // ===== PASO 8: SINCRONIZAR CATEGORÍAS =====
            if (empty($errores) && $sincronizar_categorias && $verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 8/10: Sincronizando categorías...";</script>';
                echo '<div class="step"><h3>📂 Paso 8: Sincronizando Categorías desde BD Central</h3>';
                
                try {
                    // Obtener categorías de BD central
                    $stmt_central = $dbCentral->prepare("SELECT id, categoria, fecha FROM categorias ORDER BY id");
                    $stmt_central->execute();
                    $categorias_central = $stmt_central->fetchAll();
                    
                    $categorias_sincronizadas = 0;
                    
                    foreach ($categorias_central as $categoria) {
                        try {
                            $stmt_local = $pdo_nueva->prepare("
                                INSERT INTO categorias (id, categoria, fecha) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE categoria = VALUES(categoria), fecha = VALUES(fecha)
                            ");
                            $stmt_local->execute([
                                $categoria['id'],
                                $categoria['categoria'],
                                $categoria['fecha']
                            ]);
                            $categorias_sincronizadas++;
                        } catch (Exception $e) {
                            // Ignorar errores de duplicados
                        }
                    }
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Categorías sincronizadas:</strong> ' . $categorias_sincronizadas;
                    echo '</div>';
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error sincronizando categorías: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "80%";</script>';
                flush();
            }

// ===== PASO 8.5: IMPORTAR DATOS DE OTRA SUCURSAL =====
if (empty($errores)) {
    echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 8.5/10: Importando datos seleccionados...";</script>';
    echo '<div class="step"><h3>📊 Paso 8.5: Importando Datos Seleccionados</h3>';
    
    $clientes_importados = 0;
    $usuarios_importados = 0;
    $bd_origen_datos = '';
    
    try {
        // ✅ OBTENER DATOS DEL FORMULARIO
        if (isset($_POST['sucursal_origen']) && !empty($_POST['sucursal_origen'])) {
            $bd_origen_datos = $_POST['sucursal_origen'];
            
            echo '<div class="info">';
            echo '🔄 <strong>Importando desde:</strong> ' . htmlspecialchars($bd_origen_datos);
            echo '</div>';
            
            // ✅ CONEXIÓN A LA BD ORIGEN (PARA LEER DATOS)
            try {
                $pdo_origen = new PDO("mysql:host=localhost;dbname={$bd_origen_datos}", 
                                    "epicosie_ricaurte", 
                                    "m5Wwg)~M{i~*kFr{");
                $pdo_origen->exec("set names utf8");
                $pdo_origen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="success">✅ Conectado a BD origen: ' . $bd_origen_datos . '</div>';
                
            } catch (Exception $e) {
                throw new Exception("Error conectando a BD origen ({$bd_origen_datos}): " . $e->getMessage());
            }
            
            // ✅ IMPORTAR CLIENTES SELECCIONADOS
            // ✅ VERIFICACIÓN MEJORADA PARA CLIENTES
            if ((isset($_POST['clientes_importar_data']) && !empty($_POST['clientes_importar_data'])) || 
                (isset($_POST['importar_clientes']) && $_POST['importar_clientes'] === 'on')) {
                
                echo '<div class="info">📥 <strong>Importando clientes...</strong></div>';
                
                try {
                    // Si hay datos JSON específicos, usarlos
                    if (isset($_POST['clientes_importar_data']) && !empty($_POST['clientes_importar_data'])) {
                        $datos_clientes = json_decode($_POST['clientes_importar_data'], true);
                    } else {
                        // Si solo está marcada la casilla, importar todos por defecto
                        $datos_clientes = ['opcion' => 'todos'];
                    }
                    
                    if ($datos_clientes && isset($datos_clientes['opcion'])) {
                        
                        echo '<div class="info">🔄 <strong>Opción seleccionada:</strong> ' . $datos_clientes['opcion'] . '</div>';
                        
                        if ($datos_clientes['opcion'] === 'todos') {
                            // ✅ IMPORTAR TODOS LOS CLIENTES
                            $stmt_clientes = $pdo_origen->prepare("
                                SELECT nombre, documento, email, telefono, direccion, 
                                    fecha_nacimiento as nacimiento, compras, ultima_compra, fecha
                                FROM clientes 
                                WHERE LENGTH(TRIM(COALESCE(nombre, ''))) > 0
                                ORDER BY nombre ASC
                            ");
                            $stmt_clientes->execute();
                            
                        } else if ($datos_clientes['opcion'] === 'solo_con_datos') {
                            // ✅ SOLO CLIENTES CON EMAIL O TELÉFONO
                            $stmt_clientes = $pdo_origen->prepare("
                                SELECT nombre, documento, email, telefono, direccion, 
                                    fecha_nacimiento as nacimiento, compras, ultima_compra, fecha
                                FROM clientes 
                                WHERE LENGTH(TRIM(COALESCE(nombre, ''))) > 0
                                AND (LENGTH(TRIM(COALESCE(email, ''))) > 0 OR LENGTH(TRIM(COALESCE(telefono, ''))) > 0)
                                ORDER BY nombre ASC
                            ");
                            $stmt_clientes->execute();
                        } else {
                            // No importar
                            echo '<div class="info">ℹ️ Opción "ninguno" seleccionada</div>';
                            $stmt_clientes = null;
                        }
                        
                        if ($stmt_clientes) {
                            $clientes_origen = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo '<div class="info">🔍 <strong>Clientes encontrados en origen:</strong> ' . count($clientes_origen) . '</div>';
                            
                            if (count($clientes_origen) > 0) {
                                // ✅ INSERTAR CLIENTES UNO POR UNO
                                $stmt_insert_cliente = $pdo_nueva->prepare("
                                    INSERT INTO clientes (nombre, documento, email, telefono, direccion, fecha_nacimiento, compras, ultima_compra, fecha)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                
                                $clientes_procesados = 0;
                                $clientes_con_error = 0;
                                
                                foreach ($clientes_origen as $cliente) {
                                    try {
                                        $stmt_insert_cliente->execute([
                                            trim($cliente['nombre']) ?: 'Sin nombre',
                                            $cliente['documento'] ?: 0,
                                            trim($cliente['email']) ?: '',
                                            trim($cliente['telefono']) ?: '',
                                            trim($cliente['direccion']) ?: '',
                                            $cliente['nacimiento'] ?: '0000-00-00',
                                            $cliente['compras'] ?: 0,
                                            $cliente['ultima_compra'] ?: '0000-00-00 00:00:00',
                                            $cliente['fecha'] ?: date('Y-m-d H:i:s')
                                        ]);
                                        $clientes_procesados++;
                                        
                                    } catch (Exception $e) {
                                        $clientes_con_error++;
                                        error_log("Error importando cliente {$cliente['nombre']}: " . $e->getMessage());
                                    }
                                }
                                
                                $clientes_importados = $clientes_procesados;
                                
                                echo '<div class="success">';
                                echo '✅ <strong>Clientes procesados:</strong><br>';
                                echo '• Importados exitosamente: ' . $clientes_procesados . '<br>';
                                if ($clientes_con_error > 0) {
                                    echo '• Con errores: ' . $clientes_con_error . '<br>';
                                }
                                echo '</div>';
                                
                            } else {
                                echo '<div class="warning">⚠️ No se encontraron clientes que coincidan con los criterios</div>';
                            }
                        }
                        
                    } else {
                        echo '<div class="warning">⚠️ Datos de importación de clientes inválidos</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error importando clientes: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    error_log("Error en importación de clientes: " . $e->getMessage());
                }
                
            } else {
                echo '<div class="info">ℹ️ <strong>Clientes:</strong> No se seleccionó importación</div>';
            }
            
            // ✅ IMPORTAR USUARIOS SELECCIONADOS (SIN GUIÓN BAJO)
            if (isset($_POST['usuarios_importar']) && is_array($_POST['usuarios_importar'])) {
                
                echo '<div class="info">👥 <strong>Importando usuarios seleccionados...</strong></div>';
                
                try {
                    $usuarios_ids = array_map('intval', $_POST['usuarios_importar']);
                    
                    if (!empty($usuarios_ids)) {
                        $placeholders = str_repeat('?,', count($usuarios_ids) - 1) . '?';
                        
                        $stmt_usuarios = $pdo_origen->prepare("
                            SELECT nombre, usuario, password, perfil, foto, estado, ultimo_login,
                                   empresa, telefono, direccion, fecha
                            FROM usuarios 
                            WHERE id IN ({$placeholders})
                            AND estado = 1
                        ");
                        $stmt_usuarios->execute($usuarios_ids);
                        $usuarios_origen = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo '<div class="info">🔍 <strong>Usuarios encontrados:</strong> ' . count($usuarios_origen) . '</div>';
                        
                        // ✅ INSERTAR USUARIOS SIN MODIFICAR NOMBRES DE USUARIO
                        $stmt_insert_usuario = $pdo_nueva->prepare("
                            INSERT INTO usuarios (nombre, usuario, password, perfil, foto, estado, ultimo_login, empresa, telefono, direccion, fecha)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $usuarios_procesados = 0;
                        $usuarios_con_error = 0;
                        
                        foreach ($usuarios_origen as $usuario) {
                            try {
                                // ✅ NO MODIFICAR EL NOMBRE DE USUARIO - MANTENER ORIGINAL
                                $usuario_original = trim($usuario['usuario']);
                                
                                // ✅ VERIFICAR SI EL USUARIO YA EXISTE
                                $stmt_verificar = $pdo_nueva->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                                $stmt_verificar->execute([$usuario_original]);
                                
                                if ($stmt_verificar->rowCount() > 0) {
                                    echo '<div class="warning">⚠️ Usuario <strong>' . htmlspecialchars($usuario_original) . '</strong> ya existe - omitido</div>';
                                    continue;
                                }
                                
                                // ✅ INSERTAR CON USUARIO ORIGINAL
                                $stmt_insert_usuario->execute([
                                    trim($usuario['nombre']) ?: 'Usuario sin nombre',
                                    $usuario_original, // ✅ SIN MODIFICACIONES
                                    $usuario['password'] ?: '',
                                    $usuario['perfil'] ?: 'Especial',
                                    $usuario['foto'] ?: 'vistas/img/usuarios/default/anonymous.png',
                                    1, // Activo
                                    $usuario['ultimo_login'] ?: '0000-00-00 00:00:00',
                                    $usuario['empresa'] ?: $nombre_sucursal,
                                    trim($usuario['telefono']) ?: '',
                                    trim($usuario['direccion']) ?: '',
                                    date('Y-m-d H:i:s')
                                ]);
                                
                                $usuarios_procesados++;
                                
                                echo '<div class="success">✅ Usuario importado: <strong>' . htmlspecialchars($usuario_original) . '</strong></div>';
                                
                            } catch (Exception $e) {
                                $usuarios_con_error++;
                                echo '<div class="error">❌ Error importando usuario ' . htmlspecialchars($usuario['nombre']) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                                error_log("Error importando usuario {$usuario['nombre']}: " . $e->getMessage());
                            }
                        }
                        
                        $usuarios_importados = $usuarios_procesados;
                        
                        echo '<div class="success">';
                        echo '✅ <strong>Usuarios procesados:</strong><br>';
                        echo '• Importados exitosamente: ' . $usuarios_procesados . '<br>';
                        if ($usuarios_con_error > 0) {
                            echo '• Con errores: ' . $usuarios_con_error . '<br>';
                        }
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error importando usuarios: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    error_log("Error en importación de usuarios: " . $e->getMessage());
                }
                
            } else {
                echo '<div class="info">ℹ️ <strong>Usuarios:</strong> No se seleccionaron usuarios para importar</div>';
            }
            
        } else {
            echo '<div class="info">ℹ️ <strong>Sin importación de datos:</strong> No se seleccionó sucursal origen</div>';
        }
        
        // ✅ RESUMEN DE IMPORTACIÓN
        echo '<div class="success">';
        echo '✅ <strong>Importación completada:</strong><br>';
        echo '• Clientes importados: ' . $clientes_importados . '<br>';
        echo '• Usuarios importados: ' . $usuarios_importados . '<br>';
        if (!empty($bd_origen_datos)) {
            echo '• Origen: ' . htmlspecialchars($bd_origen_datos);
        }
        echo '</div>';
        
        $pasos_completados++;
        
    } catch (Exception $e) {
        $errores[] = "Error en importación de datos: " . $e->getMessage();
        echo '<div class="error">';
        echo '❌ <strong>Error en importación:</strong><br>' . htmlspecialchars($e->getMessage());
        echo '</div>';
    }
    
    echo '</div>';
    echo '<script>document.getElementById("progressBar").style.width = "85%";</script>';
    flush();
}
            
            // ===== PASO 9: ALIMENTAR ARCHIVO CONEXION.PHP =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 9/10: Creando archivo de conexión...";</script>';
                echo '<div class="step"><h3>🔗 Paso 9: Alimentando archivo modelos/conexion.php</h3>';
                
                try {
                    // ✅ DEFINIR TODAS LAS VARIABLES NECESARIAS
                    $fecha_conexion = date('Y-m-d H:i:s');
                    $archivo_conexion = "../modelos/conexion.php";
                    
                    // ✅ CREAR BACKUP DEL ARCHIVO ORIGINAL SI EXISTE
                    if (file_exists($archivo_conexion)) {
                        $fecha_backup = date('Y-m-d_H-i-s');
                        $archivo_backup = "../modelos/conexion_backup_{$fecha_backup}.php";
                        
                        if (copy($archivo_conexion, $archivo_backup)) {
                            echo '<div class="info">';
                            echo '📋 <strong>Backup creado:</strong> conexion_backup_' . $fecha_backup . '.php';
                            echo '</div>';
                        }
                    }
                    
                    // ✅ DEFINIR EL CONTENIDO DEL ARCHIVO
                    $contenido_conexion = '<?php

            /*=============================================
            ARCHIVO DE CONEXIÓN GENERADO AUTOMÁTICAMENTE
            Sucursal: ' . $codigo_sucursal . ' (' . $nombre_sucursal . ')
            Fecha de creación: ' . $fecha_conexion . '
            Sistema: AdminV5 - danytrax/adminv5
            =============================================*/

            class Conexion {

                static public function conectar() {

                    try {
                        
                        $link = new PDO("mysql:host=' . $bd_host . ';dbname=' . $bd_nombre . ';charset=utf8mb4", 
                                    "' . $bd_usuario . '", 
                                    "' . $bd_password . '");

                        // Configuración de PDO
                        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        
                        // Configurar charset UTF-8
                        $link->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        return $link;

                    } catch (PDOException $e) {
                        
                        // Log del error
                        error_log("Error de conexión BD: " . $e->getMessage());
                        
                        // Mostrar error amigable en desarrollo
                        if (ini_get("display_errors")) {
                            die("Error de conexión a la base de datos: " . $e->getMessage());
                        } else {
                            die("Error de conexión a la base de datos. Contacte al administrador.");
                        }
                    }
                }
                
                /*=============================================
                INFORMACIÓN DE LA CONEXIÓN
                =============================================*/
                static public function getInfo() {
                    return [
                        "host" => "' . $bd_host . '",
                        "database" => "' . $bd_nombre . '",
                        "user" => "' . $bd_usuario . '",
                        "sucursal" => "' . $codigo_sucursal . '",
                        "nombre_sucursal" => "' . addslashes($nombre_sucursal) . '",
                        "fecha_creacion" => "' . $fecha_conexion . '"
                    ];
                }
            }

            ?>';

                    // ✅ VERIFICAR QUE EL DIRECTORIO EXISTE
                    $directorio_modelos = dirname($archivo_conexion);
                    if (!is_dir($directorio_modelos)) {
                        if (mkdir($directorio_modelos, 0755, true)) {
                            echo '<div class="info">📁 <strong>Directorio modelos creado:</strong> ' . $directorio_modelos . '</div>';
                        } else {
                            throw new Exception("No se pudo crear el directorio: " . $directorio_modelos);
                        }
                    }
                    
                    // ✅ ESCRIBIR EL ARCHIVO
                    if (file_put_contents($archivo_conexion, $contenido_conexion)) {
                        
                        echo '<div class="success">';
                        echo '✅ <strong>Archivo modelos/conexion.php creado exitosamente</strong><br>';
                        echo '• Host: ' . htmlspecialchars($bd_host) . '<br>';
                        echo '• Base de datos: <strong>' . htmlspecialchars($bd_nombre) . '</strong><br>';
                        echo '• Usuario: ' . htmlspecialchars($bd_usuario) . '<br>';
                        echo '• Sucursal: ' . htmlspecialchars($codigo_sucursal) . ' (' . htmlspecialchars($nombre_sucursal) . ')<br>';
                        echo '• Archivo: ' . $archivo_conexion;
                        echo '</div>';
                        
                        // ✅ VERIFICAR QUE EL ARCHIVO SE ESCRIBIÓ CORRECTAMENTE
                        if (file_exists($archivo_conexion) && filesize($archivo_conexion) > 0) {
                            $tamaño_archivo = filesize($archivo_conexion);
                            echo '<div class="success">';
                            echo '✅ <strong>Verificación exitosa:</strong> Archivo creado (' . $tamaño_archivo . ' bytes)';
                            echo '</div>';
                            
                            // ✅ PROBAR LA CONEXIÓN CON EL NUEVO ARCHIVO
                            try {
                                include $archivo_conexion;
                                $test_connection = Conexion::conectar();
                                
                                if ($test_connection) {
                                    echo '<div class="success">';
                                    echo '✅ <strong>Prueba de conexión exitosa:</strong> El archivo funciona correctamente';
                                    echo '</div>';
                                    $pasos_completados++;
                                }
                                
                            } catch (Exception $e) {
                                echo '<div class="warning">';
                                echo '⚠️ <strong>Advertencia:</strong> El archivo se creó pero la prueba de conexión falló: ' . htmlspecialchars($e->getMessage());
                                echo '</div>';
                            }
                            
                        } else {
                            throw new Exception("El archivo se creó pero está vacío o corrupto");
                        }
                        
                    } else {
                        throw new Exception("No se pudo escribir el archivo " . $archivo_conexion);
                    }
                    
                    // ✅ CREAR COPIA DE SEGURIDAD ESPECÍFICA DE LA SUCURSAL
                    $archivo_sucursal = "../modelos/conexion-{$codigo_sucursal}.php";
                    if (file_put_contents($archivo_sucursal, $contenido_conexion)) {
                        echo '<div class="info">';
                        echo '📁 <strong>Copia específica creada:</strong> conexion-' . htmlspecialchars($codigo_sucursal) . '.php';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Error creando archivo de conexión: " . $e->getMessage();
                    echo '<div class="error">';
                    echo '❌ <strong>Error creando modelos/conexion.php:</strong><br>' . htmlspecialchars($e->getMessage());
                    echo '<br><br><strong>Verificar:</strong>';
                    echo '<ul>';
                    echo '<li>Permisos de escritura en el directorio modelos/</li>';
                    echo '<li>Espacio suficiente en disco</li>';
                    echo '<li>Variables de BD correctas</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "90%";</script>';
                flush();
            }
            
            // ===== PASO 10: FINALIZACIÓN Y LOG =====
            echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 10/10: Finalizando instalación...";</script>';
            echo '<div class="step"><h3>🎉 Instalación Completada</h3>';

            // ✅ DEFINIR FECHA PARA LOG (SOLUCIONA EL WARNING)
            $fecha = date('Y-m-d H:i:s');
            $fecha_archivo = date('Y-m-d_H-i-s');

            // ✅ CALCULAR PROGRESO FINAL
            $progreso_porcentaje = ($pasos_completados / 10) * 100;
            $estado_instalacion = empty($errores) ? 'exitosa' : 'con_advertencias';

            if ($progreso_porcentaje >= 80) {
                echo '<div class="success">';
                echo '✅ <strong>¡Instalación Exitosa!</strong><br><br>';
                echo '<strong>Progreso:</strong> ' . $pasos_completados . '/10 pasos completados (' . round($progreso_porcentaje) . '%)<br>';
                echo '</div>';
            } else {
                echo '<div class="warning">';
                echo '⚠️ <strong>Instalación con advertencias</strong><br><br>';
                echo '<strong>Progreso:</strong> ' . $pasos_completados . '/10 pasos completados (' . round($progreso_porcentaje) . '%)<br>';
                echo '</div>';
            }

            // ✅ RESUMEN DE LA INSTALACIÓN
            echo '<div class="info">';
            echo '📋 <strong>Resumen de la instalación:</strong><br><br>';
            echo '• <strong>Sucursal:</strong> ' . htmlspecialchars($nombre_sucursal) . ' (' . htmlspecialchars($codigo_sucursal) . ')<br>';
            echo '• <strong>Base de Datos:</strong> ' . htmlspecialchars($bd_nombre) . '<br>';
            echo '• <strong>Usuario Admin:</strong> ' . (isset($usuario_admin_creado) && $usuario_admin_creado ? 'Creado' : 'No creado') . '<br>';
            echo '• <strong>Fecha:</strong> ' . $fecha . '<br>';

            if (!empty($errores)) {
                echo '<br><strong>⚠️ Advertencias encontradas:</strong><br>';
                foreach ($errores as $error) {
                    echo '• ' . htmlspecialchars($error) . '<br>';
                }
            }
            echo '</div>';

            // ✅ CREAR LOG DE INSTALACIÓN
            try {
                // Crear directorio de logs si no existe
                $logs_dir = __DIR__ . '/logs/';
                if (!is_dir($logs_dir)) {
                    mkdir($logs_dir, 0755, true);
                }
                
                // ✅ DATOS DEL LOG
                $log_data = [
                    'instalacion' => [
                        'fecha' => $fecha,
                        'sucursal' => [
                            'codigo' => $codigo_sucursal,
                            'nombre' => $nombre_sucursal,
                            'url' => 'https://' . $_SERVER['HTTP_HOST']
                        ],
                        'base_datos' => [
                            'host' => $bd_host,
                            'nombre' => $bd_nombre,
                            'usuario' => $bd_usuario
                        ],
                        'progreso' => [
                            'pasos_completados' => $pasos_completados,
                            'total_pasos' => 10,
                            'porcentaje' => $progreso_porcentaje,
                            'estado' => $estado_instalacion
                        ],
                        'importacion' => [
                            'clientes_importados' => $clientes_importados ?? 0,
                            'usuarios_importados' => $usuarios_importados ?? 0,
                            'bd_origen' => $bd_origen_datos ?? 'ninguna'
                        ],
                        'errores' => $errores,
                        'sistema' => [
                            'php_version' => phpversion(),
                            'servidor' => $_SERVER['HTTP_HOST'] ?? 'unknown',
                            'ip_instalador' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                        ]
                    ]
                ];
                
                $log_file = $logs_dir . "instalacion_{$codigo_sucursal}_{$fecha_archivo}.json";
                
                if (file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                    echo '<div class="success">';
                    echo '📄 <strong>Log de instalación creado:</strong> ' . basename($log_file);
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="warning">';
                echo '⚠️ <strong>No se pudo crear el log:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }

            // ✅ PRÓXIMOS PASOS
            echo '<div class="info">';
            echo '🚀 <strong>Próximos pasos:</strong><br><br>';
            echo '1. Acceder al sistema con las credenciales de administrador<br>';
            echo '2. Cambiar la contraseña por defecto<br>';
            echo '3. Configurar datos adicionales de la sucursal<br>';
            echo '4. Sincronizar productos desde el catálogo maestro<br>';
            echo '5. Entrenar al personal en el uso del sistema<br>';
            echo '</div>';

            // ✅ BOTONES DE ACCIÓN
            echo '<div style="text-align: center; margin: 30px 0;">';

            if ($progreso_porcentaje >= 80) {
                echo '<a href="../" class="btn" style="background: #28a745; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; margin: 0 10px;">';
                echo '🏠 Ir al Sistema';
                echo '</a>';
            }

            echo '<a href="logout.php" class="btn" style="background: #dc3545; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; margin: 0 10px;">';
            echo '🔓 Cerrar Sesión';
            echo '</a>';

            echo '</div>';

            echo '</div>';

            // ✅ INFORMACIÓN TÉCNICA
            echo '<div class="step">';
            echo '<h3>🔧 Información Técnica</h3>';
            echo '<div class="info">';
            echo '<strong>Versión del instalador:</strong> 1.0<br>';
            echo '<strong>PHP Version:</strong> ' . phpversion() . '<br>';
            echo '<strong>MySQL Version:</strong> ' . (isset($pdo_nueva) ? $pdo_nueva->getAttribute(PDO::ATTR_SERVER_VERSION) : 'No disponible') . '<br>';
            echo '<strong>Directorio de instalación:</strong> ' . __DIR__ . '<br>';
            echo '<strong>URL de acceso:</strong> https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/<br>';
            echo '</div>';
            echo '</div>';

            echo '<script>document.getElementById("progressBar").style.width = "100%";</script>';
            flush();
            
            // ===== RESULTADO FINAL =====
            echo '<script>document.getElementById("pasoActual").innerHTML = "¡Instalación completada!";</script>';
            
            $porcentaje_exito = ($pasos_completados / $total_pasos) * 100;
            
            echo '<div class="step">';
            echo '<h2>🎉 Instalación Completada</h2>';
            
            if ($porcentaje_exito >= 80) {
                echo '<div class="success">';
                echo '<h3>✅ ¡Instalación Exitosa!</h3>';
                echo '<p><strong>Progreso:</strong> ' . $pasos_completados . '/' . $total_pasos . ' pasos completados (' . round($porcentaje_exito) . '%)</p>';
                
                echo '<h4>📋 Resumen de la instalación:</h4>';
                echo '<ul>';
                echo '<li><strong>Sucursal:</strong> ' . htmlspecialchars($nombre_sucursal) . ' (' . $codigo_sucursal . ')</li>';
                echo '<li><strong>Base de Datos:</strong> ' . htmlspecialchars($bd_nombre) . '</li>';
                echo '<li><strong>Usuario Admin:</strong> ' . (isset($usuario_admin) ? $usuario_admin : 'No creado') . '</li>';
                echo '<li><strong>Fecha:</strong> ' . $FECHA_INSTALACION . '</li>';
                echo '</ul>';
                
                echo '<h4>🚀 Próximos pasos:</h4>';
                echo '<ol>';
                echo '<li>Acceder al sistema con las credenciales de administrador</li>';
                echo '<li>Cambiar la contraseña por defecto</li>';
                echo '<li>Configurar datos adicionales de la sucursal</li>';
                echo '<li>Sincronizar productos desde el catálogo maestro</li>';
                echo '<li>Entrenar al personal en el uso del sistema</li>';
                echo '</ol>';
                
                echo '<div style="text-align: center; margin-top: 20px;">';
                echo '<a href="index.php" class="btn" style="text-decoration: none;">🏠 Ir al Sistema</a>';
                echo '</div>';
                
                echo '</div>';
                
            } else {
                echo '<div class="warning">';
                echo '<h3>⚠️ Instalación Parcial</h3>';
                echo '<p>La instalación se completó con algunos errores. Progreso: ' . $pasos_completados . '/' . $total_pasos . ' pasos.</p>';
                echo '<p>Revise los mensajes anteriores y complete manualmente los pasos faltantes.</p>';
                echo '</div>';
            }
            
            if (!empty($errores)) {
                echo '<div class="error">';
                echo '<h4>❌ Errores encontrados:</h4>';
                echo '<ul>';
                foreach ($errores as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            echo '</div>';
            
            // ===== INFORMACIÓN TÉCNICA =====
            echo '<div class="step">';
            echo '<h3>🔧 Información Técnica</h3>';
            echo '<div class="info">';
            echo '<p><strong>Versión del instalador:</strong> ' . $INSTALADOR_VERSION . '</p>';
            echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
            echo '<p><strong>MySQL Version:</strong> ' . (isset($pdo_nueva) ? $pdo_nueva->getAttribute(PDO::ATTR_SERVER_VERSION) : 'N/A') . '</p>';
            echo '<p><strong>Directorio de instalación:</strong> ' . __DIR__ . '</p>';
            echo '<p><strong>URL de acceso:</strong> ' . 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/</p>';
            echo '</div>';
            echo '</div>';
            
            // ===== LOG DE INSTALACIÓN =====
            $log_instalacion = [
                'fecha' => $FECHA_INSTALACION,
                'version_instalador' => $INSTALADOR_VERSION,
                'sucursal' => [
                    'codigo' => $codigo_sucursal,
                    'nombre' => $nombre_sucursal
                ],
                'bd' => [
                    'host' => $bd_host,
                    'nombre' => $bd_nombre,
                    'usuario' => $bd_usuario
                ],
                'pasos_completados' => $pasos_completados,
                'total_pasos' => $total_pasos,
                'porcentaje_exito' => $porcentaje_exito,
                'errores' => $errores,
                'importacion' => [
                    'clientes_importados' => $clientes_importados ?? 0,
                    'usuarios_importados' => $usuarios_importados ?? 0,
                    'bd_origen' => $bd_origen_datos ?? 'ninguna'
                ],
                'sistema' => [
                    'php_version' => phpversion(),
                    'servidor' => $_SERVER['HTTP_HOST'] ?? 'unknown',
                    'ip_instalador' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            ];
            
            // ✅ GUARDAR LOG CORRECTAMENTE
            try {
                $fecha = date('Y-m-d H:i:s');
                $fecha_archivo = date('Y-m-d_H-i-s');
                
                // Crear directorio de logs si no existe
                $logs_dir = __DIR__ . '/logs/';
                if (!is_dir($logs_dir)) {
                    mkdir($logs_dir, 0755, true);
                }

                $log_file = $logs_dir . "instalacion_{$codigo_sucursal}_{$fecha_archivo}.json";
                
                // ✅ ESCRIBIR EL LOG CORRECTAMENTE
                if (file_put_contents($log_file, json_encode($log_instalacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                    echo '<div class="info">';
                    echo '📄 <strong>Log de instalación guardado:</strong> ' . basename($log_file);
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                // Ignorar errores de log - no afectan la instalación
                error_log("Error guardando log de instalación: " . $e->getMessage());
            }
        }
        
        ?>
        
        <?php endif; ?>
        
    </div>
</div>

</body>
</html>