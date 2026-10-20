<?php
session_start();
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$dbuser='root';
$dbpass='root123';
$pdo = new PDO($dsn,$dbuser,$dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 权限校验：必须登录且角色为admin
if(!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin'){
    header("Location: admin_login.php");
    exit;
}
$msg = "";
// ========== 新增：添加用户逻辑 ==========
if(isset($_POST['add_user_submit'])){
    $newUsername = trim($_POST['add_username']);
    $newRole = $_POST['add_role'];
    $newPwdRaw = "123456"; //新建用户默认密码123456
    $hashPwd = password_hash($newPwdRaw, PASSWORD_DEFAULT);
    //简单校验用户名非空
    if(empty($newUsername)){
        $msg = "❌ Username cannot be empty!";
    }else{
        $stmtAdd = $pdo->prepare("INSERT INTO users (username, password, role, last_login) VALUES (?, ?, ?, NULL)");
        $stmtAdd->execute([$newUsername, $hashPwd, $newRole]);
        $msg = "✅ New user created, default password:123456";
    }
}
// 删除用户逻辑
if(isset($_POST['delete_user_id'])){
    $delUid = (int)$_POST['delete_user_id'];
    // 禁止删除当前登录管理员账号，防止锁死后台
    if($delUid == $_SESSION['uid']){
        $msg = "❌ Cannot delete currently logged-in admin account!";
    }else{
        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmtDel->execute([$delUid]);
        $msg = "✅ User deleted successfully.";
    }
}
// 编辑用户提交逻辑
if(isset($_POST['edit_user_submit'])){
    $editUid = (int)$_POST['edit_uid'];
    $newUsername = trim($_POST['new_username']);
    $newRole = $_POST['new_role'];
    // 不能修改当前管理员id
    if($editUid == $_SESSION['uid'] && $newRole !== "admin"){
        $msg = "❌ Cannot change role of current admin to user!";
    }else{
        $stmtUpdate = $pdo->prepare("UPDATE users SET username=?, role=? WHERE id=?");
        $stmtUpdate->execute([$newUsername, $newRole, $editUid]);
        $msg = "✅ User info updated.";
    }
}
// ========= 修改密码（自定义密码，用于注入测试） =========
if(isset($_POST['change_pwd_submit'])){
    $pwdUid = (int)$_POST['pwd_uid'];
    $inputPwd = trim($_POST['input_new_pwd']);
    $hashPwd = password_hash($inputPwd, PASSWORD_DEFAULT);
    $stmtReset = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
    $stmtReset->execute([$hashPwd, $pwdUid]);
    $msg = "✅ Password updated";
}
// 获取全部用户，附带last_login
$stmtUsers = $pdo->query("SELECT id, username, role, last_login FROM users");
$allUsers = $stmtUsers->fetchAll();
// 获取系统全部日记（私有+公开，全部用户）
$stmtAllDiaries = $pdo->query("
    SELECT d.*, u.username
    FROM diaries d JOIN users u ON d.uid = u.id
    ORDER BY d.create_time DESC
");
$allDiaries = $stmtAllDiaries->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Management Panel</title>
</head>
<body>
<h1>Admin Management Panel</h1>
<p>Welcome Admin: <?=htmlspecialchars($_SESSION['username'])?></p>
<?php if($msg) echo '<p style="color:red;">'.$msg.'</p>'; ?>
<p>
    <a href="index.php">Homepage</a> |
    <a href="cookie_panel.php">Cookie Panel</a> |
    <a href="logout.php">Logout</a>
</p>
<hr>
<h2>User Management List（Show last login time）</h2>
<!-- ========== 新增添加用户按钮 ========== -->
<button onclick="showAddForm()">➕ Add New User</button>
<br><br>
<table border="1" cellpadding="6">
<tr>
    <th>User ID</th>
    <th>Username</th>
    <th>Role</th>
    <th>Last Login Time</th>
    <th>Operations</th>
</tr>
<?php foreach($allUsers as $u): ?>
<tr>
    <td><?=$u['id']?></td>
    <td><?=htmlspecialchars($u['username'])?></td>
    <td><?=$u['role']?></td>
    <td><?= empty($u['last_login']) ? "Never Login" : $u['last_login'] ?></td>
    <td>
        <!-- Edit Button，弹窗简单编辑表单 -->
        <button onclick="showEditForm(<?=$u['id']?>, '<?=htmlspecialchars($u['username'])?>', '<?=$u['role']?>')">Edit</button>
        <!-- Change Password 自定义密码按钮 -->
        <button onclick="showPwdForm(<?=$u['id']?>)">ChangePwd</button>
        <!-- Delete form -->
        <form method="post" style="display:inline" onsubmit="return confirm('Confirm delete this user? All diaries/comments of this user will be removed!')">
            <input type="hidden" name="delete_user_id" value="<?=$u['id']?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<!-- 新增：添加用户弹窗 -->
<div id="addModal" style="display:none; border:1px solid black; padding:10px; margin:10px 0;">
<h3>Add New User</h3>
<form method="post">
    Username: <input type="text" name="add_username" id="add_username" required><br><br>
    Role:
    <select name="add_role">
        <option value="user">user</option>
        <option value="admin">admin</option>
    </select>
    <br><br>
    <button type="submit" name="add_user_submit">Create User</button>
    <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
</form>
</div>

<!-- 编辑弹窗表单 -->
<div id="editModal" style="display:none; border:1px solid black; padding:10px; margin:10px 0;">
<h3>Edit User</h3>
<form method="post">
    <input type="hidden" name="edit_uid" id="edit_uid">
    Username: <input type="text" name="new_username" id="edit_username" required><br><br>
    Role:
    <select name="new_role" id="edit_role">
        <option value="user">user</option>
        <option value="admin">admin</option>
    </select>
    <br><br>
    <button type="submit" name="edit_user_submit">Save Change</button>
    <button type="button" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
</form>
</div>

<!-- 修改密码弹窗 -->
<div id="pwdModal" style="display:none; border:1px solid black; padding:10px; margin:10px 0;">
<h3>Change User Password</h3>
<form method="post">
    <input type="hidden" name="pwd_uid" id="pwd_uid">
    New Password: <input type="text" name="input_new_pwd" id="input_new_pwd" required><br><br>
    <button type="submit" name="change_pwd_submit">Update Password</button>
    <button type="button" onclick="document.getElementById('pwdModal').style.display='none'">Cancel</button>
</form>
</div>

<script>
function showEditForm(uid, uname, role){
    document.getElementById('editModal').style.display='block';
    document.getElementById('edit_uid').value = uid;
    document.getElementById('edit_username').value = uname;
    document.getElementById('edit_role').value = role;
}
// 新增js：打开新增用户弹窗
function showAddForm(){
    document.getElementById('addModal').style.display='block';
    document.getElementById('add_username').value = '';
}
// 打开修改密码弹窗
function showPwdForm(uid){
    document.getElementById('pwdModal').style.display='block';
    document.getElementById('pwd_uid').value = uid;
    document.getElementById('input_new_pwd').value = '';
}
</script>
<hr>
<h2>All Diaries in System (Admin can view all private & public)</h2>
<?php if(empty($allDiaries)): ?>
<p>No diary exists in system.</p>
<?php else: ?>
<?php foreach($allDiaries as $row): ?>
<div style="border:1px solid #ccc;padding:8px;margin:6px;">
    <h4>
        <a href="diary_detail.php?id=<?=$row['id']?>"><?=htmlspecialchars($row['title'])?></a>
        <?php if($row['is_public']): ?>
            <span style="color:green">[Public]</span>
        <?php else: ?>
            <span style="color:red">[Private]</span>
        <?php endif; ?>
    </h4>
    <small>Author: <?=htmlspecialchars($row['username'])?>
    | Create: <?=$row['create_time']?> | Update: <?=$row['update_time']?></small>
</div>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>

