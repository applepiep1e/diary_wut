<?php
session_start();
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$user='root';
$pass='root123'; // 替换成你的mysql root密码
$pdo = new PDO($dsn,$user,$pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$msg = '';

if($_POST){
    $username = trim($_POST['username']);
    $pwd = $_POST['password'];
    // 校验
    if(empty($username) || empty($pwd)){
        $msg="❌用户名和密码不能为空";
    }else{
        try{
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            // 只能注册普通user角色，不能注册admin
            $stmt = $pdo->prepare("INSERT INTO users(username,password,role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hash]);
            $msg="✅注册成功！<a href='index.php'>前往登录</a>";
        }catch(PDOException $e){
            $msg="❌用户名已被占用，请换一个";
        }
    }
}
?>
<h2>Register New User</h2>
<?php echo $msg;?>
<form method="post">
Username: <input name="username"><br><br>
Password: <input type="password" name="password"><br><br>
<button type="submit">Register</button>
</form>
<br>
<a href="index.php">← Back to Login</a>

