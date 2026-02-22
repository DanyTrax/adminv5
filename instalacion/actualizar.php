<?php
/**
 * Panel de Actualización - Verifica conexiones y ejecuta migraciones SQL
 * Requiere login del instalador
 */
session_start();

if (!isset($_SESSION['instalacion_logueado']) || $_SESSION['instalacion_logueado'] !== true) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['instalacion_tiempo']) || (time() - $_SESSION['instalacion_tiempo']) > 3600) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$_SESSION['instalacion_tiempo'] = time();

// SQL para CENTRAL (epicosie_central) - Todas las migraciones usadas en instalación y operación
$SQL_CENTRAL = [
    'trazabilidad-solicitud-despacho' => 'instalacion/sql/trazabilidad-solicitud-despacho.sql',
    'agregar-prefijo-categorias' => 'agregar-prefijo-categorias.sql'
];

// SQL para LOCAL (cada sucursal) - Todas las migraciones usadas en instalación y operación
$SQL_LOCAL = [
    'crear-abonos-historial' => 'instalacion/sql/crear-abonos-historial.sql',
    'agregar-columnas-bd' => 'instalacion/sql/agregar-columnas-bd.sql',
    'crear-tablas-trazabilidad' => 'instalacion/sql/crear-tablas-trazabilidad.sql'
];

