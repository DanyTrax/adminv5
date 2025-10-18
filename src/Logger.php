<?php

class Logger {
    
    private static $logFile = 'logs/sistema.log';
    
    /**
     * Escribir log con diferentes niveles
     */
    public static function log($level, $message, $file = '', $function = '') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] [$file] [$function] - $message" . PHP_EOL;
        
        // Crear directorio de logs si no existe
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Escribir al archivo de log
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // También escribir al error_log de PHP para debugging inmediato
        error_log("[$level] $message");
    }
    
    /**
     * Log de información general
     */
    public static function info($message, $file = '', $function = '') {
        self::log('INFO', $message, $file, $function);
    }
    
    /**
     * Log de advertencias
     */
    public static function warning($message, $file = '', $function = '') {
        self::log('WARNING', $message, $file, $function);
    }
    
    /**
     * Log de errores
     */
    public static function error($message, $file = '', $function = '') {
        self::log('ERROR', $message, $file, $function);
    }
    
    /**
     * Log de debug (solo en desarrollo)
     */
    public static function debug($message, $file = '', $function = '') {
        self::log('DEBUG', $message, $file, $function);
    }
    
    /**
     * Log de transacciones de base de datos
     */
    public static function transaction($action, $table, $data = [], $file = '', $function = '') {
        $message = "TRANSACTION: $action on $table";
        if (!empty($data)) {
            $message .= " | Data: " . json_encode($data);
        }
        self::log('TRANSACTION', $message, $file, $function);
    }
    
    /**
     * Log de operaciones de stock
     */
    public static function stock($operation, $producto, $cantidad, $file = '', $function = '') {
        $message = "STOCK: $operation | Producto: $producto | Cantidad: $cantidad";
        self::log('STOCK', $message, $file, $function);
    }
    
    /**
     * Log de operaciones AJAX
     */
    public static function ajax($action, $data = [], $file = '', $function = '') {
        $message = "AJAX: $action";
        if (!empty($data)) {
            $message .= " | Data: " . json_encode($data);
        }
        self::log('AJAX', $message, $file, $function);
    }
}
?>
