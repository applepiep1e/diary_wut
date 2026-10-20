<?php
if (!extension_loaded('uopz')) {
    return;
}
$logFile = '/tmp/sql_hook.log';

try {
    uopz_set_hook(PDO::class, 'prepare', function ($sql, $options = []) use ($logFile) {
        $t = date('Y-m-d H:i:s');
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $post = file_get_contents('php://input');
        $log = "==== PREPARE ====\nTime:{$t}\nURI:{$uri}\nSQL:{$sql}\nPOST:{$post}\n----------------\n";
        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    });
} catch (Throwable $e) {}

try {
    uopz_set_hook(PDOStatement::class, 'execute', function ($params = null) use ($logFile) {
        $t = date('Y-m-d H:i:s');
        $log = "==== EXECUTE ====\nTime:{$t}\nParams:" . print_r($params, true) . "\n----------------\n\n";
        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    });
} catch (Throwable $e) {}
