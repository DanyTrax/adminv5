<?php

require_once "modelos/conexion.php";

/*=============================================
SCRIPT PARA LIMPIAR PRODUCTOS NO DIVISIBLES
=============================================*/

class LimpiezaProductos {
    
    /*=============================================
    CONEXIÓN A BASE CENTRAL
    =============================================*/
    static private function conectarCentral() {
        try {
            $servidor = "localhost";
            $nombreBD = "epicosie_central";
            $usuario = "epicosie_central";
            $password = "=Nf?M#6A'QU&.6c";
            
            $link = new PDO(
                "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password
            );
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $link;
        } catch (PDOException $e) {
            die("Error de conexión central: " . $e->getMessage());
        }
    }
    
    /*=============================================
    LIMPIAR PRODUCTOS NO DIVISIBLES
    =============================================*/
    public static function limpiarProductosNoDivisibles() {
        
        try {
            
            // Buscar productos que NO son divisibles pero tienen hijos configurados
            $stmtBuscar = self::conectarCentral()->prepare("
                SELECT id, codigo, descripcion, 
                       codigo_hijo_mitad, codigo_hijo_tercio, codigo_hijo_cuarto
                FROM catalogo_maestro 
                WHERE es_divisible = 0 
                AND (codigo_hijo_mitad != '' 
                     OR codigo_hijo_tercio != '' 
                     OR codigo_hijo_cuarto != '' 
                     OR codigo_hijo_mitad IS NOT NULL 
                     OR codigo_hijo_tercio IS NOT NULL 
                     OR codigo_hijo_cuarto IS NOT NULL)
                AND activo = 1
            ");
            
            $stmtBuscar->execute();
            $productosALimpiar = $stmtBuscar->fetchAll();
            
            if(count($productosALimpiar) > 0) {
                
                echo "<h2>🔍 Productos NO divisibles con hijos configurados:</h2>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Mitad</th><th>Tercio</th><th>Cuarto</th></tr>";
                
                foreach($productosALimpiar as $producto) {
                    echo "<tr>";
                    echo "<td>" . $producto["id"] . "</td>";
                    echo "<td>" . $producto["codigo"] . "</td>";
                    echo "<td>" . $producto["descripcion"] . "</td>";
                    echo "<td>" . ($producto["codigo_hijo_mitad"] ?: "VACÍO") . "</td>";
                    echo "<td>" . ($producto["codigo_hijo_tercio"] ?: "VACÍO") . "</td>";
                    echo "<td>" . ($producto["codigo_hijo_cuarto"] ?: "VACÍO") . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Limpiar productos
                $stmtLimpiar = self::conectarCentral()->prepare("
                    UPDATE catalogo_maestro 
                    SET codigo_hijo_mitad = '',
                        codigo_hijo_tercio = '',
                        codigo_hijo_cuarto = '',
                        fecha_actualizacion = NOW()
                    WHERE es_divisible = 0 
                    AND (codigo_hijo_mitad != '' 
                         OR codigo_hijo_tercio != '' 
                         OR codigo_hijo_cuarto != '' 
                         OR codigo_hijo_mitad IS NOT NULL 
                         OR codigo_hijo_tercio IS NOT NULL 
                         OR codigo_hijo_cuarto IS NOT NULL)
                    AND activo = 1
                ");
                
                if($stmtLimpiar->execute()) {
                    $productosLimpiados = $stmtLimpiar->rowCount();
                    echo "<h3 style='color: green;'>✅ Limpieza completada: $productosLimpiados productos actualizados</h3>";
                } else {
                    echo "<h3 style='color: red;'>❌ Error al limpiar productos</h3>";
                }
                
            } else {
                echo "<h3 style='color: blue;'>🎉 No hay productos que limpiar. Todos están correctos.</h3>";
            }
            
        } catch (Exception $e) {
            echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
        }
    }
    
    /*=============================================
    VERIFICAR ESTADO DESPUÉS DE LIMPIEZA
    =============================================*/
    public static function verificarEstado() {
        
        try {
            
            $stmt = self::conectarCentral()->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN es_divisible = 1 THEN 1 ELSE 0 END) as divisibles,
                    SUM(CASE WHEN es_divisible = 0 THEN 1 ELSE 0 END) as no_divisibles,
                    SUM(CASE WHEN es_divisible = 0 AND (codigo_hijo_mitad != '' OR codigo_hijo_tercio != '' OR codigo_hijo_cuarto != '') THEN 1 ELSE 0 END) as no_divisibles_con_hijos
                FROM catalogo_maestro 
                WHERE activo = 1
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch();
            
            echo "<h2>📊 Estado actual del catálogo:</h2>";
            echo "<ul>";
            echo "<li><strong>Total productos:</strong> " . $resultado["total"] . "</li>";
            echo "<li><strong>Productos divisibles:</strong> " . $resultado["divisibles"] . "</li>";
            echo "<li><strong>Productos NO divisibles:</strong> " . $resultado["no_divisibles"] . "</li>";
            echo "<li><strong>NO divisibles con hijos (ERROR):</strong> " . $resultado["no_divisibles_con_hijos"] . "</li>";
            echo "</ul>";
            
            if($resultado["no_divisibles_con_hijos"] > 0) {
                echo "<p style='color: red;'>⚠️ Aún hay productos NO divisibles con hijos configurados</p>";
            } else {
                echo "<p style='color: green;'>✅ Todos los productos están correctamente configurados</p>";
            }