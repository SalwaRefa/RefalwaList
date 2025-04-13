<?php include 'db.php'; 
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subtask</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(101, 121, 144);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input[type="text"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color:rgb(44, 62, 80);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s;
        }

        button:hover {
            background-color:rgb(34, 52, 70);
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            text-decoration: none;
            color:rgb(44, 62, 80);
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Subtask</h1>
    <?php
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<p class='error'>ID subtask tidak tersedia.</p>";
        exit;
    }
    
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM subtasks WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        echo "<p class='error'>Subtask tidak ditemukan.</p>";
        exit;
    }

    $isCompleted = $row['completed'];
    $isOverdue = strtotime($row['deadline']) < time();
    $isLocked = $isCompleted || $isOverdue;
    ?>

    <form method="POST">
        <label>Deskripsi:</label>
        <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" <?= $isLocked ? 'disabled' : '' ?> required>
        
        <label>Deadline:</label>
        <input type="date" name="deadline" value="<?= $row['deadline'] ?>" <?= $isLocked ? 'disabled' : '' ?> required>
        
        <button type="submit" name="update" <?= $isLocked ? 'disabled' : '' ?>>Update</button>
    </form>

    <div class="back-link">
        <a href="subtasks.php?task_id=<?= $row['task_id'] ?>">Kembali ke Subtasks</a>
    </div>

    <?php
    if (isset($_POST['update']) && !$isLocked) {
        $description = $_POST['description'];
        $deadline = $_POST['deadline'];

        $stmt = $pdo->prepare("UPDATE subtasks SET description = ?, deadline = ? WHERE id = ?");
        $updated = $stmt->execute([$description, $deadline, $id]);
        
        if ($updated) {
            echo "<p class='success'>Subtask berhasil diupdate!</p>";
            header("Refresh: 2; URL=subtasks.php?task_id=" . $row['task_id']);
        } else {
            echo "<p class='error'>Error: Gagal mengupdate subtask.</p>";
        }
    }
    ?>
</div>
</body>
</html>