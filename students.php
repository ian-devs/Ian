
<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission = trim($_POST['admission_no'] ?? '');
    $name = trim($_POST['full_name'] ?? '');
    $class = trim($_POST['class_name'] ?? '');

    if ($admission === '' || $name === '' || $class === '') {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO students
                (admission_no, full_name, class_name)
                VALUES (?, ?, ?)"
            );
            $stmt->execute([$admission, $name, $class]);

            header("Location: students.php?added=1");
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $error = "That admission number already exists.";
            } else {
                $error = "Unable to save the student.";
            }
        }
    }
}

$students = $pdo->query(
    "SELECT * FROM students ORDER BY full_name"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Student Management</h1></header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="records.php">Records</a>
    <a href="report.php">Reports</a>
</nav>

<main>
    <h2>Register Student</h2>

    <?php if (isset($_GET['added'])): ?>
        <p class="success">Student added successfully.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Admission Number</label>
        <input type="text" name="admission_no"
               maxlength="30" required>

        <label>Full Name</label>
        <input type="text" name="full_name"
               maxlength="100" required>

        <label>Class</label>
        <input type="text" name="class_name"
               maxlength="50" required>

        <button type="submit">Add Student</button>
    </form>

    <h2>Registered Students</h2>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Admission No.</th>
            <th>Full Name</th>
            <th>Class</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?= e($student['id']) ?></td>
            <td><?= e($student['admission_no']) ?></td>
            <td><?= e($student['full_name']) ?></td>
            <td><?= e($student['class_name']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>
</body>
</html>
