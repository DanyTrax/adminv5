<?php
require_once "conexion.php";
class ModeloProductos {
	static public function mdlMostrarProductos($tabla, $item, $valor, $orden){
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();
		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		$stmt = null;
	}
	static public function mdlIngresarProducto($tabla, $datos) {
		$campos = array_keys($datos);
		$camposSQL = implode(", ", $campos);
		$placeholders = ":" . implode(", :", $campos);
		$sql = "INSERT INTO $tabla ($camposSQL) VALUES ($placeholders)";
		try {
            $stmt = Conexion::conectar()->prepare($sql);
            foreach ($datos as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            return "error";
        }
	}
	static public function mdlEditarProducto($tabla, $datos) {
		$sql = "UPDATE $tabla SET id_categoria = :id_categoria, codigo = :codigo, descripcion = :descripcion, stock = :stock, precio_venta = :precio_venta, imagen = :imagen, es_divisible = :es_divisible, nombre_mitad = :nombre_mitad, precio_mitad = :precio_mitad, nombre_tercio = :nombre_tercio, precio_tercio = :precio_tercio, nombre_cuarto = :nombre_cuarto, precio_cuarto = :precio_cuarto WHERE id = :id";
		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":es_divisible", $datos["es_divisible"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre_mitad", $datos["nombre_mitad"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_mitad", $datos["precio_mitad"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_tercio", $datos["nombre_tercio"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_tercio", $datos["precio_tercio"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_cuarto", $datos["nombre_cuarto"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_cuarto", $datos["precio_cuarto"], PDO::PARAM_STR);
		if($stmt->execute()){ return "ok"; }else{ return "error";	}
	}
	static public function mdlEliminarProducto($tabla, $id) {
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		if($stmt->execute()){ return "ok"; }else{ return "error"; }
	}
	static public function mdlActualizarCampo($tabla, $campo, $valorCampo, $id) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $campo = :$campo WHERE id = :id");
		$stmt->bindParam(":$campo", $valorCampo, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		if($stmt->execute()){ return "ok"; }else{ return "error"; }
	}
	static public function mdlDividirProducto($idProductoPadre, $nombreParte, $precioParte, $cantidadResultante) {
		$db = Conexion::conectar();
		$db->beginTransaction();
		try {
			$stmtPadre = $db->prepare("UPDATE productos SET stock = stock - 1 WHERE id = :id_padre AND stock > 0");
			$stmtPadre->bindParam(":id_padre", $idProductoPadre, PDO::PARAM_INT);
			$stmtPadre->execute();
			if ($stmtPadre->rowCount() == 0) { $db->rollBack(); return "error_stock"; }
			
			$stmtBusqueda = $db->prepare("SELECT * FROM productos WHERE descripcion = :descripcion");
			$stmtBusqueda->bindParam(":descripcion", $nombreParte, PDO::PARAM_STR);
			$stmtBusqueda->execute();
			$productoHijo = $stmtBusqueda->fetch(PDO::FETCH_ASSOC);
			
			if ($productoHijo) {
				$stmtHijo = $db->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id_hijo");
				$stmtHijo->bindParam(":cantidad", $cantidadResultante, PDO::PARAM_INT);
				$stmtHijo->bindParam(":id_hijo", $productoHijo['id'], PDO::PARAM_INT);
				$stmtHijo->execute();
			} else {
				$stmtPadreInfo = $db->prepare("SELECT * FROM productos WHERE id = :id_padre");
				$stmtPadreInfo->bindParam(":id_padre", $idProductoPadre, PDO::PARAM_INT);
				$stmtPadreInfo->execute();
				$padreInfo = $stmtPadreInfo->fetch(PDO::FETCH_ASSOC);
				$stmtCodigo = $db->prepare("SELECT MAX(CAST(codigo AS UNSIGNED)) as ultimo_codigo FROM productos");
				$stmtCodigo->execute();
				$ultimoCodigo = $stmtCodigo->fetch();
				$nuevoCodigo = ($ultimoCodigo['ultimo_codigo'] ?? 0) + 1;
				$stmtHijo = $db->prepare("INSERT INTO productos(id_categoria, codigo, descripcion, imagen, stock, precio_compra, precio_venta, ventas) VALUES (:id_categoria, :codigo, :descripcion, :imagen, :stock, :precio_compra, :precio_venta, :ventas)");
				$precioCompraHijo = ($padreInfo['precio_compra'] > 0 && $cantidadResultante > 0) ? $padreInfo['precio_compra'] / $cantidadResultante : 0;
				$ventasInicial = 0;
				$stmtHijo->bindParam(":id_categoria", $padreInfo['id_categoria'], PDO::PARAM_INT);
				$stmtHijo->bindParam(":codigo", $nuevoCodigo, PDO::PARAM_STR);
				$stmtHijo->bindParam(":descripcion", $nombreParte, PDO::PARAM_STR);
				$stmtHijo->bindParam(":imagen", $padreInfo['imagen'], PDO::PARAM_STR);
				$stmtHijo->bindParam(":stock", $cantidadResultante, PDO::PARAM_INT);
				$stmtHijo->bindParam(":precio_compra", $precioCompraHijo, PDO::PARAM_STR);
				$stmtHijo->bindParam(":precio_venta", $precioParte, PDO::PARAM_STR);
				$stmtHijo->bindParam(":ventas", $ventasInicial, PDO::PARAM_INT);
				$stmtHijo->execute();
			}
			$db->commit();
			return "ok";
		} catch (Exception $e) {
			$db->rollBack();
			return "Error: " . $e->getMessage();
		}
	}
	/*=============================================
    MOSTRAR SUMA TOTAL DE VENTAS
    =============================================*/
    static public function mdlMostrarSumaVentas($tabla){
    
        $stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");
        $stmt -> execute();
        return $stmt -> fetch();
    
        $stmt = null;
    }
    /*=============================================
    MOSTRAR ÚLTIMO CÓDIGO DE UNA CATEGORÍA
    =============================================*/
    static public function mdlObtenerUltimoCodigo($tabla, $item, $valor){
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY codigo DESC LIMIT 1");
        $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    /*=============================================
    CONTAR PRODUCTOS POR CATEGORIA (MÉTODO FIABLE)
    =============================================*/
    static public function mdlContarProductosPorCategoria($tabla, $item, $valor){
        
        // Esta consulta usa COUNT(*) que es 100% precisa
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE $item = :$item");
        
        $stmt->bindParam(":".$item, $valor, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // fetchColumn() devuelve directamente el número del conteo (ej: '0', '5', etc.)
        return $stmt->fetchColumn();
    }


/*=============================================
ACTUALIZAR PRECIO POR DESCRIPCIÓN (VERSIÓN SÚPER ROBUSTA)
=============================================*/
static public function mdlActualizarPrecioPorDescripcion($tabla, $descripcion, $nuevoPrecio){

    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET precio_venta = :precio_venta WHERE UPPER(TRIM(descripcion)) = UPPER(TRIM(:descripcion))");

    $stmt->bindParam(":precio_venta", $nuevoPrecio, PDO::PARAM_STR);
    $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);

    if($stmt->execute()){
        return "ok";
    }else{
        return "error";
    }

    $stmt->close();
    $stmt = null;
}
/*=============================================
ACTUALIZAR STOCK POR TRANSFERENCIA (RESTAR)
=============================================*/
static public function mdlActualizarStock($tabla, $idProducto, $cantidad){
    
    // Esta consulta resta la cantidad directamente en la base de datos
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET stock = stock - :cantidad WHERE id = :id");

    $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);

    if($stmt->execute()){
        return "ok";
    }else{
        return "error";	
    }

    $stmt->close();
    $stmt = null;
}
/*=============================================
AGREGAR STOCK POR TRANSFERENCIA (SUMAR)
=============================================*/
static public function mdlAgregarStock($tabla, $idProducto, $cantidad){

    // Esta consulta suma la cantidad directamente en la base de datos
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET stock = stock + :cantidad WHERE id = :id");

    $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);

    if($stmt->execute()){
        return "ok";
    }else{
        return "error";	
    }

    $stmt->close();
    $stmt = null;
}
}