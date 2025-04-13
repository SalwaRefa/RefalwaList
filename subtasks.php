<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['task_id'])) {
    header("Location: login.php");
    exit;
}

$task_id = $_GET['task_id'];

$stmt = $pdo->prepare("SELECT title, priority FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo "Tugas tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'], $_POST['deadline'])) {
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, description, deadline) VALUES (?, ?, ?)");
    $stmt->execute([$task_id, $description, $deadline]);

    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM subtasks WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subtask_id'])) {
    $subtask_id = $_POST['subtask_id'];
    $completed = isset($_POST['completed']) ? 1 : 0;

    $stmt = $pdo->prepare("SELECT deadline, completed FROM subtasks WHERE id = ?");
    $stmt->execute([$subtask_id]);
    $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subtask) {
        $deadlineTimestamp = strtotime($subtask['deadline']);
        $now = time();

        if ($deadlineTimestamp >= $now && $subtask['completed'] == 0 && $completed == 1) {
            $stmt = $pdo->prepare("UPDATE subtasks SET completed = ? WHERE id = ?");
            $stmt->execute([$completed, $subtask_id]);
        }
    }

    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

$subtasks = $pdo->prepare("SELECT * FROM subtasks WHERE task_id = ?");
$subtasks->execute([$task_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtasks - <?= htmlspecialchars($task['title']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(101, 121, 144);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        h1, h2 {
            text-align: center;
            font-size: 1.5rem;
            color: #333;
        }

        .priority {
            text-align: center;
            font-size: 1rem;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            color: #fff;
            background-color: <?= ($task['priority'] === 'Tinggi') ? '#dc3545' : (($task['priority'] === 'Sedang') ? '#ffc107' : '#28a745') ?>;
        }

        table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: rgb(44, 62, 80);
            color: white;
        }

        .completed {
            text-decoration: line-through;
            color: #888;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        textarea, input[type="date"], button {
            margin-top: 10px;
            padding: 8px;
            width: 100%;
        }

        button {
            background-color: #2c3e50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($task['title']) ?></h1>
    <h2 class="priority">Prioritas: <?= htmlspecialchars($task['priority']) ?></h2>

    <form method="POST">
        <textarea name="description" placeholder="Deskripsi Subtask" required rows="3"></textarea>
        <input type="date" name="deadline" required min="<?= date("Y-m-d") ?>">
        <button type="submit">Tambah</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>Deskripsi</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($subtasks as $subtask): ?>
            <tr>
                <td class="<?= $subtask['completed'] ? 'completed' : '' ?>">
                    <?= htmlspecialchars($subtask['description']) ?>
                </td>
                <td><?= date("d M Y", strtotime($subtask['deadline'])) ?></td>
                <td style="text-align: center;">
                    <form method="POST">
                        <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
                        <input type="checkbox"
                               name="completed"
                               <?= $subtask['completed'] ? 'checked disabled' : '' ?>
                               <?= strtotime($subtask['deadline']) < time() ? 'disabled' : '' ?>
                               onchange="this.form.submit()">
                    </form>
                    <?= $subtask['completed'] ? '<span style="color:green; font-weight:bold;">Sudah Selesai</span>' : '<span style="color:red; font-weight:bold;">Belum Selesai</span>' ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $subtask['id']; ?>">Edit</a>
                    <a href="subtasks.php?task_id=<?= $task_id; ?>&delete_id=<?= $subtask['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="back-link">
        <a href="tasks.php">Kembali</a>
    </div>
</div>
</body>
</html>
