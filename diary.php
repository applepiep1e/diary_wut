<?php
session_start();
if(!isset($_SESSION['uid'])){header("Location:index.php");exit;}
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$user='root';
$pass='root123';
$pdo = new PDO($dsn,$user,$pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 删除日记
if(isset($_GET['del_id'])){
    $del_id = (int)$_GET['del_id'];
    if($_SESSION['role'] === 'admin'){
        $stmt = $pdo->prepare("DELETE FROM diaries WHERE id = ?");
        $stmt->execute([$del_id]);
    }else{
        $stmt = $pdo->prepare("DELETE FROM diaries WHERE id = ? AND uid = ?");
        $stmt->execute([$del_id, $_SESSION['uid']]);
    }
    header("Location: diary.php");
    exit;
}

// 新增日记，带公开私有选项
if(isset($_POST['title'],$_POST['content'])){
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $stmt = $pdo->prepare("INSERT INTO diaries(uid,title,content,is_public) VALUES (?,?,?,?)");
    $stmt->execute([$_SESSION['uid'],$_POST['title'],$_POST['content'],$isPublic]);
    echo "<p>✅日记创建成功</p>";
}

// 读取可见日记
if($_SESSION['role'] === 'admin'){
    //管理员：全部日记
    $stmt = $pdo->query("
        SELECT d.*,u.username 
        FROM diaries d JOIN users u ON d.uid=u.id 
        ORDER BY d.create_time DESC
    ");
}else{
    //普通用户：自己所有日记 + 其他人公开日记
    $stmt = $pdo->prepare("
        SELECT d.*,u.username 
        FROM diaries d JOIN users u ON d.uid=u.id 
        WHERE d.uid=? OR d.is_public=1
        ORDER BY d.create_time DESC
    ");
    $stmt->execute([$_SESSION['uid']]);
}
$list = $stmt->fetchAll();
?>
<h2>Welcome <?php echo $_SESSION['username']; ?> [<?php echo $_SESSION['role'];?>]</h2>

<h3>新建日记</h3>
<form method="post">
Title:<input name="title" required><br>
Content:<textarea name="content" required></textarea><br>
<label><input type="checkbox" name="is_public"> 公开日记（其他登录用户可以查看+评论）</label>
<br>
<button>Submit Diary</button>
</form>
<hr>

<h3>日记条目列表</h3>
<?php foreach($list as $row): ?>
<div style="border:1px solid #ccc;padding:10px;margin:6px;">
    <h4>
        <a href="diary_detail.php?id=<?=$row['id']?>"><?=htmlspecialchars($row['title'])?></a>
        <?php if($row['is_public']): ?>
            <span style="color:green">[公开]</span>
        <?php else: ?>
            <span style="color:red">[私有]</span>
        <?php endif; ?>
    </h4>
    <small>作者：<?=htmlspecialchars($row['username'])?> | 创建：<?=$row['create_time']?> | 修改：<?=$row['update_time']?></small>
    <br>
    <?php
    // 作者 or admin 才显示删除按钮
    if($_SESSION['role'] === 'admin' || $row['uid'] == $_SESSION['uid']):
    ?>
        <a href="?del_id=<?=$row['id']?>" onclick="return confirm('确认删除日记？')">🗑️删除</a>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<br>
<p>
<a href="index.php">Index</a>
<a href="cookie_panel.php">Cookie Management Panel</a></p>
<a href="logout.php">Logout</a>
