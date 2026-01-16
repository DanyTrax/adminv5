<?php

require_once __DIR__ . "/conexion.php";

class ModeloSalidasInventario{

	/*=============================================
	MOSTRAR SALIDAS DE INVENTARIO
	=============================================*/

	static public function mdlMostrarSalidasInventario($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT 
				s.*,
				u.nombre as usuario_nombre,
				p.descripcion as producto_nombre,
				p.codigo as producto_codigo
			FROM $tabla s
			LEFT JOIN usuarios u ON s.id_usuario = u.id
			LEFT JOIN productos p ON s.id_producto = p.id
			ORDER BY s.fecha_salida DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	FILTRAR SALIDAS DE INVENTARIO POR FECHA
	=============================================*/
	static public function mdlFilterBy($tabla, $fechaInicial, $fechaFinal) {
		$sql = "SELECT 
			s.*,
			u.nombre as usuario_nombre,
			p.descripcion as producto_nombre,
			p.codigo as producto_codigo
		FROM $tabla s
		LEFT JOIN usuarios u ON s.id_usuario = u.id
		LEFT JOIN productos p ON s.id_producto = p.id
		WHERE 1=1";
		
		$params = [];
		
		if ($fechaInicial != null && $fechaFinal != null) {
			// Se usa DATE() para comparar solo la fecha
			if($fechaInicial == $fechaFinal){
				$sql .= " AND DATE(s.fecha_salida) = :fecha";
				$params[":fecha"] = $fechaInicial;
			} else {
				$sql .= " AND DATE(s.fecha_salida) BETWEEN :fechaInicial AND :fechaFinal";
				$params[":fechaInicial"] = $fechaInicial;
				$params[":fechaFinal"] = $fechaFinal;
			}
		}
		
		$sql .= " ORDER BY s.fecha_salida DESC";
		
		$stmt = Conexion::conectar()->prepare($sql);
		
		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value);
		}
		
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	CREAR SALIDA DE INVENTARIO
	=============================================*/

	static public function mdlCrearSalidaInventario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_usuario, id_producto, cantidad, descripcion, numero_remision) VALUES (:id_usuario, :id_producto, :cantidad, :descripcion, :numero_remision)");

		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		$stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
		$stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":numero_remision", $datos["numero_remision"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	ELIMINAR SALIDA DE INVENTARIO
	=============================================*/

	static public function mdlEliminarSalidaInventario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	BUSCAR PRODUCTOS PARA AJAX
	=============================================*/

	static public function mdlBuscarProductos($busqueda){

		$stmt = Conexion::conectar()->prepare("SELECT 
			id,
			codigo,
			descripcion,
			stock,
			precio_venta
		FROM productos 
		WHERE (codigo LIKE :busqueda OR descripcion LIKE :busqueda)
		AND stock > 0
		ORDER BY descripcion ASC
		LIMIT 10");

		$busqueda = "%".$busqueda."%";
		$stmt -> bindParam(":busqueda", $busqueda, PDO::PARAM_STR);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	BUSCAR REMISIONES/FACTURAS PARA AJAX
	=============================================*/

	static public function mdlBuscarRemisiones($busqueda){

		$stmt = Conexion::conectar()->prepare("SELECT 
			v.id,
			v.codigo,
			v.total,
			v.fecha_venta,
			v.metodo_pago,
			c.nombre as cliente_nombre,
			u.nombre as vendedor_nombre
		FROM ventas v
		LEFT JOIN clientes c ON v.id_cliente = c.id
		LEFT JOIN usuarios u ON v.id_vendedor = u.id
		WHERE v.codigo LIKE :busqueda
		ORDER BY v.codigo DESC
		LIMIT 10");

		$busqueda = "%".$busqueda."%";
		$stmt -> bindParam(":busqueda", $busqueda, PDO::PARAM_STR);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

}
