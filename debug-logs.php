<?php
echo "<h2>Error Log Reciente:</h2>";
echo "<pre>";
echo file_get_contents("error_log") ?: "No se encontró error_log";
echo "</pre>";
?>