<?php
// Stage2: WUT HelloWorld with DB access, NO INSTRUMENTATION
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

echo "<h1>WUT Hello World (Database Demo, No Instrumentation)</h1>";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 原生数据库查询
    $stmt = $pdo->query("SELECT username, email FROM users WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<p>✅ Database query success</p>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";

} catch (PDOException $e) {
    echo "<p>❌ DB Error: " . $e->getMessage() . "</p>";
}

echo "<p>End of WUT execution.</p>";
