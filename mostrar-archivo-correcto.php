<?php
/**
 * Script para mostrar el contenido del archivo correcto
 */

echo "<h2>📄 Contenido del Archivo Correcto</h2>\n";

echo "<h3>🔧 Archivo: obtener-medios-pago-activos.php</h3>\n";
echo "<p><strong>Ubicación:</strong> api-transferencias/obtener-medios-pago-activos.php</p>\n";
echo "<p><strong>Propósito:</strong> Obtener medios de pago de la BD local de la sucursal</p>\n";

echo "<h3>📝 Contenido Completo:</h3>\n";
echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ccc; font-family: monospace; font-size: 12px; line-height: 1.4; max-height: 600px; overflow-y: auto;'>\n";
echo htmlspecialchars(file_get_contents('obtener-medios-pago-activos.php'));
echo "</pre>\n";

echo "<h3>🎯 Características del Archivo Correcto:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Consulta BD local:</strong> Usa config.php de la sucursal</li>\n";
echo "<li>✅ <strong>Tabla correcta:</strong> medios_pago (no medios_pago_central)</li>\n";
echo "<li>✅ <strong>Filtro activo:</strong> WHERE activo = 1</li>\n";
echo "<li>✅ <strong>Respuesta JSON:</strong> Formato correcto para el instalador</li>\n";
echo "<li>✅ <strong>Manejo de errores:</strong> Try-catch con logging</li>\n";
echo "<li>✅ <strong>Headers CORS:</strong> Para permitir acceso desde el instalador</li>\n";
echo "</ul>\n";

echo "<h3>📋 Instrucciones de Instalación:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Acceder al servidor de la sucursal:</strong> pruebas.acrilicosinfinito.com</li>\n";
echo "<li><strong>Ir a la carpeta:</strong> api-transferencias/</li>\n";
echo "<li><strong>Hacer backup del archivo actual:</strong> obtener-medios-pago-activos.php.backup</li>\n";
echo "<li><strong>Reemplazar con el archivo correcto:</strong> Copiar el contenido de arriba</li>\n";
echo "<li><strong>Verificar permisos:</strong> 644 o 755</li>\n";
echo "<li><strong>Probar la API:</strong> https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php</li>\n";
echo "</ol>\n";

echo "<h3>🧪 Prueba de Funcionamiento:</h3>\n";
echo "<p>Después de instalar, la API debe devolver:</p>\n";
echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>\n";
echo htmlspecialchars('{
    "success": true,
    "message": "Medios de pago obtenidos exitosamente",
    "total": 3,
    "medios_pago": [
        {
            "id": "1",
            "nombre": "Efectivo",
            "descripcion": "Pago en efectivo",
            "activo": "1",
            "fecha_creacion": "2024-01-01 00:00:00"
        }
    ]
}');
echo "</pre>\n";

echo "<h3>⚠️ Nota Importante:</h3>\n";
echo "<p>Este archivo debe estar en <strong>CADA SUCURSAL</strong>, no en el central.</p>\n";
echo "<p>Cada sucursal debe consultar su propia tabla <code>medios_pago</code> local.</p>\n";
?>
