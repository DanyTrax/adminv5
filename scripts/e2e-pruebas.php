<?php
/**
 * Suite E2E AdminV5 contra http://127.0.0.1:8080
 * Uso: php scripts/e2e-pruebas.php
 */
$base = getenv('ADMINV5_BASE') ?: 'http://127.0.0.1:8080';
$cookie = sys_get_temp_dir() . '/adminv5_e2e_' . getmypid() . '.txt';
@unlink($cookie);

$results = [];
$fail = 0;

function assert_test(string $name, bool $ok, string $detail = ''): void {
    global $results, $fail;
    $results[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
    echo ($ok ? '[OK] ' : '[FAIL] ') . $name . ($detail ? " — $detail" : '') . "\n";
    if (!$ok) $fail++;
}

function http(string $url, string $cookie, ?array $post = null, bool $multipart = false): array {
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HEADER => true,
    ];
    if ($post !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $multipart ? $post : http_build_query($post);
    }
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr((string) $raw, 0, $headerSize);
    $body = substr((string) $raw, $headerSize);
    return [$code, $body, $headers];
}

function db() {
    static $pdo = null;
    if (!$pdo) {
        require_once dirname(__DIR__) . '/config.database.php';
        $pdo = new PDO(
            'mysql:host=' . DB_LOCAL_HOST . ';dbname=' . DB_LOCAL_NAME . ';charset=utf8mb4',
            DB_LOCAL_USER,
            DB_LOCAL_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function dbCentral() {
    static $pdo = null;
    if (!$pdo) {
        require_once dirname(__DIR__) . '/config.database.php';
        $pdo = new PDO(
            'mysql:host=' . DB_CENTRAL_HOST . ';dbname=' . DB_CENTRAL_NAME . ';charset=utf8mb4',
            DB_CENTRAL_USER,
            DB_CENTRAL_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function noFatal(string $body): bool {
    return stripos($body, 'Fatal error') === false
        && stripos($body, 'Uncaught') === false
        && stripos($body, 'Parse error') === false;
}

echo "=== E2E AdminV5 @ $base ===\n";

// 0) Salud
[$c, $b] = http("$base/", $cookie);
assert_test('Servidor responde', $c === 200 && noFatal($b), "HTTP $c");

// 1) Login
[$c, $b] = http("$base/", $cookie, ['ingUsuario' => 'admin', 'ingPassword' => 'admin']);
$loginOk = $c === 200 && (str_contains($b, 'window.location = "inicio"') || str_contains($b, 'sidebar-menu'));
assert_test('Login admin', $loginOk, $loginOk ? 'sesión' : 'sin redirect');

[$c, $b] = http("$base/inicio", $cookie);
assert_test('Página inicio', $c === 200 && str_contains($b, 'sidebar-menu') && noFatal($b), "HTTP $c");

// 2) Categoría
[$c, $b] = http("$base/categorias", $cookie, [
    'nuevaCategoria' => 'Cat E2E ' . time(),
]);
$catId = (int) db()->query("SELECT id FROM categorias ORDER BY id DESC LIMIT 1")->fetchColumn();
assert_test('Crear categoría', $c === 200 && noFatal($b) && $catId > 0, "id=$catId");

// 3) Producto (puede fallar si falta precio_compra en BD)
$codeProd = 'E2E' . substr((string) time(), -6);
[$c, $b] = http("$base/productos", $cookie, [
    'nuevaCategoria' => (string) max(1, $catId),
    'nuevoCodigo' => $codeProd,
    'nuevaDescripcion' => 'Producto E2E ' . $codeProd,
    'nuevoStock' => '50',
    'nuevoPrecioVenta' => '25000',
]);
$prod = db()->query("SELECT * FROM productos WHERE codigo=" . db()->quote($codeProd))->fetch(PDO::FETCH_ASSOC);
$prodOk = $prod !== false;
assert_test('Crear producto', $c === 200 && noFatal($b) && $prodOk, $prodOk ? "id={$prod['id']} stock={$prod['stock']}" : substr(strip_tags($b), 0, 180));

if (!$prodOk) {
    // fallback insert directo para no bloquear venta
    db()->prepare("INSERT INTO productos (id_categoria,codigo,descripcion,imagen,stock,precio_venta,ventas) VALUES (?,?,?,?,?,?,0)")
        ->execute([max(1, $catId), $codeProd, 'Producto E2E ' . $codeProd, 'vistas/img/productos/default/anonymous.png', 50, 25000]);
    $prod = db()->query("SELECT * FROM productos WHERE codigo=" . db()->quote($codeProd))->fetch(PDO::FETCH_ASSOC);
    assert_test('Producto fallback seed', (bool) $prod, 'insert SQL directo');
}

$prodId = (int) $prod['id'];
$stockAntes = (int) $prod['stock'];

// 4) Cliente
[$c, $b] = http("$base/clientes", $cookie, [
    'nuevoCliente' => 'Cliente E2E Test',
    'nuevoDocumentoId' => '1098765432',
    'nuevoEmail' => 'e2e@test.local',
    'nuevoTelefono' => '3001234567',
    'nuevaDireccion' => 'Calle 1 # 2-3',
    'nuevaFechaNacimiento' => '1990-05-05',
]);
$cli = db()->query("SELECT * FROM clientes WHERE documento='1098765432' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert_test('Crear cliente', $c === 200 && noFatal($b) && (bool) $cli, $cli ? "id={$cli['id']}" : 'no insert');
$cliId = $cli ? (int) $cli['id'] : 1;

// 5) Medio de pago
[$c, $b] = http("$base/medios-pago", $cookie, [
    'nuevoMedioPago' => 'Nequi E2E',
]);
$mp = db()->query("SELECT id FROM medios_pago WHERE nombre LIKE 'Nequi%' ORDER BY id DESC LIMIT 1")->fetchColumn();
assert_test('Crear medio de pago', $c === 200 && noFatal($b) && (bool) $mp, $mp ? "id=$mp" : 'no insert');

// 6) Venta
$lista = json_encode([[
    'id' => (string) $prodId,
    'descripcion' => $prod['descripcion'],
    'cantidad' => '2',
    'stock' => (string) $stockAntes,
    'precio' => '25000',
    'total' => '50000',
]], JSON_UNESCAPED_UNICODE);

$vendedorId = (int) db()->query("SELECT id FROM usuarios WHERE usuario='admin' LIMIT 1")->fetchColumn();
[$c, $b] = http("$base/crear-venta", $cookie, [
    'nuevaVenta' => '20001',
    'idVendedor' => (string) $vendedorId,
    'seleccionarCliente' => (string) $cliId,
    'listaProductos' => $lista,
    'nuevoImpuestoVenta' => '0',
    'nuevoPrecioImpuesto' => '0',
    'nuevoDescuentoVenta' => '0',
    'nuevoPrecioDescuento' => '0',
    'nuevoPrecioNeto' => '50000',
    'nuevoTotalVenta' => '50000',
    'totalVenta' => '50000',
    'detalle' => 'Venta E2E automatica',
    'nuevoMetodoPago' => 'Completo',
    'nuevoMedioPago' => 'Efectivo',
    'pago' => '',
]);
$venta = db()->query("SELECT * FROM ventas ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$stockDespues = (int) db()->query("SELECT stock FROM productos WHERE id=$prodId")->fetchColumn();
$ventaOk = $venta && (float) $venta['total'] == 50000.0;
$stockOk = $stockDespues === ($stockAntes - 2);
assert_test('Crear venta', $c === 200 && noFatal($b) && $ventaOk, $venta ? "venta id={$venta['id']} codigo={$venta['codigo']}" : substr(strip_tags($b), 0, 200));
assert_test('Stock descontado en venta', $stockOk, "antes=$stockAntes despues=$stockDespues");

// 7) Contabilidad generada
$conta = db()->query("SELECT COUNT(*) FROM contabilidad WHERE detalle LIKE '%Venta factura%'")->fetchColumn();
assert_test('Asiento contable de venta', (int) $conta > 0, "registros=$conta");

// 8) Páginas clave sin fatal
$pages = [
    'productos', 'clientes', 'ventas', 'categorias', 'medios-pago', 'sucursales',
    'cotizacion', 'crear-cotizacion', 'gastos', 'entradas', 'reportes', 'contabilidad',
    'salidas-inventario', 'solicitudes-stock', 'despachos', 'stock-transito',
    'usuarios', 'usuarios-central', 'clientes-central', 'categorias-central',
    'catalogo-maestro', 'medios-pago-central', 'productos-stock-sucursales',
];
$pageFails = [];
foreach ($pages as $p) {
    [$c, $b] = http("$base/$p", $cookie);
    if ($c !== 200 || !noFatal($b)) {
        $pageFails[] = "$p(HTTP$c)";
    }
}
assert_test('Páginas módulo sin Fatal', count($pageFails) === 0, $pageFails ? implode(', ', $pageFails) : count($pages) . ' OK');

// 9) AJAX datatables
$ajax = [
    'ajax/datatable-productos.ajax.php',
    'ajax/datatable-ventas.ajax.php',
    'ajax/datatable-despachos.ajax.php',
    'ajax/datatable-solicitudes-stock.ajax.php',
    'ajax/datatable-stock-transito.ajax.php',
    'ajax/datatable-contabilidad.ajax.php',
];
$ajaxFails = [];
foreach ($ajax as $a) {
    [$c, $b] = http("$base/$a", $cookie, []);
    $j = json_decode($b, true);
    $hasError = is_array($j) && !empty($j['error']);
    if ($c !== 200 || !noFatal($b) || $hasError || !is_array($j) || !array_key_exists('data', $j)) {
        $ajaxFails[] = $a . ($hasError ? "({$j['error']})" : "");
    }
}
assert_test('AJAX DataTables', count($ajaxFails) === 0, $ajaxFails ? implode(' | ', $ajaxFails) : 'todos OK');

// 10) Sucursales (listado + BD central)
[$c, $b] = http("$base/sucursales", $cookie);
$suc = dbCentral()->query("SELECT COUNT(*) FROM sucursales")->fetchColumn();
assert_test('Módulo sucursales', $c === 200 && noFatal($b) && (int) $suc >= 1, "sucursales_central=$suc");

// 11) Cotización mínima si el controlador lo permite
[$c, $b] = http("$base/crear-cotizacion", $cookie);
assert_test('Página crear cotización', $c === 200 && noFatal($b), "HTTP $c");

// 12) Perfil vendedor ACL
$cookie2 = sys_get_temp_dir() . '/adminv5_e2e_vend_' . getmypid() . '.txt';
@unlink($cookie2);
http("$base/", $cookie2, ['ingUsuario' => 'vendedor', 'ingPassword' => 'admin']);
[$c, $b] = http("$base/ventas", $cookie2);
[$c2, $b2] = http("$base/usuarios", $cookie2);
$vendVentas = $c === 200 && str_contains($b, 'Ventas') || str_contains($b, 'ventas') || str_contains($b, 'crear-venta');
$vendNoUsers = $c2 === 200 && !str_contains($b2, 'btnAgregarUsuario') && !str_contains($b2, 'tablaUsuarios');
assert_test('ACL vendedor ve ventas', $vendVentas || str_contains($b, 'sidebar-menu'), 'ventas');
assert_test('ACL vendedor no administra usuarios', $vendNoUsers, $vendNoUsers ? 'bloqueado/redirigido' : 'expuso usuarios');

// 13) Seguridad: no password_bd
[$c, $b] = http("$base/ajax/obtener-sucursal-actual.ajax.php?accion=obtener_sucursal_actual", $cookie);
assert_test('AJAX sucursal sin password_bd', $c === 200 && !str_contains($b, 'password_bd'), substr($b, 0, 120));

// 14) Gasto
[$c, $b] = http("$base/crear-gastos", $cookie, [
    'nuevoGasto' => '1',
    'idVendedor' => (string) $vendedorId,
    'fecha' => date('Y-m-d'),
    'detalle' => 'Gasto E2E prueba',
    'valor' => '15000',
    'nuevoMedioPago' => 'Efectivo',
]);
$gasto = db()->query("SELECT id FROM contabilidad WHERE tipo='Gasto' AND detalle LIKE 'Gasto E2E%' ORDER BY id DESC LIMIT 1")->fetchColumn();
assert_test('Crear gasto', $c === 200 && noFatal($b) && (bool) $gasto, $gasto ? "id=$gasto" : 'no insert');

// 15) Entrada (antes estaba en modo debug y no guardaba)
[$c, $b] = http("$base/crear-entradas", $cookie, [
    'nuevoEntrada' => '1',
    'idVendedor' => (string) $vendedorId,
    'fecha' => date('Y-m-d'),
    'descripcion' => 'Entrada E2E prueba',
    'valor' => '20000',
    'nuevoMedioPago' => 'Efectivo',
]);
$entrada = db()->query("SELECT id FROM contabilidad WHERE tipo='Entrada' AND detalle LIKE 'Entrada E2E%' ORDER BY id DESC LIMIT 1")->fetchColumn();
assert_test('Crear entrada', $c === 200 && noFatal($b) && (bool) $entrada, $entrada ? "id=$entrada" : substr(strip_tags($b), 0, 160));

// 16) Salida inventario
$stockPreSalida = (int) db()->query("SELECT stock FROM productos WHERE id=$prodId")->fetchColumn();
[$c, $b] = http("$base/salidas-inventario", $cookie, [
    'nuevaCantidad' => '3',
    'nuevoProducto' => (string) $prodId,
    'nuevoUsuario' => (string) $vendedorId,
    'nuevaDescripcion' => 'Salida E2E',
    'nuevaRemision' => 'REM-E2E-1',
]);
$salida = db()->query("SELECT id FROM salidas_inventario ORDER BY id DESC LIMIT 1")->fetchColumn();
$stockPostSalida = (int) db()->query("SELECT stock FROM productos WHERE id=$prodId")->fetchColumn();
assert_test('Crear salida inventario', $c === 200 && noFatal($b) && (bool) $salida, $salida ? "id=$salida" : 'no insert');
assert_test('Stock tras salida', $stockPostSalida === $stockPreSalida - 3, "antes=$stockPreSalida despues=$stockPostSalida");

// 17) Listado ventas refleja venta creada
[$c, $b] = http("$base/ventas", $cookie);
assert_test('Listado ventas carga', $c === 200 && noFatal($b) && str_contains($b, 'ventas'), "HTTP $c");

// 18) Usuarios página admin
[$c, $b] = http("$base/usuarios", $cookie);
assert_test('Módulo usuarios admin', $c === 200 && noFatal($b) && (str_contains($b, 'Usuarios') || str_contains($b, 'usuarios')), "HTTP $c");

// 19) Cotización
$listaCot = json_encode([[
    'id' => (string) $prodId,
    'descripcion' => $prod['descripcion'],
    'cantidad' => '1',
    'stock' => (string) max(1, $stockDespues),
    'precio' => '25000',
    'total' => '25000',
]], JSON_UNESCAPED_UNICODE);
[$c, $b] = http("$base/crear-cotizacion", $cookie, [
    'nuevaCotizacion' => '30001',
    'idVendedor' => (string) $vendedorId,
    'seleccionarCliente' => (string) $cliId,
    'listaProductos' => $listaCot,
    'nuevoImpuestoVenta' => '0',
    'nuevoPrecioImpuesto' => '0',
    'nuevoDescuentoVenta' => '0',
    'nuevoPrecioDescuento' => '0',
    'nuevoPrecioNeto' => '25000',
    'nuevoTotalVenta' => '25000',
    'totalVenta' => '25000',
    'detalle' => 'Cotizacion E2E suite',
    'nuevoMetodoPago' => 'Completo',
    'nuevoMedioPago' => 'EFECTIVO',
    'pago' => '',
]);
$cot = db()->query("SELECT id,total FROM cotizaciones WHERE detalle LIKE 'Cotizacion E2E%' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert_test('Crear cotización', $c === 200 && noFatal($b) && (bool) $cot, $cot ? "id={$cot['id']} total={$cot['total']}" : 'no insert');

// Reporte
$ok = count(array_filter($results, fn($r) => $r['ok']));
$total = count($results);
$md = "# Resultado E2E AdminV5\n\nFecha: " . date('c') . "\nBase: `$base`\n\n**$ok / $total OK**\n\n| Prueba | Estado | Detalle |\n|--------|--------|---------|\n";
foreach ($results as $r) {
    $md .= '| ' . $r['name'] . ' | ' . ($r['ok'] ? 'OK' : 'FAIL') . ' | ' . str_replace('|', '/', $r['detail']) . " |\n";
}
$out = dirname(__DIR__) . '/docs/RESULTADO-E2E.md';
file_put_contents($out, $md);
echo "\n=== RESUMEN: $ok/$total OK ===\nEscrito: $out\n";
exit($fail > 0 ? 1 : 0);
