<?php
session_start();
if(!isset($_SESSION['uid'])){header("Location:index.php");exit;}
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$user='root';
$pass='root123';
$pdo = new PDO($dsn,$user,$pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$diaryId = (int)$_GET['id'];

// 查询日记
$stmtDiary = $pdo->prepare("
    SELECT d.*,u.username 
    FROM diaries d JOIN users u ON d.uid=u.id 
    WHERE d.id = ?
");
$stmtDiary->execute([$diaryId]);
$diary = $stmtDiary->fetch();

// 权限校验：私有日记，只有作者和管理员可以访问
if(!$diary){
    die("日记不存在");
}
if($diary['is_public'] == 0 && $_SESSION['role']!='admin' && $diary['uid'] != $_SESSION['uid']){
    die("❌无权限访问此私有日记");
}

// 提交评论
if(isset($_POST['comment_content']) && !empty(trim($_POST['comment_content']))){
    // 只有公开日记才允许评论
    if($diary['is_public'] == 1){
        $stmtComment = $pdo->prepare("INSERT INTO comments(diary_id, uid, content) VALUES (?,?,?)");
        $stmtComment->execute([$diaryId, $_SESSION['uid'], $_POST['comment_content']]);
        header("Location: diary_detail.php?id=$diaryId");
        exit;
    }else{
        $msg = "私有日记不能被评论";
    }
}

// 查询该日记下所有评论
$stmtComments = $pdo->prepare("
    SELECT c.*,u.username 
    FROM comments c JOIN users u ON c.uid=u.id 
    WHERE c.diary_id = ? ORDER BY c.create_time ASC
");
$stmtComments->execute([$diaryId]);
$comments = $stmtComments->fetchAll();
?>
<a href="diary.php">← 返回日记列表</a>
<h2><?=htmlspecialchars($diary['title'])?>
<?php if($diary['is_public']): ?>
    <span style="color:green">[公开日记]</span>
<?php else: ?>
    <span style="color:red">[私有日记]</span>
<?php endif; ?>
</h2>
<p><small>作者：<?=htmlspecialchars($diary['username'])?><br>
创建时间：<?=$diary['create_time']?><br>
最后修改：<?=$diary['update_time']?></small></p>
<div style="border:1px solid #aaa;padding:10px;">
<?=nl2br(htmlspecialchars($diary['content']))?>
</div>

<?php if($diary['is_public'] == 1): ?>
    <hr>
    <h3>添加评论</h3>
    <?php if(isset($msg)) echo "<p style='color:red'>$msg</p>"; ?>
    <form method="post">
        <textarea name="comment_content" required placeholder="写下你的评论"></textarea><br>
        <button>提交评论</button>
    </form>
<?php endif; ?>

<hr>
<h3>评论列表</h3>
<?php if(empty($comments)): ?>
<p>暂无评论</p>
<?php else: ?>
<?php foreach($comments as $c): ?>
<div style="border:1px solid #eee;padding:6px;margin:4px;">
    <b><?=htmlspecialchars($c['username'])?></b> <small><?=$c['create_time']?></small>
    <p><?=nl2br(htmlspecialchars($c['content']))?></p>
</div>
<?php endforeach; ?>
<?php endif; ?>

