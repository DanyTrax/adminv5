<?php
/**
 * Entorno local AdminV5 (MySQL root sin password / Homebrew).
 * Uso: php scripts/local-setup.php
 */
$root = dirname(__DIR__);
$host = '127.0.0.1';
$rootUser = 'root';
$rootPass = '';

$dbLocal = 'adminv5_local';
$dbCentral = 'adminv5_central';
$dbUser = 'adminv5';
$dbPass = 'adminv5_local';

echo "== AdminV5 local setup ==\n";

try {
    $pdo = new PDO("mysql:host=$host", $rootUser, $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Throwable $e) {
    fwrite(STDERR, "No se pudo conectar a MySQL root: {$e->getMessage()}\n");
    exit(1);
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbLocal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbCentral` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'");
$pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'127.0.0.1' IDENTIFIED BY '$dbPass'");
$pdo->exec("GRANT ALL PRIVILEGES ON `$dbLocal`.* TO '$dbUser'@'localhost'");
$pdo->exec("GRANT ALL PRIVILEGES ON `$dbLocal`.* TO '$dbUser'@'127.0.0.1'");
$pdo->exec("GRANT ALL PRIVILEGES ON `$dbCentral`.* TO '$dbUser'@'localhost'");
$pdo->exec("GRANT ALL PRIVILEGES ON `$dbCentral`.* TO '$dbUser'@'127.0.0.1'");
$pdo->exec("FLUSH PRIVILEGES");
echo "OK bases y usuario $dbUser\n";

$local = new PDO("mysql:host=$host;dbname=$dbLocal;charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$central = new PDO("mysql:host=$host;dbname=$dbCentral;charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tablasLocal = [
"CREATE TABLE IF NOT EXISTS abonos_historial (
 id INT AUTO_INCREMENT PRIMARY KEY, id_venta INT NOT NULL, codigo_venta INT NOT NULL,
 monto_abono DECIMAL(10,2) NOT NULL, fecha_abono DATETIME NOT NULL, id_vendedor_abono INT NOT NULL,
 nombre_vendedor_abono VARCHAR(255) NOT NULL, medio_pago VARCHAR(50) NULL, observaciones TEXT NULL,
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS categorias (
 id INT AUTO_INCREMENT PRIMARY KEY, categoria TEXT NOT NULL, prefijo VARCHAR(10) NOT NULL DEFAULT '',
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS clientes (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT NOT NULL, documento VARCHAR(20) NOT NULL,
 email TEXT NOT NULL, telefono TEXT NOT NULL, direccion TEXT NOT NULL, fecha_nacimiento DATE NULL,
 compras INT NOT NULL DEFAULT 0, ultima_compra DATETIME NULL,
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS contabilidad (
 id INT AUTO_INCREMENT PRIMARY KEY, id_vendedor INT NOT NULL, fecha DATETIME NOT NULL,
 detalle TEXT NOT NULL, valor VARCHAR(100) NOT NULL, medio_pago VARCHAR(50) NOT NULL,
 forma_pago VARCHAR(50) NULL, factura VARCHAR(20) NULL, tipo VARCHAR(50) NOT NULL)",
"CREATE TABLE IF NOT EXISTS cotizaciones (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo INT NOT NULL, id_cliente INT NOT NULL, id_vendedor INT NOT NULL,
 productos TEXT NOT NULL, impuesto FLOAT NOT NULL, descuento INT DEFAULT 0, neto FLOAT NOT NULL, total FLOAT NOT NULL,
 detalle TEXT NOT NULL, metodo_pago TEXT NOT NULL, fecha_venta DATETIME NOT NULL, id_vend_abono INT NOT NULL DEFAULT 0,
 abono FLOAT NOT NULL DEFAULT 0, fecha_abono DATETIME NULL, pago TEXT NULL, Ult_abono FLOAT DEFAULT 0,
 medio_pago VARCHAR(50) NULL, images TEXT NULL)",
"CREATE TABLE IF NOT EXISTS medios_pago (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL)",
"CREATE TABLE IF NOT EXISTS productos (
 id INT AUTO_INCREMENT PRIMARY KEY, id_categoria INT NOT NULL, parent_id INT NULL,
 codigo TEXT NOT NULL, codigo_maestro VARCHAR(50) NULL, descripcion TEXT NOT NULL, imagen TEXT NOT NULL,
 stock INT NOT NULL DEFAULT 0, precio_compra FLOAT NOT NULL DEFAULT 0, precio_venta FLOAT NOT NULL DEFAULT 0, ventas INT NOT NULL DEFAULT 0,
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 es_divisible TINYINT(1) DEFAULT 0, nombre_mitad VARCHAR(255) NULL, precio_mitad DECIMAL(10,2) NULL,
 nombre_tercio VARCHAR(255) NULL, precio_tercio DECIMAL(10,2) NULL,
 nombre_cuarto VARCHAR(255) NULL, precio_cuarto DECIMAL(10,2) NULL)",
"CREATE TABLE IF NOT EXISTS sucursal_local (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo_sucursal VARCHAR(20) NOT NULL, nombre VARCHAR(255) NOT NULL,
 direccion TEXT NULL, telefono VARCHAR(50) NULL, email VARCHAR(255) NULL,
 usuario_bd VARCHAR(50) NULL, password_bd VARCHAR(255) NULL, nombre_bd VARCHAR(100) NULL,
 host_bd VARCHAR(255) NULL, puerto_bd INT DEFAULT 3306,
 url_base VARCHAR(255) NOT NULL, url_api VARCHAR(255) NOT NULL, url_central VARCHAR(255) NULL,
 importar_medios_pago TINYINT(1) DEFAULT 0, es_principal TINYINT(1) DEFAULT 0, activo TINYINT(1) DEFAULT 1,
 registrada_en_central TINYINT(1) DEFAULT 0,
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT NOT NULL, usuario TEXT NOT NULL, password TEXT NOT NULL,
 perfil TEXT NOT NULL, foto TEXT NOT NULL, estado INT NOT NULL DEFAULT 1, ultimo_login DATETIME NULL,
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 empresa TEXT NOT NULL, telefono TEXT NULL, direccion TEXT NULL)",
"CREATE TABLE IF NOT EXISTS venta_productos (
 id INT AUTO_INCREMENT PRIMARY KEY, id_venta INT NOT NULL, descripcion VARCHAR(255) NOT NULL,
 cantidad INT NOT NULL, total DECIMAL(10,2) NOT NULL)",
"CREATE TABLE IF NOT EXISTS ventas (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo INT NOT NULL, id_cliente INT NOT NULL, id_vendedor INT NOT NULL,
 productos TEXT NOT NULL, impuesto FLOAT NOT NULL, descuento INT DEFAULT 0, neto FLOAT NOT NULL, total FLOAT NOT NULL,
 detalle TEXT NOT NULL, metodo_pago TEXT NOT NULL, fecha_venta DATETIME NOT NULL, id_vend_abono INT DEFAULT 0,
 abono FLOAT DEFAULT 0, fecha_abono DATETIME NULL, pago TEXT NULL, Ult_abono FLOAT DEFAULT 0, medio_pago VARCHAR(50) NULL)",
"CREATE TABLE IF NOT EXISTS salidas_inventario (
 id INT AUTO_INCREMENT PRIMARY KEY, id_producto INT NOT NULL, id_usuario INT NOT NULL,
 cantidad DECIMAL(10,2) NOT NULL, descripcion TEXT NULL, numero_remision VARCHAR(255) NULL,
 fecha_salida DATETIME DEFAULT CURRENT_TIMESTAMP)",
];

foreach ($tablasLocal as $sql) {
    $local->exec($sql);
}
echo "OK tablas locales\n";

$tablasCentral = [
"CREATE TABLE IF NOT EXISTS sucursales (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo_sucursal VARCHAR(20) NOT NULL, nombre VARCHAR(255) NOT NULL,
 url_base VARCHAR(255) NULL, url_api VARCHAR(255) NULL, activo TINYINT(1) DEFAULT 1,
 usuario_bd VARCHAR(100) NULL, password_bd VARCHAR(255) NULL, nombre_bd VARCHAR(100) NULL,
 host_bd VARCHAR(255) NULL, puerto_bd INT DEFAULT 3306)",
"CREATE TABLE IF NOT EXISTS usuarios_central (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255) NOT NULL, usuario VARCHAR(100) NOT NULL UNIQUE,
 password TEXT NOT NULL, perfil VARCHAR(50) NOT NULL, estado INT DEFAULT 1, empresa TEXT NULL,
 telefono VARCHAR(50) NULL, direccion TEXT NULL, foto TEXT NULL)",
"CREATE TABLE IF NOT EXISTS clientes_central (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT NOT NULL, documento VARCHAR(20) NOT NULL,
 email TEXT NULL, telefono TEXT NULL, direccion TEXT NULL)",
"CREATE TABLE IF NOT EXISTS categorias (
 id INT AUTO_INCREMENT PRIMARY KEY, categoria TEXT NOT NULL, prefijo VARCHAR(10) DEFAULT '')",
"CREATE TABLE IF NOT EXISTS catalogo_maestro (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(50) NOT NULL, descripcion TEXT NOT NULL,
 id_categoria INT NULL, precio_venta FLOAT DEFAULT 0, stock INT DEFAULT 0, imagen TEXT NULL)",
"CREATE TABLE IF NOT EXISTS medios_pago_central (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, activo TINYINT(1) DEFAULT 1)",
"CREATE TABLE IF NOT EXISTS despachos (
 id INT AUTO_INCREMENT PRIMARY KEY, numero_despacho VARCHAR(50) NOT NULL, estado VARCHAR(50) DEFAULT 'pendiente',
 sucursal_origen INT NULL, sucursal_destino INT NULL, transportador_id INT NULL,
 id_solicitud_origen INT NULL, numero_solicitud VARCHAR(50) NULL, usuario_creador INT NULL,
 fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, fecha_actualizacion DATETIME NULL, observaciones TEXT NULL)",
"CREATE TABLE IF NOT EXISTS solicitudes_stock (
 id INT AUTO_INCREMENT PRIMARY KEY, numero_solicitud VARCHAR(50) NOT NULL, estado VARCHAR(50) DEFAULT 'pendiente',
 sucursal_solicitante INT NULL, fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS stock_transito (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo_producto VARCHAR(50) NOT NULL, descripcion TEXT NULL,
 descripcion_producto TEXT NULL, cantidad INT NOT NULL DEFAULT 0, cantidad_disponible INT NOT NULL DEFAULT 0,
 cantidad_original INT NOT NULL DEFAULT 0, transportador_id INT NULL, nombre_transportador VARCHAR(255) NULL,
 transportador_nombre VARCHAR(255) NULL, sucursal_origen INT NULL, sucursal_destino INT NULL,
 numero_despacho VARCHAR(50) NULL, id_despacho INT NULL, id_solicitud_origen INT NULL,
 numero_solicitud VARCHAR(50) NULL, estado VARCHAR(50) DEFAULT 'en_transito',
 fecha_ingreso DATETIME NULL, fecha_actualizacion DATETIME NULL)",
"CREATE TABLE IF NOT EXISTS personalizacion_colores (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NULL, activo TINYINT(1) DEFAULT 0,
 id_sucursal INT NULL, nombre_sucursal VARCHAR(255) NULL, colores_json TEXT NULL)",
"CREATE TABLE IF NOT EXISTS personalizacion_cotizaciones (
 id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NULL, activo TINYINT(1) DEFAULT 0, config_json TEXT NULL)",
"CREATE TABLE IF NOT EXISTS registro_descargas_stock_transito (
 id INT AUTO_INCREMENT PRIMARY KEY, codigo_producto VARCHAR(50) NOT NULL, descripcion_producto TEXT NULL,
 cantidad_descargada INT NOT NULL, usuario_id INT NULL, usuario_nombre VARCHAR(255) NULL,
 sucursal_id INT NULL, sucursal_nombre VARCHAR(255) NULL, transportador_id INT NULL,
 transportador_nombre VARCHAR(255) NULL, numero_despacho VARCHAR(50) NULL, observaciones TEXT NULL,
 fecha_descarga DATETIME DEFAULT CURRENT_TIMESTAMP, ip_usuario VARCHAR(50) NULL,
 user_agent TEXT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
];

foreach ($tablasCentral as $i => $sql) {
    try {
        $central->exec($sql);
    } catch (Throwable $e) {
        // Si la tabla ya existe con esquema parcial, recrear
        if (preg_match('/CREATE TABLE IF NOT EXISTS\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $tm)) {
            $central->exec("DROP TABLE IF EXISTS `{$tm[1]}`");
            $central->exec($sql);
            echo "WARN recreada tabla central {$tm[1]}\n";
        } else {
            throw $e;
        }
    }
}
echo "OK tablas centrales\n";

$pass = crypt('admin', '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
$url = 'http://127.0.0.1:8080/';

$local->exec("DELETE FROM usuarios");
$local->exec("DELETE FROM sucursal_local");
$local->exec("DELETE FROM categorias");
$local->exec("DELETE FROM medios_pago");
$local->exec("DELETE FROM productos");
$local->exec("DELETE FROM clientes");

$stmt = $local->prepare("INSERT INTO sucursal_local (codigo_sucursal,nombre,direccion,telefono,email,usuario_bd,password_bd,nombre_bd,host_bd,puerto_bd,url_base,url_api,url_central,es_principal,activo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,1)");
$stmt->execute(['LOCAL01','Sucursal Local Dev','Calle Local 1','3000000000','dev@local.test',$dbUser,$dbPass,$dbLocal,$host,3306,$url,$url.'api-transferencias/',$url,]);

$stmt = $local->prepare("INSERT INTO usuarios (nombre,usuario,password,perfil,foto,estado,ultimo_login,empresa) VALUES (?,?,?,?,?,1,NOW(),?)");
$stmt->execute(['Administrador Local','admin',$pass,'Administrador','vistas/img/usuarios/default/anonymous.png','Sucursal Local Dev']);

// perfiles extra para pruebas de menú
foreach ([['Vendedor Local','vendedor','Vendedor'],['Contador Local','contador','Contador'],['Especial Local','especial','Especial'],['Transportador Local','transportador','Transportador']] as $u) {
    $stmt->execute([$u[0], $u[1], $pass, $u[2], 'vistas/img/usuarios/default/anonymous.png', 'Sucursal Local Dev']);
}

$local->exec("INSERT INTO categorias (categoria, prefijo) VALUES ('General','GEN'),('Accesorios','ACC')");
$local->exec("INSERT INTO medios_pago (nombre) VALUES ('Efectivo'),('Transferencia')");
$local->exec("INSERT INTO productos (id_categoria,codigo,descripcion,imagen,stock,precio_venta,ventas) VALUES (1,'GEN001','Producto demo','vistas/img/productos/default/anonymous.png',100,15000,0)");
$local->exec("INSERT INTO clientes (nombre,documento,email,telefono,direccion,fecha_nacimiento,compras,ultima_compra) VALUES ('Cliente Demo','123456789','cliente@demo.test','3001112233','Dir demo','1990-01-01',0,NOW())");

$central->exec("DELETE FROM sucursales");
$central->exec("INSERT INTO sucursales (codigo_sucursal,nombre,url_base,url_api,activo,usuario_bd,password_bd,nombre_bd,host_bd,puerto_bd) VALUES ('LOCAL01','Sucursal Local Dev','$url','{$url}api-transferencias/',1,'$dbUser','$dbPass','$dbLocal','$host',3306)");
$central->exec("INSERT INTO medios_pago_central (nombre,activo) VALUES ('Efectivo',1),('Transferencia',1)");

// config.database.php local
file_put_contents($root . '/config.database.php', <<<PHP
<?php
/** Entorno LOCAL generado por scripts/local-setup.php */
if (!defined('DB_LOCAL_HOST')) {
    define('DB_LOCAL_HOST', '$host');
    define('DB_LOCAL_NAME', '$dbLocal');
    define('DB_LOCAL_USER', '$dbUser');
    define('DB_LOCAL_PASS', '$dbPass');
}
if (!defined('DB_CENTRAL_HOST')) {
    define('DB_CENTRAL_HOST', '$host');
    define('DB_CENTRAL_NAME', '$dbCentral');
    define('DB_CENTRAL_USER', '$dbUser');
    define('DB_CENTRAL_PASS', '$dbPass');
}
PHP);

// config.php API local
$config = file_get_contents($root . '/config.php');
if ($config !== false) {
    $config = preg_replace("/define\\('API_URL'[^;]+;/", "define('API_URL', '{$url}api-transferencias/');", $config);
    $config = preg_replace("/define\\('NOMBRE_SUCURSAL'[^;]+;/", "define('NOMBRE_SUCURSAL', 'Sucursal Local Dev');", $config);
    file_put_contents($root . '/config.php', $config);
}

echo "OK seed datos\n";
echo "Login: usuario=admin password=admin\n";
echo "Otros: vendedor/contador/especial/transportador (password=admin)\n";
echo "URL: $url\n";
echo "Listo.\n";