// Procesar solicitud AJAX
if (isset($_POST['accion'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $tipo = $_POST['tipo'] ?? ''; // 'central' o 'local'
    $accion = $_POST['accion']; // 'verificar', 'actualizar', 'verificar_archivos'
    
    try {
        // Verificar todos los archivos SQL del proyecto
        if ($accion === 'verificar_archivos') {
            $archivos = [];
            $baseDir = dirname(__DIR__);
            foreach ($SQL_CENTRAL as $nombre => $ruta) {
                $rutaCompleta = $baseDir . '/' . $ruta;
                $archivos[] = [
                    'nombre' => $nombre,
                    'ruta' => $ruta,
                    'tipo' => 'central',
                    'existe' => file_exists($rutaCompleta),
                    'tamano' => file_exists($rutaCompleta) ? filesize($rutaCompleta) : 0
                ];
            }
            foreach ($SQL_LOCAL as $nombre => $ruta) {
                $rutaCompleta = $baseDir . '/' . $ruta;
                $archivos[] = [
                    'nombre' => $nombre,
                    'ruta' => $ruta,
                    'tipo' => 'local',
                    'existe' => file_exists($rutaCompleta),
                    'tamano' => file_exists($rutaCompleta) ? filesize($rutaCompleta) : 0
                ];
            }
            // Escanear también instalacion/sql/ por si hay más archivos
            $sqlDir = $baseDir . '/instalacion/sql/';
            if (is_dir($sqlDir)) {
                foreach (glob($sqlDir . '*.sql') as $file) {
                    $nombre = basename($file, '.sql');
                    $rutaRel = 'instalacion/sql/' . basename($file);
                    $yaListado = false;
                    foreach (array_merge($SQL_CENTRAL, $SQL_LOCAL) as $r) {
                        if (basename($r, '.sql') === $nombre || strpos($r, $nombre . '.sql') !== false) { $yaListado = true; break; }
                    }
                    if (!$yaListado) {
                        $archivos[] = [
                            'nombre' => $nombre,
                            'ruta' => $rutaRel,
                            'tipo' => 'instalacion',
                            'existe' => true,
                            'tamano' => filesize($file)
                        ];
                    }
                }
            }
            echo json_encode(['success' => true, 'archivos' => $archivos]);
            exit;
        }
        
        if ($accion === 'verificar') {
            if ($tipo === 'central') {
                require_once __DIR__ . '/../api-transferencias/conexion-central.php';
                $conexion = ConexionCentral::conectar();
                $stmt = $conexion->query("SELECT DATABASE() as db, @@hostname as host");
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt = $conexion->query("SELECT VERSION() as version");
                $info['version'] = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'tipo' => 'central', 'datos' => $info]);
            } else {
                require_once __DIR__ . '/../modelos/conexion.php';
                $conexion = Conexion::conectar();
                $stmt = $conexion->query("SELECT DATABASE() as db, @@hostname as host");
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt = $conexion->query("SELECT VERSION() as version");
                $info['version'] = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'tipo' => 'local', 'datos' => $info]);
            }
            exit;
        }
        
        if ($accion === 'actualizar') {
            $resultados = [];
            
            if ($tipo === 'central') {
                $archivos = $SQL_CENTRAL;
                require_once __DIR__ . '/../api-transferencias/conexion-central.php';
                $conexion = ConexionCentral::conectar();
            } else {
                $archivos = $SQL_LOCAL;
                require_once __DIR__ . '/../modelos/conexion.php';
                $conexion = Conexion::conectar();
            }
            
            foreach ($archivos as $nombre => $ruta) {
                $rutaCompleta = __DIR__ . '/../' . $ruta;
                if (!file_exists($rutaCompleta)) {
                    $resultados[] = ['archivo' => $nombre, 'estado' => 'omitido', 'mensaje' => 'Archivo no encontrado'];
                    continue;
                }
                
                $sql = file_get_contents($rutaCompleta);
                $sentencias = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($s) { 
                        $s = trim($s); 
                        return strlen($s) > 10 && !preg_match('/^--/', $s) && 
                               !preg_match('/^(DESCRIBE|SELECT \*)/i', $s);
                    }
                );
                
                $ejecutadas = 0;
                $errores = [];
                
                foreach ($sentencias as $sentencia) {
                    $sentencia = trim($sentencia);
                    if (empty($sentencia) || substr($sentencia, 0, 2) === '--') continue;
                    if (preg_match('/^(DESCRIBE|SELECT \*)/i', $sentencia)) continue;
                    
                    try {
                        $conexion->exec($sentencia);
                        $ejecutadas++;
                    } catch (PDOException $e) {
                        $msg = $e->getMessage();
                        if (strpos($msg, 'Duplicate column') !== false || 
                            strpos($msg, 'already exists') !== false ||
                            strpos($msg, 'Duplicate key') !== false) {
                            $ejecutadas++;
                        } else {
                            $errores[] = substr($msg, 0, 150);
                        }
                    }
                }
                
                if (empty($errores)) {
                    $resultados[] = ['archivo' => $nombre, 'estado' => 'ok', 'mensaje' => "$ejecutadas sentencias ejecutadas"];
                } else {
                    $resultados[] = ['archivo' => $nombre, 'estado' => 'error', 'mensaje' => implode('; ', array_slice($errores, 0, 3))];
                }
            }
            
            echo json_encode(['success' => true, 'resultados' => $resultados]);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar - Instalación AdminV5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .panel { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .panel-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .panel-body { padding: 25px; }
        .conexion-card { border: 2px solid #e9ecef; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .conexion-card.activa { border-color: #28a745; background: #f8fff9; }
        .conexion-card.inactiva { border-color: #dc3545; background: #fff8f8; }
        .btn-actualizar { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white; padding: 12px 24px; border-radius: 25px; font-weight: bold; }
        .btn-actualizar:hover { color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40,167,69,0.4); }
        .btn-verificar { background: #6c757d; border: none; color: white; padding: 8px 16px; border-radius: 8px; }
        .resultado-item { padding: 10px; margin: 5px 0; border-radius: 8px; }
        .resultado-item.ok { background: #d4edda; color: #155724; }
        .resultado-item.error { background: #f8d7da; color: #721c24; }
        .resultado-item.omitido { background: #fff3cd; color: #856404; }
        .logout-btn { position: fixed; top: 20px; right: 20px; background: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 5px; text-decoration: none; z-index: 1000; }
        .logout-btn:hover { color: white; }
    </style>
</head>
<body>

<a href="logout.php" class="logout-btn" onclick="return confirm('¿Cerrar sesión?')">🔓 Cerrar Sesión</a>
<a href="instalador-nuevo.php" class="logout-btn" style="right: 140px; background: #6c757d;">📋 Instalador</a>

<div class="container" style="max-width: 900px;">
    <div class="panel">
        <div class="panel-header text-center">
            <h2><i class="fas fa-sync-alt"></i> Panel de Actualización</h2>
            <p class="mb-0">Verifica conexiones y actualiza bases de datos con las últimas migraciones SQL</p>
        </div>
        
        <div class="panel-body">
            <!-- Base CENTRAL -->
            <div class="conexion-card" id="card-central">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5><i class="fas fa-database text-primary"></i> Base de Datos CENTRAL</h5>
                        <p class="text-muted mb-1">Base compartida: despachos, stock en tránsito, solicitudes</p>
                        <div id="info-central" class="small text-muted">Haz clic en "Verificar conexión"</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-verificar" onclick="verificarConexion('central')">
                            <i class="fas fa-plug"></i> Verificar conexión
                        </button>
                        <button type="button" class="btn btn-actualizar" onclick="actualizarBase('central')">
                            <i class="fas fa-arrow-up"></i> Actualizar Central
                        </button>
                    </div>
                </div>
                <div id="resultados-central" class="mt-3" style="display:none;"></div>
            </div>
            
            <!-- Base LOCAL (Sucursal) -->
            <div class="conexion-card" id="card-local">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5><i class="fas fa-store text-success"></i> Base de Datos LOCAL (Sucursal)</h5>
                        <p class="text-muted mb-1">Base de esta sucursal: productos, ventas, usuarios</p>
                        <div id="info-local" class="small text-muted">Haz clic en "Verificar conexión"</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-verificar" onclick="verificarConexion('local')">
                            <i class="fas fa-plug"></i> Verificar conexión
                        </button>
                        <button type="button" class="btn btn-actualizar" onclick="actualizarBase('local')">
                            <i class="fas fa-arrow-up"></i> Actualizar Local
                        </button>
                    </div>
                </div>
                <div id="resultados-local" class="mt-3" style="display:none;"></div>
            </div>
            
            <!-- Verificar archivos SQL -->
            <div class="mt-4 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-file-code"></i> Archivos SQL (instalación y operación)</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="verificarArchivos()">
                        <i class="fas fa-search"></i> Verificar todos
                    </button>
                </div>
                <div id="lista-archivos-sql" class="small">
                    <p class="text-muted mb-0">Haz clic en "Verificar todos" para ver el estado de cada archivo SQL.</p>
                </div>
            </div>
            
            <!-- Actualizar todo -->
            <div class="mt-4 text-center">
                <button type="button" class="btn btn-lg btn-actualizar" onclick="actualizarTodo()">
                    <i class="fas fa-sync-alt"></i> Actualizar Central + Local (todo)
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function verificarConexion(tipo) {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verificando...';
    
    const formData = new FormData();
    formData.append('accion', 'verificar');
    formData.append('tipo', tipo);
    
    fetch('actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            const card = document.getElementById('card-' + tipo);
            const info = document.getElementById('info-' + tipo);
            card.classList.remove('activa', 'inactiva');
            if (data.success) {
                card.classList.add('activa');
                info.innerHTML = '<strong>✅ Conectado</strong><br>Base: ' + data.datos.db + 
                    ' | Host: ' + data.datos.host + ' | MySQL: ' + data.datos.version;
            } else {
                card.classList.add('inactiva');
                info.innerHTML = '<strong>❌ Error:</strong> ' + (data.error || 'Sin conexión');
            }
        })
        .catch(err => {
            document.getElementById('card-' + tipo).classList.add('inactiva');
            document.getElementById('info-' + tipo).innerHTML = '<strong>❌ Error:</strong> ' + err.message;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

function actualizarBase(tipo) {
    if (!confirm('¿Ejecutar migraciones SQL en la base ' + (tipo === 'central' ? 'CENTRAL' : 'LOCAL') + '? Esto puede modificar tablas y columnas.')) return;
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando...';
    
    const contenedor = document.getElementById('resultados-' + tipo);
    contenedor.style.display = 'block';
    contenedor.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Ejecutando migraciones...</p></div>';
    
    const formData = new FormData();
    formData.append('accion', 'actualizar');
    formData.append('tipo', tipo);
    
    fetch('actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            let html = '';
            if (data.success && data.resultados) {
                data.resultados.forEach(r => {
                    const icon = r.estado === 'ok' ? '✅' : (r.estado === 'error' ? '❌' : '⚠️');
                    html += '<div class="resultado-item ' + r.estado + '">' + icon + ' <strong>' + r.archivo + '</strong>: ' + r.mensaje + '</div>';
                });
            } else {
                html = '<div class="resultado-item error">❌ ' + (data.error || 'Error desconocido') + '</div>';
            }
            contenedor.innerHTML = html;
        })
        .catch(err => {
            contenedor.innerHTML = '<div class="resultado-item error">❌ Error: ' + err.message + '</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

function verificarArchivos() {
    const contenedor = document.getElementById('lista-archivos-sql');
    contenedor.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Escaneando...</div>';
    
    const formData = new FormData();
    formData.append('accion', 'verificar_archivos');
    
    fetch('actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.archivos) {
                contenedor.innerHTML = '<div class="text-danger">Error al verificar archivos</div>';
                return;
            }
            let html = '<table class="table table-sm table-bordered mb-0"><thead><tr><th>Archivo</th><th>Tipo</th><th>Estado</th></tr></thead><tbody>';
            data.archivos.forEach(a => {
                const icon = a.existe ? '✅' : '❌';
                const estado = a.existe ? '<span class="text-success">OK (' + Math.round(a.tamano/1024*10)/10 + ' KB)</span>' : '<span class="text-danger">No encontrado</span>';
                const tipoBadge = a.tipo === 'central' ? '<span class="badge bg-primary">Central</span>' : 
                    (a.tipo === 'local' ? '<span class="badge bg-success">Local</span>' : '<span class="badge bg-secondary">Instalación</span>');
                html += '<tr><td>' + icon + ' ' + a.nombre + '</td><td>' + tipoBadge + '</td><td>' + estado + '</td></tr>';
            });
            html += '</tbody></table>';
            contenedor.innerHTML = html;
        })
        .catch(err => {
            contenedor.innerHTML = '<div class="text-danger">Error: ' + err.message + '</div>';
        });
}

function actualizarTodo() {
    if (!confirm('¿Ejecutar TODAS las migraciones SQL en Central y Local? Esto puede modificar tablas y columnas.')) return;
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando Central...';
    
    document.getElementById('resultados-central').style.display = 'block';
    document.getElementById('resultados-central').innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Ejecutando migraciones Central...</p></div>';
    document.getElementById('resultados-local').style.display = 'block';
    document.getElementById('resultados-local').innerHTML = '';
    
    const formData = new FormData();
    formData.append('accion', 'actualizar');
    formData.append('tipo', 'central');
    
    fetch('actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            let html = '';
            if (data.success && data.resultados) {
                data.resultados.forEach(r => {
                    const icon = r.estado === 'ok' ? '✅' : (r.estado === 'error' ? '❌' : '⚠️');
                    html += '<div class="resultado-item ' + r.estado + '">' + icon + ' <strong>' + r.archivo + '</strong>: ' + r.mensaje + '</div>';
                });
            } else {
                html = '<div class="resultado-item error">❌ ' + (data.error || 'Error') + '</div>';
            }
            document.getElementById('resultados-central').innerHTML = html;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando Local...';
            const formDataLocal = new FormData();
            formDataLocal.append('accion', 'actualizar');
            formDataLocal.append('tipo', 'local');
            return fetch('actualizar.php', { method: 'POST', body: formDataLocal });
        })
        .then(r => r.json())
        .then(data => {
            let html = '';
            if (data.success && data.resultados) {
                data.resultados.forEach(r => {
                    const icon = r.estado === 'ok' ? '✅' : (r.estado === 'error' ? '❌' : '⚠️');
                    html += '<div class="resultado-item ' + r.estado + '">' + icon + ' <strong>' + r.archivo + '</strong>: ' + r.mensaje + '</div>';
                });
            } else {
                html = '<div class="resultado-item error">❌ ' + (data.error || 'Error') + '</div>';
            }
            document.getElementById('resultados-local').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('resultados-local').innerHTML = '<div class="resultado-item error">❌ ' + err.message + '</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}
</script>

</body>
</html>
