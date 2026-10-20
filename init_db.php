<?php
$dsn = 'mysql:host=uopz-mysql-1;dbname=wut_demo;charset=utf8mb4';
$user = 'root';
$pass = 'root123';
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 删表，注意外键顺序
$pdo->exec("DROP TABLE IF EXISTS comments");
$pdo->exec("DROP TABLE IF EXISTS diaries");
$pdo->exec("DROP TABLE IF EXISTS users");

// 用户表：新增 last_login 记录最近登录时间
$sqlUser = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL,
    last_login DATETIME NULL
)";
$pdo->exec($sqlUser);

// 日记表
$sqlDiary = "CREATE TABLE IF NOT EXISTS diaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    is_public TINYINT(1) DEFAULT 0,
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(uid) REFERENCES users(id) ON DELETE CASCADE
)";
$pdo->exec($sqlDiary);

// 评论表
$sqlComment = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    diary_id INT NOT NULL,
    uid INT NOT NULL,
    content TEXT NOT NULL,
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(diary_id) REFERENCES diaries(id) ON DELETE CASCADE,
    FOREIGN KEY(uid) REFERENCES users(id) ON DELETE CASCADE
)";
$pdo->exec($sqlComment);

// 预置账号
$adminPwd = password_hash('admin123', PASSWORD_DEFAULT);
$userPwd = password_hash('alice123', PASSWORD_DEFAULT);
$pdo->exec("INSERT IGNORE INTO users(username,password,role) VALUES ('admin','$adminPwd','admin'),('alice','$userPwd','user')");

echo "✅ 数据表重建完成，users表增加last_login字段";
