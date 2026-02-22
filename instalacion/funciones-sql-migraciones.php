<?php
/**
 * Funciones compartidas para ejecutar migraciones SQL con comparación inteligente.
 * Compara tablas/columnas/índices y solo aplica lo que falta.
 */

// Scripts para BD LOCAL (sucursales: productos, categorias, ventas, etc.)
$GLOBALS['SQL_LOCAL'] = [
    'crear-abonos-historial' => 'instalacion/sql/crear-abonos-historial.sql',
    'agregar-columnas-bd' => 'instalacion/sql/agregar-columnas-bd.sql',
    'crear-tablas-trazabilidad' => 'instalacion/sql/crear-tablas-trazabilidad.sql',
    'agregar-prefijo-categorias' => 'agregar-prefijo-categorias.sql'
];

// Scripts para BD CENTRAL (despachos, stock_transito, solicitudes, categorias maestro, etc.)
$GLOBALS['SQL_CENTRAL'] = [
    'stock-transito-despacho' => 'instalacion/sql/stock-transito-despacho.sql',
    'trazabilidad-solicitud-despacho' => 'instalacion/sql/trazabilidad-solicitud-despacho.sql',
    'historial-despachos' => 'instalacion/sql/historial-despachos.sql',
    'agregar-prefijo-categorias' => 'agregar-prefijo-categorias.sql'
];

/**
 * Ejecuta sentencias SQL de un archivo con lógica de comparación.
 * @param PDO $pdo Conexión a la BD
 * @param string $rutaCompleta Ruta absoluta al archivo .sql
 * @return array ['ejecutadas' => int, 'omitidas' => int, 'errores' => string[]]
 */
function ejecutarSQLConComparacion($pdo, $rutaCompleta) {
    $ejecutadas = 0;
    $omitidas = 0;
    $errores = [];

    if (!file_exists($rutaCompleta)) {
        return ['ejecutadas' => 0, 'omitidas' => 0, 'errores' => ['Archivo no encontrado']];
    }

    $sql = file_get_contents($rutaCompleta);
    $sentencias = array_filter(
        array_map(function($s) {
            $s = trim(preg_replace('/^(\s*--[^\n]*\n?)+/', '', trim($s)));
            return $s;
        }, explode(';', $sql)),
        function($s) { 
            return strlen($s) > 10 && !preg_match('/^--/', $s) && !preg_match('/^(DESCRIBE|SELECT \*)/i', $s);
        }
    );

    foreach ($sentencias as $sentencia) {
        $sentencia = trim($sentencia);
        if (empty($sentencia) || substr($sentencia, 0, 2) === '--') continue;
        if (preg_match('/^(DESCRIBE|SELECT \*)/i', $sentencia)) continue;

        $debeEjecutar = true;
        try {
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $sentencia, $m)) {
                $tabla = $m[1];
                if (strpos($sentencia, 'IF NOT EXISTS') === false) {
                    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tabla));
                    if ($stmt && $stmt->rowCount() > 0) {
                        $debeEjecutar = false;
                        $omitidas++;
                    }
                }
            } elseif (preg_match('/ALTER\s+TABLE\s+[`"]?(\w+)[`"]?\s+ADD\s+COLUMN\s+[`"]?(\w+)[`"]?/i', $sentencia, $m)) {
                $tabla = $m[1];
                $columna = $m[2];
                $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tabla` LIKE ?");
                $stmt->execute([$columna]);
                if ($stmt->rowCount() > 0) {
                    $debeEjecutar = false;
                    $omitidas++;
                }
            } elseif (preg_match('/ALTER\s+TABLE\s+[`"]?(\w+)[`"]?\s+ADD\s+(?:INDEX|KEY)\s+[`"]?(\w+)[`"]?/i', $sentencia, $m)) {
                $tabla = $m[1];
                $indice = $m[2];
                $stmt = $pdo->query("SHOW INDEX FROM `$tabla` WHERE Key_name = " . $pdo->quote($indice));
                if ($stmt && $stmt->rowCount() > 0) {
                    $debeEjecutar = false;
                    $omitidas++;
                }
            } elseif (preg_match('/CREATE\s+INDEX\s+[`"]?(\w+)[`"]?\s+ON\s+[`"]?(\w+)[`"]?/i', $sentencia, $m)) {
                $indice = $m[1];
                $tabla = $m[2];
                $stmt = $pdo->query("SHOW INDEX FROM `$tabla` WHERE Key_name = " . $pdo->quote($indice));
                if ($stmt && $stmt->rowCount() > 0) {
                    $debeEjecutar = false;
                    $omitidas++;
                }
            }
        } catch (PDOException $e) {
            // Si falla la verificación (ej. tabla no existe), intentar ejecutar
        }

        if ($debeEjecutar) {
            try {
                $pdo->exec($sentencia);
                $ejecutadas++;
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate column') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Duplicate key') !== false) {
                    $omitidas++;
                } else {
                    $errores[] = substr($msg, 0, 120);
                }
            }
        }
    }

    return ['ejecutadas' => $ejecutadas, 'omitidas' => $omitidas, 'errores' => $errores];
}
