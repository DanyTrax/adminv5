<?php

if (!function_exists('numberFormat')) {
    function numberFormat($float, $int = 0)
    {
        return number_format($numero ?? 0, 2, ',', '.');
    }
}

if (!function_exists('send_factura')) {
    function sendFactura($codigo)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://admin.epicosiempremas.com/extensiones/tcpdf/pdf/factura-email.php?codigo=$codigo");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $th) {
            // throw $th;
        }
    }
}
