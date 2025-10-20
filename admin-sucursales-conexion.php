<?php
// Interfaz de administración para gestionar datos de conexión de sucursales
session_start();

// Verificar permisos
if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    die("No tienes permisos para acceder a esta página");
}

require_once __DIR__ . "/api-transferencias/conexion-central.php";

// Procesar actualización si se envió
if ($_POST && isset($_POST['actualizar_sucursal'])) {
    try {
        $conexion = ConexionCentral::conectar();
        
        $stmt = $conexion->prepare("
            UPDATE sucursales SET 
                usuario_bd = ?, 
                password_bd = ?, 
                nombre_bd = ?, 
                host_bd = ?, 
                puerto_bd = ?
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([
            $_POST['usuario_bd'],
            $_POST['password_bd'],
            $_POST['nombre_bd'],
            $_POST['host_bd'],
            $_POST['puerto_bd'],
            $_POST['sucursal_id']
        ]);
        
        if($resultado) {
            $mensaje = "✅ Sucursal actualizada correctamente";
            $tipoMensaje = "success";
        } else {
            $mensaje = "❌ Error al actualizar la sucursal";
            $tipoMensaje = "danger";
        }
        
    } catch(Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Obtener sucursales
try {
    $conexion = ConexionCentral::conectar();
    $stmt = $conexion->prepare("
        SELECT id, codigo_sucursal, nombre, usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd, activo
        FROM sucursales 
        ORDER BY nombre
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $sucursales = [];
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Conexiones de Sucursales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-database"></i> Administración de Conexiones de Sucursales</h3>
                    </div>
                    <div class="card-body">
                        
                        <?php if(isset($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show">
                            <?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th>Usuario BD</th>
                                                <th>Password BD</th>
                                                <th>Nombre BD</th>
                                                <th>Host BD</th>
                                                <th>Puerto BD</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($sucursales as $sucursal): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sucursal['id']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($sucursal['codigo_sucursal']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($sucursal['nombre']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo !empty($sucursal['usuario_bd']) ? 'success' : 'warning'; ?>">
                                                        <?php echo !empty($sucursal['usuario_bd']) ? htmlspecialchars($sucursal['usuario_bd']) : 'Sin configurar'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo !empty($sucursal['password_bd']) ? 'success' : 'warning'; ?>">
                                                        <?php echo !empty($sucursal['password_bd']) ? '••••••••' : 'Sin configurar'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo !empty($sucursal['nombre_bd']) ? 'success' : 'warning'; ?>">
                                                        <?php echo !empty($sucursal['nombre_bd']) ? htmlspecialchars($sucursal['nombre_bd']) : 'Sin configurar'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($sucursal['host_bd']); ?></td>
                                                <td><?php echo htmlspecialchars($sucursal['puerto_bd']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $sucursal['activo'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $sucursal['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $sucursal['id']; ?>">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de edición -->
    <?php foreach($sucursales as $sucursal): ?>
    <div class="modal fade" id="modalEditar<?php echo $sucursal['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Conexión - <?php echo htmlspecialchars($sucursal['nombre']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="sucursal_id" value="<?php echo $sucursal['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="usuario_bd_<?php echo $sucursal['id']; ?>" class="form-label">Usuario BD:</label>
                            <input type="text" class="form-control" id="usuario_bd_<?php echo $sucursal['id']; ?>" 
                                   name="usuario_bd" value="<?php echo htmlspecialchars($sucursal['usuario_bd']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_bd_<?php echo $sucursal['id']; ?>" class="form-label">Password BD:</label>
                            <input type="password" class="form-control" id="password_bd_<?php echo $sucursal['id']; ?>" 
                                   name="password_bd" value="<?php echo htmlspecialchars($sucursal['password_bd']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nombre_bd_<?php echo $sucursal['id']; ?>" class="form-label">Nombre BD:</label>
                            <input type="text" class="form-control" id="nombre_bd_<?php echo $sucursal['id']; ?>" 
                                   name="nombre_bd" value="<?php echo htmlspecialchars($sucursal['nombre_bd']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="host_bd_<?php echo $sucursal['id']; ?>" class="form-label">Host BD:</label>
                            <input type="text" class="form-control" id="host_bd_<?php echo $sucursal['id']; ?>" 
                                   name="host_bd" value="<?php echo htmlspecialchars($sucursal['host_bd']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="puerto_bd_<?php echo $sucursal['id']; ?>" class="form-label">Puerto BD:</label>
                            <input type="number" class="form-control" id="puerto_bd_<?php echo $sucursal['id']; ?>" 
                                   name="puerto_bd" value="<?php echo htmlspecialchars($sucursal['puerto_bd']); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="actualizar_sucursal" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
