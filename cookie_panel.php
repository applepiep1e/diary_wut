<?php
session_start();
if(!isset($_SESSION['uid'])){
    header("Location:index.php");
    exit;
}

$msg = "";
// 设置自定义cookie
if(isset($_POST['set_cookie'])){
    $c_name = trim($_POST['cookie_name']);
    $c_val = trim($_POST['cookie_value']);
    $expire_h = (int)$_POST['expire_hours'];
    $expire = time() + $expire_h * 3600;
    // 禁止用户创建名为PHPSESSID的cookie，避免冲突
    if($c_name === "PHPSESSID"){
        $msg = "❌ 不允许手动创建PHPSESSID，这是系统会话Cookie";
    }else{
        setcookie($c_name, $c_val, $expire, "/");
        $msg = "✅ Cookie【$c_name】设置成功";
    }
}

// 删除cookie（禁止删除PHPSESSID）
if(isset($_POST['del_cookie_name'])){
    $delName = $_POST['del_cookie_name'];
    if($delName === "PHPSESSID"){
        $msg = "❌ 禁止删除PHPSESSID（系统会话Cookie，删除会直接退出登录）";
    }else{
        setcookie($delName, "", time()-3600, "/");
        $msg = "🗑️ Cookie【$delName】已删除";
    }
}
?>
<h2>Cookie Management Panel</h2>
<p>Logged in as: <?=htmlspecialchars($_SESSION['username'])?> [<?=$_SESSION['role']?>]</p>
<?php if($msg) echo "<p>$msg</p>"; ?>

<h3>Set new custom cookie</h3>
<form method="post">
Cookie Name: <input type="text" name="cookie_name" required><br>
Cookie Value: <input type="text" name="cookie_value" required><br>
Expire hours: <input type="number" name="expire_hours" value="1" min="0"><br>
<button name="set_cookie">Set Cookie</button>
<p style="color:#666">⚠️ 不能创建名称为 PHPSESSID 的Cookie</p>
</form>

<hr>
<h3>Current Cookies in Browser</h3>
<?php if(empty($_COOKIE)): ?>
<p>No cookies found.</p>
<?php else: ?>
<ul>
<?php foreach($_COOKIE as $k => $v): ?>
<li style="margin:8px 0;">
    <b><?=htmlspecialchars($k)?></b> = <?=htmlspecialchars($v)?>
    <?php if($k === 'PHPSESSID'): ?>
        <span style="color:blue;">【系统会话Cookie PHPSESSID，用于维持登录，不可删除】</span>
    <?php else: ?>
        <form method="post" style="display:inline">
            <input type="hidden" name="del_cookie_name" value="<?=htmlspecialchars($k)?>">
            <button type="submit">Delete</button>
        </form>
    <?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<br>
<a href="diary.php">← Back to Diary List</a>

