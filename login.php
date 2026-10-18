<?php
session_start();
if(isset($_SESSION['uid'])){
    header("Location: index.php");
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
        // 普通用户登录页面：禁止admin账号登录
        if($userRow['role'] === 'admin'){
            $msg = "❌管理员账号请使用Admin专属登录页面";
        }else{
            // 更新last_login为当前时间
            $updateLogin = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id=?");
            $updateLogin->execute([$userRow['id']]);

            $_SESSION['uid'] = $userRow['id'];
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role'] = $userRow['role'];
            header("Location: index.php");
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
<title>Normal User Login</title>
</head>
<body>
<h2>Normal User Login</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post">
Username: <input name="username" required><br><br>
Password: <input type="password" name="password" required><br><br>
<button>Login</button>
</form>
<br>
<p><a href="register.php">Register new normal user</a></p>
<p style="color:blue"><a href="admin_login.php">Admin Login Portal</a></p>
</body>
</html>

