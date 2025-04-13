<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['priority'])) {
    $title = trim($_POST['title']);
    $priority = $_POST['priority'];

    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, priority, status) VALUES (?, ?, ?, 'Not Done')");
    $stmt->execute([$user_id, $title, $priority]);

    header("Location: tasks.php");
    exit;
}

$tasks = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY 
    CASE 
        WHEN priority = 'High' THEN 1
        WHEN priority = 'Medium' THEN 2
        WHEN priority = 'Low' THEN 3
    END");
$tasks->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard To-Do List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: rgb(101, 121, 144);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 450px;
            text-align: center;
            position: relative;
        }
        h1 {
            margin-bottom: 20px;
            display: inline-block;
        }
        .logout {
            position: absolute;
            top: 50px;
            right: 20px;
            color: #e74c3c;
            font-weight: bold;
            text-decoration: none;
        }
        .logout:hover {
            text-decoration: underline;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: rgb(44, 62, 80);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: rgb(34, 52, 70);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: rgb(44, 62, 80);
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            background: #2c3e50;
        }
        .btn:hover {
            background: #34495e;
        }
        .btn.delete {
            background: #e74c3c;
        }
        .btn.delete:hover {
            background: #c0392b;
        }
        .priority {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>To-Do List</h1>
        <a href="logout.php" class="logout">Logout</a>
        <form method="POST" action="tasks.php">
            <input type="text" name="title" placeholder="Masukkan Judul Kegiatan" required>
            <select name="priority" required>
                <option value="High">Penting</option>
                <option value="Medium">Kurang Penting</option>
                <option value="Low">Biasa</option>
            </select>
            <button type="submit">Tambah List</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Prioritas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><span class="priority <?= strtolower($task['priority']) ?>">
                        <?= $task['priority'] === 'High' ? 'Penting' : ($task['priority'] === 'Medium' ? 'Kurang Penting' : 'Biasa') ?></span></td>
                    <td>
                        <a href="subtasks.php?task_id=<?= $task['id'] ?>" class="btn">Lihat Tugas</a>
                        <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn delete" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
