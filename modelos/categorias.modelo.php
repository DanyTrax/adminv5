<?php

require_once "conexion.php";

class ModeloCategorias{

	/*=============================================
	CREAR CATEGORIA
	=============================================*/

	static public function mdlIngresarCategoria($tabla, $datos){
		try {
			$db = Conexion::conectar();
			
			// Verificar si la tabla tiene campo prefijo
			$stmtCheck = $db->prepare("SHOW COLUMNS FROM $tabla LIKE 'prefijo'");
			$stmtCheck->execute();
			$tienePrefijo = $stmtCheck->rowCount() > 0;
			
			// Si $datos es un array (con prefijo), usar ambos campos
			if (is_array($datos)) {
				$categoria = $datos["categoria"];
				$prefijo = isset($datos["prefijo"]) && !empty($datos["prefijo"]) ? strtoupper(trim($datos["prefijo"])) : null;
				
				if ($tienePrefijo && $prefijo !== null) {
					$stmt = $db->prepare("INSERT INTO $tabla(categoria, prefijo) VALUES (:categoria, :prefijo)");
					$stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
					$stmt->bindParam(":prefijo", $prefijo, PDO::PARAM_STR);
				} else {
					$stmt = $db->prepare("INSERT INTO $tabla(categoria) VALUES (:categoria)");
					$stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
				}
			} else {
				// Compatibilidad con código antiguo donde $datos es solo el nombre
				$stmt = $db->prepare("INSERT INTO $tabla(categoria) VALUES (:categoria)");
				$stmt->bindParam(":categoria", $datos, PDO::PARAM_STR);
			}

			if($stmt->execute()){
				return "ok";
			} else {
				return "error";
			}
		} catch (Exception $e) {
			error_log("Error creando categoría: " . $e->getMessage());
			return "error";
		}
	}

	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/

	static public function mdlMostrarCategorias($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	EDITAR CATEGORIA
	=============================================*/

	static public function mdlEditarCategoria($tabla, $datos){
		try {
			$db = Conexion::conectar();
			
			// Verificar si la tabla tiene campo prefijo
			$stmtCheck = $db->prepare("SHOW COLUMNS FROM $tabla LIKE 'prefijo'");
			$stmtCheck->execute();
			$tienePrefijo = $stmtCheck->rowCount() > 0;
			
			if ($tienePrefijo && isset($datos["prefijo"])) {
				$stmt = $db->prepare("UPDATE $tabla SET categoria = :categoria, prefijo = :prefijo WHERE id = :id");
				$prefijo = !empty($datos["prefijo"]) ? strtoupper(trim($datos["prefijo"])) : null;
				$stmt->bindParam(":prefijo", $prefijo, PDO::PARAM_STR);
			} else {
				$stmt = $db->prepare("UPDATE $tabla SET categoria = :categoria WHERE id = :id");
			}

			$stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
			$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

			if($stmt->execute()){
				return "ok";
			} else {
				return "error";
			}
		} catch (Exception $e) {
			error_log("Error editando categoría: " . $e->getMessage());
			return "error";
		}
	}

/*=============================================
BORRAR CATEGORIA
=============================================*/
static public function mdlBorrarCategoria($tabla, $datos){
    $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
    $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
    if($stmt->execute()){
        return "ok";
    } else {
        return "error";	
    }
    $stmt->close();
    $stmt = null;
}
/*=============================================
CONTAR PRODUCTOS POR CATEGORIA (MÉTODO FIABLE)
=============================================*/
static public function mdlContarProductosPorCategoria($tabla, $item, $valor){
    $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE $item = :$item");
    $stmt->bindParam(":".$item, $valor, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn(); // Devuelve solo el número del conteo (ej: '0', '5', etc.)
}

/*=============================================
BORRAR TODAS LAS CATEGORÍAS DE LA SUCURSAL
=============================================*/
static public function mdlBorrarTodasCategorias($tabla) {
    try {
        $db = Conexion::conectar();
        $db->beginTransaction();
        
        // Eliminar todas las categorías
        $stmt = $db->prepare("DELETE FROM $tabla");
        $resultado = $stmt->execute();
        
        if ($resultado) {
            $db->commit();
            return "ok";
        } else {
            $db->rollBack();
            return "error";
        }
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        error_log("Error borrando todas las categorías: " . $e->getMessage());
        return "error";
    }
}

/*=============================================
SINCRONIZAR CATEGORÍAS DESDE CENTRAL
=============================================*/
static public function mdlSincronizarCategoriasDesdeCentral($tabla) {
    try {
        require_once __DIR__ . "/categorias-central.modelo.php";
        
        // Primero borrar todas las categorías
        $borrar = self::mdlBorrarTodasCategorias($tabla);
        if ($borrar !== "ok") {
            return "error_borrar";
        }
        
        // Luego sincronizar desde central
        $sincronizacion = ModeloCategoriasCentral::mdlSincronizarCentralASucursalActual();
        
        if ($sincronizacion && isset($sincronizacion['success']) && $sincronizacion['success']) {
            return "ok";
        } else {
            return "error_sincronizar";
        }
    } catch (Exception $e) {
        error_log("Error sincronizando categorías: " . $e->getMessage());
        return "error";
    }
}

}

