<?php
session_start();
if(isset($_SESSION['uid'])){
    // 已登录管理员直接进管理面板
    if($_SESSION['role'] === 'admin'){
        header("Location: admin_panel.php");
    }else{
        header("Location: index.php");
    }
    exit;
}
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$user='root';
$pass='root123';
$pdo = new PDO($dsn,$user,$pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$msg = '';

if($_POST){
    $username = trim($_POST['username']);
    $pwd = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userRow = $stmt->fetch();
    if($userRow && password_verify($pwd, $userRow['password'])){
        // Admin登录页面：只允许admin角色账号
        if($userRow['role'] !== 'admin'){
            $msg = "❌此账号不是管理员，请使用普通用户登录页面";
        }else{
            // 更新管理员last_login
            $updateLogin = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id=?");
            $updateLogin->execute([$userRow['id']]);

            $_SESSION['uid'] = $userRow['id'];
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role'] = $userRow['role'];
            header("Location: admin_panel.php");
            exit;
        }
    }else{
        $msg = "❌用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Administrator Login</title>
</head>
<body>
<h2>Administrator Login Portal</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post">
Username: <input name="username" required><br><br>
Password: <input type="password" name="password" required><br><br>
<button>Admin Login</button>
</form>
<br>
<p><a href="login.php">Normal User Login</a></p>
</body>
</html>

