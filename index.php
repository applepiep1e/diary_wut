<?php
session_start();
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$dbuser='root';
$dbpass='root123';
$pdo = new PDO($dsn,$dbuser,$dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 未登录，跳转登录页
if(!isset($_SESSION['uid'])){
    header("Location: login.php");
    exit;
}
$now = date("Y-m-d H:i:s");
$todayDate = date("Y-m-d");

// 查询全部系统用户列表
$stmtUsers = $pdo->query("SELECT id, username, role FROM users");
$allUsers = $stmtUsers->fetchAll();

// 查询所有公开日记
$stmtPublicDiary = $pdo->query("
    SELECT d.*, u.username
    FROM diaries d JOIN users u ON d.uid = u.id
    WHERE d.is_public = 1
    ORDER BY d.create_time DESC
");
$publicDiaries = $stmtPublicDiary->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>WUT Diary Application - Home</title>
</head>
<body>
<h1>WUT Target Web Application (Diary System)</h1>
<hr>
<h3>System Time Info</h3>
<p>Current Date: <?=$todayDate?></p>
<p>Current Server Time: <?=$now?></p>

<h3>Application Description</h3>
<p>This is the WUT(Web Under Test) target demo application for UOPZ function hooking experiment.</p>
<ul>
    <li>Separate login portals: normal user login and administrator login</li>
    <li>User register/login system, role: admin / normal user</li>
    <li>Create private / public diaries</li>
    <li>Public diary supports comments from logged users</li>
    <li>Cookie & session management panel to demonstrate HTTP cookie operations</li>
    <li>Admin panel for user management and global diary inspection</li>
</ul>
<hr>

<?php
// Admin专属导航，仅admin可见
if($_SESSION['role'] === 'admin'){
?>
<h3>Admin Navigation (Admin only)</h3>
<a href="admin_panel.php">Admin Management Panel</a> |
<a href="diary.php">Diary Management</a> |
<a href="cookie_panel.php">Cookie Panel</a> |
<a href="logout.php">Logout</a>
<hr>
<?php }else{ ?>
<h3>User Navigation</h3>
<a href="diary.php">My Diary Page</a> |
<a href="cookie_panel.php">Cookie Panel</a> |
<a href="logout.php">Logout</a>
<hr>
<?php } ?>

<?php
// ========== 只有管理员才能看到用户列表 ==========
if($_SESSION['role'] === 'admin'){
?>
<h3>All Registered User List</h3>
<table border="1" cellpadding="5">
<tr>
    <th>User ID</th>
    <th>Username</th>
    <th>Role</th>
</tr>
<?php foreach($allUsers as $u): ?>
<tr>
    <td><?=$u['id']?></td>
    <td><?=htmlspecialchars($u['username'])?></td>
    <td><?=$u['role']?></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
<?php } ?>

<h3>All Public Diaries (All logged users can view & comment)</h3>
<?php if(empty($publicDiaries)): ?>
<p>No public diaries available.</p>
<?php else: ?>
<?php foreach($publicDiaries as $row): ?>
<div style="border:1px solid #ccc;padding:8px;margin:5px;">
    <h4><a href="diary_detail.php?id=<?=$row['id']?>"><?=htmlspecialchars($row['title'])?></a> <span style="color:green">[Public]</span></h4>
    <small>Author: <?=htmlspecialchars($row['username'])?> | Created: <?=$row['create_time']?></small>
</div>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>

