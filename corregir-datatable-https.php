<?php
/*=============================================
CORREGIR DATATABLE PARA HTTPS
=============================================*/

echo "🔧 Corrigiendo DataTable para HTTPS...\n\n";

$archivo_vista = 'vistas/modulos/registro-descargas-simple.php';

if(file_exists($archivo_vista)) {
    echo "📁 Archivo encontrado: $archivo_vista\n";
    
    $contenido = file_get_contents($archivo_vista);
    $contenido_original = $contenido;
    
    // Reemplazar URL HTTP por HTTPS para DataTables
    $contenido = str_replace(
        '"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"',
        '"url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"',
        $contenido
    );
    
    // También cambiar la configuración de idioma
    $contenido = str_replace(
        '"language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },',
        '"language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron registros",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },',
        $contenido
    );
    
    // Solo escribir si hubo cambios
    if($contenido !== $contenido_original) {
        if(file_put_contents($archivo_vista, $contenido)) {
            echo "✅ DataTable corregido para HTTPS\n";
        } else {
            echo "❌ Error al escribir archivo\n";
        }
    } else {
        echo "ℹ️ DataTable ya estaba configurado correctamente\n";
    }
    
} else {
    echo "❌ Archivo no encontrado: $archivo_vista\n";
}

echo "\n🎯 DataTable corregido para HTTPS\n";
echo "🔧 Próximos pasos:\n";
echo "1. Ejecutar: corregir-tabla-registro-descargas.php\n";
echo "2. Probar el módulo registro-descargas-simple\n";
echo "3. Verificar que no hay errores CORS\n";
?>
