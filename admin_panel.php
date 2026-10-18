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

// 重置用户密码
if(isset($_POST['reset_pwd_submit'])){
    $resetUid = (int)$_POST['reset_uid'];
    $newPwd = "123456"; // 重置默认密码
    $hashPwd = password_hash($newPwd, PASSWORD_DEFAULT);
    $stmtReset = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
    $stmtReset->execute([$hashPwd, $resetUid]);
    $msg = "✅ Password reset to 123456";
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

        <!-- Reset Password -->
        <form method="post" style="display:inline" onsubmit="return confirm('Reset password to 123456?')">
            <input type="hidden" name="reset_uid" value="<?=$u['id']?>">
            <button type="submit" name="reset_pwd_submit">ResetPwd</button>
        </form>

        <!-- Delete form -->
        <form method="post" style="display:inline" onsubmit="return confirm('Confirm delete this user? All diaries/comments of this user will be removed!')">
            <input type="hidden" name="delete_user_id" value="<?=$u['id']?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

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

<script>
function showEditForm(uid, uname, role){
    document.getElementById('editModal').style.display='block';
    document.getElementById('edit_uid').value = uid;
    document.getElementById('edit_username').value = uname;
    document.getElementById('edit_role').value = role;
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

