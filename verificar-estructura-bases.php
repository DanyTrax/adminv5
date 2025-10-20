<?php

// Ajustar zona horaria
@date_default_timezone_set('America/Bogota');

// Utilidades simples
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function seccion($titulo){
    echo "<h2 style='margin-top:24px;border-bottom:1px solid #ddd;padding-bottom:6px;'>" . h($titulo) . "</h2>\n";
}

// Cargar conexiones
$errores = [];
$centralOk = false; $localOk = false;

try {
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $centralOk = true;
} catch (Throwable $e) {
    $errores[] = "BD Central: " . $e->getMessage();
}

try {
    require_once __DIR__ . "/modelos/conexion.php";
    $pdoLocal = Conexion::conectar();
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localOk = true;
} catch (Throwable $e) {
    $errores[] = "BD Local: " . $e->getMessage();
}

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><meta charset='utf-8'><title>Verificación de Estructura - BD Local y Central</title>\n";
echo "<style>
body{font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#222;}
.table{border-collapse:collapse; width:100%; margin:8px 0;}
.table th,.table td{border:1px solid #ddd; padding:6px;}
.table th{background:#f7f7f7; text-align:left;}
.badge{display:inline-block; padding:2px 6px; border-radius:10px; background:#eee;}
.ok{color:#2e7d32;}
.err{color:#c62828;}
.code{font-family:Menlo,Consolas,monospace; white-space:pre;}
.box{border:1px solid #ddd; padding:10px; border-radius:6px; margin:10px 0;}
</style></head><body>\n";

echo "<h1>🔍 Estructura de Bases de Datos (Local y Central)</h1>";
if (!empty($errores)) {
    echo "<div class='box err'><strong>Errores de conexión:</strong><ul>";
    foreach($errores as $err){ echo "<li>" . h($err) . "</li>"; }
    echo "</ul></div>";
}

echo "<div class='box'><strong>Fecha:</strong> " . h(date('Y-m-d H:i:s')) . "<br>";
echo "<strong>Servidor:</strong> " . h($_SERVER['HTTP_HOST'] ?? 'CLI') . "</div>";

function listarBD(PDO $pdo, string $titulo){
    seccion($titulo);

    // Nombre de la BD actual
    try {
        $dbName = $pdo->query("SELECT DATABASE() AS db")->fetch(PDO::FETCH_ASSOC)['db'] ?? '(desconocida)';
        echo "<p><strong>Base de datos:</strong> " . h($dbName) . "</p>";
    } catch (Throwable $e) {
        echo "<p class='err'>No se pudo obtener el nombre de la BD: " . h($e->getMessage()) . "</p>";
    }

    // Listar tablas
    try {
        $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        if (empty($tablas)) {
            echo "<p class='err'>No hay tablas.</p>";
            return;
        }
        echo "<p><strong>Total tablas:</strong> " . count($tablas) . "</p>";

        foreach($tablas as $row){
            $tabla = $row[0];
            echo "<h3>📄 Tabla: " . h($tabla) . "</h3>";

            // DESCRIBE
            try {
                $cols = $pdo->query("DESCRIBE `{$tabla}`")->fetchAll(PDO::FETCH_ASSOC);
                echo "<table class='table'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
                foreach($cols as $c){
                    echo "<tr><td>".h($c['Field'])."</td><td>".h($c['Type'])."</td><td>".h($c['Null'])."</td><td>".h($c['Key'])."</td><td>".h($c['Default'])."</td><td>".h($c['Extra'])."</td></tr>";
                }
                echo "</table>";
            } catch (Throwable $e) {
                echo "<p class='err'>DESCRIBE falló: " . h($e->getMessage()) . "</p>";
            }

            // Índices
            try {
                $idx = $pdo->query("SHOW INDEX FROM `{$tabla}`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($idx)){
                    echo "<h4>Índices</h4>";
                    echo "<table class='table'><tr><th>Key_name</th><th>Non_unique</th><th>Seq_in_index</th><th>Column_name</th><th>Index_type</th></tr>";
                    foreach($idx as $i){
                        echo "<tr>";
                        echo "<td>".h($i['Key_name'])."</td>";
                        echo "<td>".h($i['Non_unique'])."</td>";
                        echo "<td>".h($i['Seq_in_index'])."</td>";
                        echo "<td>".h($i['Column_name'])."</td>";
                        echo "<td>".h($i['Index_type'])."</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } catch (Throwable $e) {
                echo "<p class='err'>SHOW INDEX falló: " . h($e->getMessage()) . "</p>";
            }

            // Conteo de filas
            try {
                $count = $pdo->query("SELECT COUNT(*) AS c FROM `{$tabla}`")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
                echo "<p><span class='badge'>Filas: " . h($count) . "</span></p>";
            } catch (Throwable $e) {
                echo "<p class='err'>COUNT(*) falló: " . h($e->getMessage()) . "</p>";
            }
        }
    } catch (Throwable $e) {
        echo "<p class='err'>Error listando tablas: " . h($e->getMessage()) . "</p>";
    }
}

if ($localOk) {
    listarBD($pdoLocal, '📌 Estructura BD Local');
}
if ($centralOk) {
    listarBD($pdoCentral, '🌐 Estructura BD Central');
}

if (!$localOk && !$centralOk) {
    echo "<p class='err'>No fue posible conectarse a ninguna base de datos.</p>";
}

echo "</body></html>";
