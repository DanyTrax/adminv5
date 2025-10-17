<?php
// ===================================================================
// INSTALADOR DE BASE DE DATOS LOCAL PARA SUCURSALES
// danytrax/adminv5 - Configuración de derivaciones
// ===================================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

$INSTALADOR_VERSION = "1.0";
$FECHA_INSTALACION = date('Y-m-d H:i:s');

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

            function cargarSucursalesDisponibles() {
                // Esta función podría hacer una llamada AJAX para obtener sucursales disponibles
                // Por ahora, agregar manualmente las conocidas
                const select = document.getElementById('sucursal_origen');
                
                // Agregar opciones dinámicamente si hay más sucursales
                // En el futuro esto podría venir de una consulta a BD central
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
                contenedor.innerHTML = '<p>⏳ Cargando clientes...</p>';
                
                // Hacer llamada AJAX para obtener clientes
                fetch('ajax-instalador-datos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: 'obtener_clientes',
                        bd_origen: bdOrigen
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success && data.clientes.length > 0) {
                        let html = '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
                        
                        data.clientes.forEach(cliente => {
                            html += `
                                <div style="margin-bottom: 8px;">
                                    <label style="display: flex; align-items: center;">
                                        <input type="checkbox" name="clientes_importar[]" value="${cliente.id}" style="margin-right: 10px;">
                                        <div>
                                            <strong>${cliente.nombre}</strong><br>
                                            <small>Doc: ${cliente.documento} | Email: ${cliente.email} | Tel: ${cliente.telefono}</small>
                                        </div>
                                    </label>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        html += `<p style="margin-top: 10px;"><strong>Total clientes disponibles:</strong> ${data.clientes.length}</p>`;
                        contenedor.innerHTML = html;
                    } else {
                        contenedor.innerHTML = '<p><em>No se encontraron clientes en la sucursal seleccionada</em></p>';
                    }
                })
                .catch(error => {
                    contenedor.innerHTML = '<p style="color: red;"><em>Error cargando clientes: ' + error.message + '</em></p>';
                });
            }

            function cargarUsuarios(bdOrigen) {
                const contenedor = document.getElementById('listaUsuarios');
                contenedor.innerHTML = '<p>⏳ Cargando usuarios...</p>';
                
                // Hacer llamada AJAX para obtener usuarios
                fetch('ajax-instalador-datos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: 'obtener_usuarios',
                        bd_origen: bdOrigen
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success && data.usuarios.length > 0) {
                        let html = '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
                        
                        data.usuarios.forEach(usuario => {
                            const esAdmin = usuario.perfil.toLowerCase() === 'administrador';
                            const colorPerfil = esAdmin ? '#dc3545' : '#007bff';
                            
                            html += `
                                <div style="margin-bottom: 8px;">
                                    <label style="display: flex; align-items: center;">
                                        <input type="checkbox" name="usuarios_importar[]" value="${usuario.id}" style="margin-right: 10px;">
                                        <div>
                                            <strong>${usuario.nombre}</strong> <span style="color: ${colorPerfil}; font-weight: bold;">(${usuario.perfil})</span><br>
                                            <small>Usuario: ${usuario.usuario} | Estado: ${usuario.estado == 1 ? 'Activo' : 'Inactivo'} | Último login: ${usuario.ultimo_login || 'Nunca'}</small>
                                        </div>
                                    </label>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        html += `<p style="margin-top: 10px;"><strong>Total usuarios disponibles:</strong> ${data.usuarios.length}</p>`;
                        contenedor.innerHTML = html;
                    } else {
                        contenedor.innerHTML = '<p><em>No se encontraron usuarios en la sucursal seleccionada</em></p>';
                    }
                })
                .catch(error => {
                    contenedor.innerHTML = '<p style="color: red;"><em>Error cargando usuarios: ' + error.message + '</em></p>';
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
                const clientesSeleccionados = document.querySelectorAll('input[name="clientes_importar[]"]:checked').length;
                const usuariosSeleccionados = document.querySelectorAll('input[name="usuarios_importar[]"]:checked').length;
                const sucursalOrigen = document.getElementById('sucursal_origen').value;
                
                const resumen = document.getElementById('resumenImportacion');
                const contenido = document.getElementById('contenidoResumen');
                
                if(clientesSeleccionados > 0 || usuariosSeleccionados > 0) {
                    let html = `<strong>📊 Datos a importar desde:</strong> ${sucursalOrigen}<br>`;
                    
                    if(clientesSeleccionados > 0) {
                        html += `• <strong>${clientesSeleccionados}</strong> cliente(s) seleccionado(s)<br>`;
                    }
                    
                    if(usuariosSeleccionados > 0) {
                        html += `• <strong>${usuariosSeleccionados}</strong> usuario(s) seleccionado(s)<br>`;
                    }
                    
                    html += '<br><em>Estos datos se importarán después de crear la estructura básica de la sucursal.</em>';
                    
                    contenido.innerHTML = html;
                    resumen.style.display = 'block';
                } else {
                    resumen.style.display = 'none';
                }
            }
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
                    require_once "api-transferencias/conexion-central.php";
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
            
            // ===== PASO 6: CONFIGURAR SUCURSAL =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 6/10: Configurando sucursal...";</script>';
                echo '<div class="step"><h3>⚙️ Paso 6: Configurando Sucursal</h3>';
                
                try {
                    $url_actual = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    $url_api = $url_actual . '/api-transferencias/';
                    
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
                    echo '• API: ' . $url_api;
                    echo '</div>';
                    
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
                    // ✅ GENERAR USUARIO SIN CARACTERES ESPECIALES
                    $usuario_admin = 'admin' . strtolower($codigo_sucursal);  // Sin guión bajo
                    $password_admin = 'admin123';
                    
                    // ✅ GENERAR HASH DE CONTRASEÑA USANDO EL MISMO MÉTODO DEL SISTEMA
                    $password_hash = crypt($password_admin, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    
                    // ✅ VERIFICAR QUE EL HASH SE GENERÓ CORRECTAMENTE
                    if (strlen($password_hash) < 30) {
                        throw new Exception("Error generando hash de contraseña - muy corto");
                    }
                    
                    // ✅ VERIFICAR SI EL USUARIO YA EXISTE
                    $stmtVerificar = $pdo_nueva->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                    $stmtVerificar->execute([$usuario_admin]);
                    
                    if ($stmtVerificar->rowCount() > 0) {
                        echo '<div class="warning">';
                        echo '⚠️ <strong>Usuario ya existe:</strong> ' . $usuario_admin . ' - Actualizando contraseña...';
                        echo '</div>';
                        
                        // Actualizar usuario existente
                        $stmt = $pdo_nueva->prepare("
                            UPDATE usuarios SET 
                                password = ?, 
                                estado = 1, 
                                ultimo_login = ? 
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
                            $usuario_admin,
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
                        echo '• Usuario: <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">' . $usuario_admin . '</code><br>';
                        echo '• Contraseña: <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">' . $password_admin . '</code><br>';
                        echo '• Hash generado: <code style="font-size: 11px;">' . substr($password_hash, 0, 40) . '...</code><br>';
                        echo '<em>⚠️ Cambiar contraseña después del primer login</em>';
                        echo '</div>';
                        
                        // ✅ PROBAR INMEDIATAMENTE EL LOGIN
                        echo '<h4>🧪 Verificando credenciales generadas:</h4>';
                        
                        // Buscar el usuario recién creado
                        $stmtPrueba = $pdo_nueva->prepare("SELECT id, usuario, password, estado FROM usuarios WHERE usuario = ?");
                        $stmtPrueba->execute([$usuario_admin]);
                        $usuarioPrueba = $stmtPrueba->fetch();
                        
                        if ($usuarioPrueba) {
                            
                            // Probar que el hash coincida
                            $hashPrueba = crypt($password_admin, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                            
                            if ($usuarioPrueba['password'] === $hashPrueba) {
                                echo '<div class="success">';
                                echo '✅ <strong>Verificación exitosa:</strong> El login debería funcionar correctamente<br>';
                                echo '• Usuario encontrado: ID ' . $usuarioPrueba['id'] . '<br>';
                                echo '• Estado: ' . ($usuarioPrueba['estado'] == 1 ? 'ACTIVO' : 'INACTIVO') . '<br>';
                                echo '• Hash coincide: SÍ';
                                echo '</div>';
                            } else {
                                echo '<div class="warning">';
                                echo '⚠️ <strong>Advertencia:</strong> Los hashes no coinciden exactamente<br>';
                                echo '• Hash en BD: ' . substr($usuarioPrueba['password'], 0, 30) . '...<br>';
                                echo '• Hash prueba: ' . substr($hashPrueba, 0, 30) . '...<br>';
                                echo '<em>Puede requerir verificación manual</em>';
                                echo '</div>';
                            }
                            
                        } else {
                            echo '<div class="error">';
                            echo '❌ <strong>Error:</strong> No se encontró el usuario recién creado';
                            echo '</div>';
                        }
                        
                        $pasos_completados++;
                        
                    } else {
                        throw new Exception("No se pudo crear/actualizar el usuario administrador");
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">';
                    echo '❌ <strong>Error creando usuario:</strong><br>' . htmlspecialchars($e->getMessage());
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
            if (empty($errores) && isset($_POST['habilitar_importacion'])) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 8.5/10: Importando datos de otra sucursal...";</script>';
                echo '<div class="step"><h3>📊 Paso 8.5: Importando Datos Seleccionados</h3>';
                
                $sucursal_origen = $_POST['sucursal_origen'] ?? '';
                $importar_clientes = isset($_POST['importar_clientes']);
                $importar_usuarios = isset($_POST['importar_usuarios']);
                
                if (!empty($sucursal_origen) && ($importar_clientes || $importar_usuarios)) {
                    
                    try {
                        // Conectar a BD origen
                        $pdo_origen = new PDO(
                            "mysql:host=localhost;dbname={$sucursal_origen};charset=utf8mb4",
                            "epicosie_ricaurte", 
                            "m5Wwg)~M{i~*kFr{",
                            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                        );
                        
                        $clientes_importados = 0;
                        $usuarios_importados = 0;
                        
                        // Importar clientes seleccionados
                        if ($importar_clientes && isset($_POST['clientes_importar']) && is_array($_POST['clientes_importar'])) {
                            
                            foreach ($_POST['clientes_importar'] as $cliente_id) {
                                
                                try {
                                    // Obtener cliente de BD origen
                                    $stmt_origen = $pdo_origen->prepare("SELECT * FROM clientes WHERE id = ?");
                                    $stmt_origen->execute([$cliente_id]);
                                    $cliente = $stmt_origen->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($cliente) {
                                        // Verificar si ya existe en BD destino
                                        $stmt_existe = $pdo_nueva->prepare("SELECT id FROM clientes WHERE documento = ?");
                                        $stmt_existe->execute([$cliente['documento']]);
                                        
                                        if ($stmt_existe->rowCount() === 0) {
                                            // Insertar en BD nueva (sin ID para que se auto-genere)
                                            $stmt_insertar = $pdo_nueva->prepare("
                                                INSERT INTO clientes (nombre, documento, email, telefono, direccion, fecha_nacimiento, compras, ultima_compra, fecha) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                                            ");
                                            
                                            $stmt_insertar->execute([
                                                $cliente['nombre'],
                                                $cliente['documento'],
                                                $cliente['email'],
                                                $cliente['telefono'], 
                                                $cliente['direccion'],
                                                $cliente['fecha_nacimiento'],
                                                $cliente['compras'],
                                                $cliente['ultima_compra'],
                                                $cliente['fecha']
                                            ]);
                                            
                                            $clientes_importados++;
                                        }
                                    }
                                    
                                } catch (Exception $e) {
                                    error_log("Error importando cliente {$cliente_id}: " . $e->getMessage());
                                }
                            }
                        }
                        
                        // Importar usuarios seleccionados
                        if ($importar_usuarios && isset($_POST['usuarios_importar']) && is_array($_POST['usuarios_importar'])) {
                            
                            foreach ($_POST['usuarios_importar'] as $usuario_id) {
                                
                                try {
                                    // Obtener usuario de BD origen
                                    $stmt_origen = $pdo_origen->prepare("SELECT * FROM usuarios WHERE id = ?");
                                    $stmt_origen->execute([$usuario_id]);
                                    $usuario = $stmt_origen->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($usuario) {
                                        // Verificar si ya existe en BD destino
                                        $stmt_existe = $pdo_nueva->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                                        $stmt_existe->execute([$usuario['usuario']]);
                                        
                                        if ($stmt_existe->rowCount() === 0) {
                                            // Insertar en BD nueva (sin ID para que se auto-genere)
                                            $stmt_insertar = $pdo_nueva->prepare("
                                                INSERT INTO usuarios (nombre, usuario, password, perfil, foto, estado, ultimo_login, fecha, empresa, telefono, direccion) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                            ");
                                            
                                            $stmt_insertar->execute([
                                                $usuario['nombre'],
                                                $usuario['usuario'],
                                                $usuario['password'], // Mantener password original
                                                $usuario['perfil'],
                                                $usuario['foto'],
                                                $usuario['estado'],
                                                $usuario['ultimo_login'],
                                                $usuario['fecha'],
                                                $usuario['empresa'],
                                                $usuario['telefono'],
                                                $usuario['direccion']
                                            ]);
                                            
                                            $usuarios_importados++;
                                        }
                                    }
                                    
                                } catch (Exception $e) {
                                    error_log("Error importando usuario {$usuario_id}: " . $e->getMessage());
                                }
                            }
                        }
                        
                        echo '<div class="success">';
                        echo '✅ <strong>Importación completada:</strong><br>';
                        echo "• Clientes importados: <strong>{$clientes_importados}</strong><br>";
                        echo "• Usuarios importados: <strong>{$usuarios_importados}</strong><br>";
                        echo "• Origen: <strong>{$sucursal_origen}</strong>";
                        echo '</div>';
                        
                        $pasos_completados++;
                        
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Error en importación: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    
                } else {
                    echo '<div class="info">ℹ️ Importación omitida - no se seleccionaron datos para importar</div>';
                }
                
                echo '</div>';
                flush();
            }
            
        // ===== PASO 9: CREAR/ACTUALIZAR ARCHIVO DE CONEXIÓN =====
        if (empty($errores) && $crear_archivo_conexion) {
            echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 9/10: Actualizando modelos/conexion.php...";</script>';
            echo '<div class="step"><h3>🔗 Paso 9: Alimentando archivo modelos/conexion.php</h3>';
            
            try {
                
                // ✅ RUTA DEL ARCHIVO A ALIMENTAR
                $archivo_conexion = "modelos/conexion.php";
                
                // ✅ CREAR RESPALDO DEL ARCHIVO ORIGINAL
                if(file_exists($archivo_conexion)) {
                    $backup_file = "modelos/conexion-backup-" . date('Y-m-d-H-i-s') . ".php";
                    if(copy($archivo_conexion, $backup_file)) {
                        echo '<div class="info">';
                        echo '📋 <strong>Respaldo creado:</strong> ' . $backup_file;
                        echo '</div>';
                    }
                }
                
                // ✅ GENERAR CONTENIDO DEL ARCHIVO CONEXION.PHP ALIMENTADO
                $contenido_conexion = '<?php
        /*=============================================
        CONEXIÓN BASE DE DATOS - SUCURSAL ' . $codigo_sucursal . '
        Alimentado automáticamente por el instalador
        Fecha: ' . $FECHA_INSTALACION . '
        =============================================*/

        class Conexion{

            static public function conectar(){

                $link = new PDO("mysql:host=' . $bd_host . ';dbname=' . $bd_nombre . '",
                                "' . $bd_usuario . '",
                                "' . str_replace('"', '\\"', $bd_password) . '");

                $link->exec("set names utf8");

                return $link;

            }

            /*=============================================
            INFORMACIÓN DE LA SUCURSAL (OPCIONAL)
            =============================================*/
            static public function obtenerInfoSucursal(){
                return array(
                    "codigo" => "' . $codigo_sucursal . '",
                    "nombre" => "' . addslashes($nombre_sucursal) . '",
                    "bd_nombre" => "' . $bd_nombre . '",
                    "fecha_instalacion" => "' . $FECHA_INSTALACION . '"
                );
            }

        }
        ?>';
                
                // ✅ ESCRIBIR EL ARCHIVO ALIMENTADO
                if(file_put_contents($archivo_conexion, $contenido_conexion)) {
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Archivo modelos/conexion.php alimentado exitosamente</strong><br>';
                    echo '• Host: ' . $bd_host . '<br>';
                    echo '• Base de datos: <strong>' . $bd_nombre . '</strong><br>';
                    echo '• Usuario: ' . $bd_usuario . '<br>';
                    echo '• Sucursal: ' . $codigo_sucursal . ' (' . htmlspecialchars($nombre_sucursal) . ')';
                    echo '</div>';
                    
                    // ✅ VERIFICAR QUE EL ARCHIVO SE ESCRIBIÓ CORRECTAMENTE
                    if(file_exists($archivo_conexion) && filesize($archivo_conexion) > 0) {
                        echo '<div class="success">';
                        echo '✅ <strong>Verificación:</strong> Archivo creado correctamente (' . filesize($archivo_conexion) . ' bytes)';
                        echo '</div>';
                    } else {
                        throw new Exception("El archivo se creó pero parece estar vacío o corrupto");
                    }
                    
                } else {
                    throw new Exception("No se pudo escribir el archivo modelos/conexion.php");
                }
                
                // ✅ TAMBIÉN CREAR UNA COPIA ESPECÍFICA DE LA SUCURSAL
                $archivo_sucursal = "modelos/conexion-sucursal-{$codigo_sucursal}.php";
                if(file_put_contents($archivo_sucursal, $contenido_conexion)) {
                    echo '<div class="info">';
                    echo '📁 <strong>Copia específica creada:</strong> ' . $archivo_sucursal;
                    echo '</div>';
                }
                
                // ✅ MOSTRAR EL CÓDIGO GENERADO PARA VERIFICACIÓN
                echo '<div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 15px 0;">';
                echo '<h4>📋 Contenido generado en modelos/conexion.php:</h4>';
                echo '<textarea readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; background: white; border: 1px solid #ccc; padding: 10px;">';
                echo htmlspecialchars($contenido_conexion);
                echo '</textarea>';
                echo '</div>';
                
                // ✅ PROBAR LA CONEXIÓN INMEDIATAMENTE
                echo '<h4>🧪 Probando la conexión generada:</h4>';
                
                try {
                    // Cargar el archivo recién creado
                    require_once $archivo_conexion;
                    
                    // Intentar conectar
                    $conexion_test = Conexion::conectar();
                    
                    // Probar consulta
                    $stmt = $conexion_test->query("SELECT DATABASE() as bd_actual, COUNT(*) as total_usuarios FROM usuarios");
                    $resultado = $stmt->fetch();
                    
                    echo '<div class="success">';
                    echo '✅ <strong>¡Conexión exitosa!</strong><br>';
                    echo '• Base de datos conectada: <strong>' . $resultado['bd_actual'] . '</strong><br>';
                    echo '• Usuarios en la BD: <strong>' . $resultado['total_usuarios'] . '</strong><br>';
                    echo '<em>El sistema está listo para funcionar</em>';
                    echo '</div>';
                    
                } catch(Exception $e) {
                    echo '<div class="error">';
                    echo '❌ <strong>Error probando conexión:</strong><br>' . htmlspecialchars($e->getMessage());
                    echo '<br><em>Revise los datos de conexión ingresados</em>';
                    echo '</div>';
                }
                
                $pasos_completados++;
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '❌ <strong>Error alimentando modelos/conexion.php:</strong><br>' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            echo '</div>';
            echo '<script>document.getElementById("progressBar").style.width = "90%";</script>';
            flush();
        }
            
            // ===== PASO 10: REGISTRAR EN BD CENTRAL (OPCIONAL) =====
            if (empty($errores) && $registrar_en_central && $verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 10/10: Registrando en BD Central...";</script>';
                echo '<div class="step"><h3>🌐 Paso 10: Registrando Sucursal en BD Central</h3>';
                
                try {
                    $url_actual = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    
                    // Verificar si ya existe
                    $stmt_verificar = $dbCentral->prepare("
                        SELECT id FROM sucursales WHERE codigo_sucursal = ?
                    ");
                    $stmt_verificar->execute([$codigo_sucursal]);
                    
                    if ($stmt_verificar->rowCount() > 0) {
                        echo '<div class="warning">⚠️ Sucursal ya registrada en BD Central</div>';
                    } else {
                        // Insertar nueva sucursal
                        $stmt_insertar = $dbCentral->prepare("
                            INSERT INTO sucursales 
                            (codigo_sucursal, nombre, direccion, url_base, url_api, activo, fecha_creacion) 
                            VALUES (?, ?, ?, ?, ?, 1, ?)
                        ");
                        $stmt_insertar->execute([
                            $codigo_sucursal,
                            $nombre_sucursal,
                            'Dirección por definir',
                            $url_actual,
                            $url_actual . '/api-transferencias/',
                            $FECHA_INSTALACION
                        ]);
                        
                        echo '<div class="success">';
                        echo '✅ <strong>Sucursal registrada en BD Central</strong><br>';
                        echo '• Código: ' . $codigo_sucursal . '<br>';
                        echo '• URL: ' . $url_actual;
                        echo '</div>';
                    }
                    
                    // Actualizar estado local
                    $stmt_local = $pdo_nueva->prepare("
                        UPDATE sucursal_local 
                        SET registrada_en_central = 1 
                        WHERE codigo_sucursal = ?
                    ");
                    $stmt_local->execute([$codigo_sucursal]);
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error registrando en central: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "100%";</script>';
                flush();
            }
            
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
                'errores' => $errores
            ];
            
            // Guardar log
            try {
                file_put_contents(
                    "logs/instalacion_{$codigo_sucursal}_{$FECHA_INSTALACION}.json",
                    json_encode($log_instalacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            } catch (Exception $e) {
                // Ignorar errores de log
            }
        }
        
        ?>
        
        <?php endif; ?>
        
    </div>
</div>

</body>
</html>