<?php
/**
 * Revisión punto a punto: menú + cabezote + whitelist plantilla.
 * Genera docs/REVISION-PUNTO-A-PUNTO.md y opcionalmente prueba HTTP.
 *
 * Uso:
 *   php scripts/review-modulos.php
 *   php scripts/review-modulos.php --http=http://127.0.0.1:8080 --user=admin --pass=admin
 */
$root = dirname(__DIR__);
$httpBase = null;
$user = 'admin';
$pass = 'admin';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--http=')) $httpBase = rtrim(substr($arg, 7), '/');
    if (str_starts_with($arg, '--user=')) $user = substr($arg, 7);
    if (str_starts_with($arg, '--pass=')) $pass = substr($arg, 7);
}

$plantilla = file_get_contents("$root/vistas/plantilla.php");
preg_match('/\$routes\s*=\s*\[(.*?)\];/s', $plantilla, $m);
preg_match_all('/"([a-z0-9-]+)"\s*=>\s*\[([^\]]*)\]/', $m[1] ?? '', $rm, PREG_SET_ORDER);
$routes = [];
foreach ($rm as $row) {
    preg_match_all('/"([^"]+)"/', $row[2], $perf);
    $routes[$row[1]] = $perf[1];
}

function extractHrefs(string $file): array {
    if (!file_exists($file)) return [];
    $t = file_get_contents($file);
    preg_match_all('/href=["\']([a-z0-9-]+)["\']/', $t, $m);
    return array_values(array_unique($m[1]));
}

$menuHrefs = extractHrefs("$root/vistas/modulos/menu.php");
$cabezoteHrefs = extractHrefs("$root/vistas/modulos/cabezote.php");
$nav = array_values(array_unique(array_merge($menuHrefs, $cabezoteHrefs, ['inicio','salir'])));

$jsMap = [];
if (preg_match('/\$jsPorRuta\s*=\s*\[(.*?)\];/s', $plantilla, $jm)) {
    preg_match_all('/"([a-z0-9-]+)"\s*=>\s*\[([^\]]*)\]/', $jm[1], $jrows, PREG_SET_ORDER);
    foreach ($jrows as $jr) {
        preg_match_all('/"([^"]+\.js)"/', $jr[2], $files);
        $jsMap[$jr[1]] = $files[1];
    }
}

$cookieFile = sys_get_temp_dir() . '/adminv5_review_cookies.txt';
$httpResults = [];
if ($httpBase) {
    @unlink($cookieFile);
    // login
    $ch = curl_init($httpBase . '/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['ingUsuario' => $user, 'ingPassword' => $pass]),
        CURLOPT_TIMEOUT => 20,
    ]);
    $loginBody = curl_exec($ch);
    $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $loggedIn = is_string($loginBody) && (str_contains($loginBody, 'sidebar-menu') || str_contains($loginBody, 'main-sidebar') || str_contains($loginBody, 'Gestion'));
    $httpResults['__login__'] = ['code' => $loginCode, 'ok' => $loggedIn, 'note' => $loggedIn ? 'sesión OK' : 'posible fallo login'];

    foreach ($nav as $ruta) {
        $url = $httpBase . '/' . $ruta;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ok = $code >= 200 && $code < 400 && is_string($body) && !str_contains($body, 'Fatal error') && !str_contains($body, 'Uncaught');
        $is404 = is_string($body) && (str_contains($body, '404') || str_contains(strtolower($body), 'página no encontrada'));
        $httpResults[$ruta] = [
            'code' => $code,
            'ok' => $ok && !$is404,
            'note' => !$ok ? 'error PHP/HTTP' : ($is404 ? '404 UI' : 'OK'),
        ];
    }
}

$lines = [];
$lines[] = '# Revisión punto a punto — AdminV5';
$lines[] = '';
$lines[] = 'Generado: ' . date('Y-m-d H:i:s');
$lines[] = $httpBase ? "HTTP base: `$httpBase`" : 'HTTP: no ejecutado (solo estático)';
$lines[] = '';
$lines[] = '## Resumen navegación';
$lines[] = '';
$lines[] = '| Opción | En whitelist | Vista PHP | JS ruta | HTTP | Nota |';
$lines[] = '|--------|--------------|-----------|---------|------|------|';

$hallazgos = [];
foreach ($nav as $ruta) {
    $inRoutes = array_key_exists($ruta, $routes);
    $vista = file_exists("$root/vistas/modulos/$ruta.php");
    $js = $jsMap[$ruta] ?? [];
    $jsOk = true;
    foreach ($js as $f) {
        if (!file_exists("$root/vistas/js/$f")) { $jsOk = false; }
    }
    $http = $httpResults[$ruta] ?? null;
    $httpCol = $http ? (($http['ok'] ? 'OK' : 'FAIL') . " ({$http['code']})") : '—';
    $note = [];
    if (!$inRoutes) { $note[] = 'NO en ACL plantilla'; $hallazgos[] = "ACL: `$ruta` en menú/cabezote pero no en \$routes"; }
    if (!$vista) { $note[] = 'Falta vista'; $hallazgos[] = "Vista faltante: `$ruta.php`"; }
    if (!$jsOk) { $note[] = 'JS faltante'; $hallazgos[] = "JS faltante para `$ruta`"; }
    if ($http && !$http['ok']) { $note[] = $http['note']; $hallazgos[] = "HTTP `$ruta`: {$http['note']} code={$http['code']}"; }
    if (!$note) $note[] = 'OK estructura';
    $lines[] = sprintf('| %s | %s | %s | %s | %s | %s |',
        $ruta,
        $inRoutes ? 'Sí (' . implode(', ', $routes[$ruta]) . ')' : 'No',
        $vista ? 'Sí' : 'No',
        $js ? implode(', ', $js) : '(común/inline)',
        $httpCol,
        implode('; ', $note)
    );
}

$lines[] = '';
$lines[] = '## Rutas en ACL sin enlace de menú (acceso directo)';
$lines[] = '';
foreach ($routes as $ruta => $perfiles) {
    if (!in_array($ruta, $nav, true) && $ruta !== 'salir') {
        $vista = file_exists("$root/vistas/modulos/$ruta.php") ? 'vista OK' : 'SIN vista';
        $lines[] = "- `$ruta` → " . implode(', ', $perfiles) . " ($vista)";
    }
}

$lines[] = '';
$lines[] = '## Hallazgos';
$lines[] = '';
if (!$hallazgos) {
    $lines[] = '- Sin hallazgos estructurales en navegación.';
} else {
    foreach (array_unique($hallazgos) as $h) {
        $lines[] = "- $h";
    }
}

if (isset($httpResults['__login__'])) {
    $lines[] = '';
    $lines[] = '## Login HTTP';
    $lines[] = '- ' . ($httpResults['__login__']['ok'] ? 'OK' : 'FAIL') . ' — ' . $httpResults['__login__']['note'];
}

$out = implode("\n", $lines) . "\n";
$outPath = "$root/docs/REVISION-PUNTO-A-PUNTO.md";
if (!is_dir("$root/docs")) mkdir("$root/docs", 0775, true);
file_put_contents($outPath, $out);
echo $out;
echo "\nEscrito: $outPath\n";
