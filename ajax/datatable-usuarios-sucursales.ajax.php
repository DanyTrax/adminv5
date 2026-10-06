<?php
require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireProfiles(["Administrador"]);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(["data" => []]);
