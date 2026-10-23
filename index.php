
<?php
require 'db.php';

$totalStudents = $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$today = date('Y-m-d');

$stmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS marked,
        SUM(status = 'Present') AS present,
        SUM(status = 'Absent') AS absent,
        SUM(status = 'Late') AS late
     FROM attendance
     WHERE attendance_date = ?"
);
$stmt->execute([$today]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Student Attendance System</h1>
    <p>Manage student attendance easily</p>
</header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="records.php">Attendance Records</a>
    <a href="report.php">Reports</a>
</nav>

<main>
    <h2>Dashboard</h2>
    <p>Today's date: <?= e($today) ?></p>

    <section class="cards">
        <div class="card">
            <h3>Total Students</h3>
            <p><?= e($totalStudents) ?></p>
        </div>

        <div class="card">
            <h3>Present</h3>
            <p><?= e($stats['present'] ?? 0) ?></p>
        </div>

        <div class="card">
            <h3>Absent</h3>
            <p><?= e($stats['absent'] ?? 0) ?></p>
        </div>

        <div class="card">
            <h3>Late</h3>
            <p><?= e($stats['late'] ?? 0) ?></p>
        </div>
    </section>

    <p>
        <a class="button" href="students.php">Add Students</a>
        <a class="button" href="attendance.php">Record Attendance</a>
    </p>

    <p class="note">
        Attendance statistics count only students whose
        attendance has been recorded today.
    </p>
</main>
</body>
</html>
