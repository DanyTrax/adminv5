<?php
session_start();

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    die("Sin permisos");
}

echo "<h1>🔍 Debug del archivo CSV</h1>";

if(isset($_POST["debugCSV"])) {
    
    if(!empty($_FILES["archivoCSV"]["tmp_name"])) {
        
        $archivo = $_FILES["archivoCSV"]["tmp_name"];
        $nombreArchivo = $_FILES["archivoCSV"]["name"];
        
        echo "<h2>📁 Archivo: {$nombreArchivo}</h2>";
        
        $contenido = file_get_contents($archivo);
        
        echo "<h3>📊 Información básica:</h3>";
        echo "<p><strong>Tamaño:</strong> " . strlen($contenido) . " bytes</p>";
        echo "<p><strong>Primeras 500 caracteres:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars(substr($contenido, 0, 500)) . "</pre>";
        
        // Analizar líneas
        $lineas = str_getcsv($contenido, "\n");
        
        echo "<h3>📋 Análisis de líneas:</h3>";
        echo "<p><strong>Total líneas:</strong> " . count($lineas) . "</p>";
        
        // Analizar encabezados
        if(count($lineas) > 0) {
            $primeraLinea = $lineas[0];
            echo "<p><strong>Primera línea (encabezados):</strong></p>";
            echo "<pre style='background: #e6f3ff; padding: 10px; border: 1px solid #0066cc;'>" . htmlspecialchars($primeraLinea) . "</pre>";
            
            $encabezados = str_getcsv($primeraLinea, ',');
            echo "<p><strong>Encabezados parseados:</strong></p>";
            echo "<ol>";
            foreach($encabezados as $i => $encabezado) {
                $encabezado = trim($encabezado);
                echo "<li><strong>[{$i}]</strong> '" . htmlspecialchars($encabezado) . "' <em>(longitud: " . strlen($encabezado) . ")</em></li>";
            }
            echo "</ol>";
        }
        
        // Analizar primera línea de datos
        if(count($lineas) > 1) {
            $segundaLinea = $lineas[1];
            echo "<p><strong>Segunda línea (primeros datos):</strong></p>";
            echo "<pre style='background: #fff2e6; padding: 10px; border: 1px solid #ff8800;'>" . htmlspecialchars($segundaLinea) . "</pre>";
            
            $datos = str_getcsv($segundaLinea, ',');
            echo "<p><strong>Datos parseados:</strong></p>";
            echo "<ol>";
            foreach($datos as $i => $dato) {
                $dato = trim($dato);
                echo "<li><strong>[{$i}]</strong> '" . htmlspecialchars($dato) . "' <em>(longitud: " . strlen($dato) . ")</em></li>";
            }
            echo "</ol>";
            
            // Combinar encabezados con datos
            if(count($lineas) > 0 && count($datos) > 0) {
                $encabezados = str_getcsv($lineas[0], ',');
                $encabezados = array_map('trim', $encabezados);
                
                echo "<h3>🔗 Combinación encabezado → dato:</h3>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>Índice</th><th>Encabezado</th><th>Dato</th><th>¿Vacío?</th></tr>";
                
                $maxItems = max(count($encabezados), count($datos));
                for($i = 0; $i < $maxItems; $i++) {
                    $encabezado = isset($encabezados[$i]) ? trim($encabezados[$i]) : '(sin encabezado)';
                    $dato = isset($datos[$i]) ? trim($datos[$i]) : '(sin dato)';
                    $vacio = empty($dato) ? 'SÍ' : 'NO';
                    
                    echo "<tr>";
                    echo "<td><strong>{$i}</strong></td>";
                    echo "<td>" . htmlspecialchars($encabezado) . "</td>";
                    echo "<td>" . htmlspecialchars($dato) . "</td>";
                    echo "<td style='color: " . (empty($dato) ? 'red' : 'green') . ";'><strong>{$vacio}</strong></td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // Verificar campo ID específicamente
                if(isset($encabezados[0]) && trim($encabezados[0]) === 'ID') {
                    $valorID = isset($datos[0]) ? trim($datos[0]) : '';
                    echo "<div style='background: #" . (empty($valorID) ? 'ffebee' : 'e8f5e8') . "; padding: 15px; margin: 10px 0; border: 2px solid #" . (empty($valorID) ? 'f44336' : '4caf50') . ";'>";
                    echo "<h3>🆔 ANÁLISIS DEL CAMPO ID:</h3>";
                    echo "<p><strong>Encabezado encontrado:</strong> '" . htmlspecialchars($encabezados[0]) . "'</p>";
                    echo "<p><strong>Valor ID:</strong> '" . htmlspecialchars($valorID) . "'</p>";
                    echo "<p><strong>¿Está vacío?:</strong> " . (empty($valorID) ? '<span style="color: red;">SÍ - ESTE ES EL PROBLEMA</span>' : '<span style="color: green;">NO</span>') . "</p>";
                    echo "</div>";
                }
            }
        }
        
        echo "<hr>";
        echo "<h3>💡 Recomendaciones:</h3>";
        echo "<ul>";
        echo "<li>Verifica que la <strong>primera columna sea realmente 'ID'</strong></li>";
        echo "<li>Asegúrate de que <strong>no haya filas vacías</strong> al inicio</li>";
        echo "<li>Confirma que <strong>no haya caracteres especiales</strong> en los encabezados</li>";
        echo "<li>Verifica que el archivo se esté <strong>guardando correctamente</strong> como CSV</li>";
        echo "</ul>";
        
    } else {
        echo "<p>No se seleccionó archivo.</p>";
    }
}

?>

<hr>
<h2>📤 Subir archivo CSV para debug</h2>
<form method="post" enctype="multipart/form-data">
    <p>
        <label><strong>Seleccionar archivo CSV:</strong></label><br>
        <input type="file" name="archivoCSV" accept=".csv,.xls,.xlsx" required>
    </p>
    <p>
        <button type="submit" name="debugCSV" style="background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer;">
            🔍 Analizar archivo
        </button>
    </p>
</form>

<hr>
<p><a href="catalogo-maestro">← Volver al catálogo</a></p>