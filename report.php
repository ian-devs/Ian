
<?php
require 'db.php';

$sql = "
    SELECT
        s.id,
        s.admission_no,
        s.full_name,
        s.class_name,
        COUNT(a.id) AS total_sessions,
        COALESCE(SUM(a.status = 'Present'), 0) AS present,
        COALESCE(SUM(a.status = 'Absent'), 0) AS absent,
        COALESCE(SUM(a.status = 'Late'), 0) AS late
    FROM students s
    LEFT JOIN attendance a ON a.student_id = s.id
    GROUP BY s.id, s.admission_no, s.full_name, s.class_name
    ORDER BY s.class_name, s.full_name
";

$reports = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Attendance Reports</h1></header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="records.php">Records</a>
    <a href="report.php">Reports</a>
</nav>

<main>
    <h2>Student Attendance Summary</h2>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Admission No.</th>
            <th>Student</th>
            <th>Class</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Total Marked</th>
            <th>Attendance %</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
            <?php
            $attended = (int)$r['present'] + (int)$r['late'];
            $total = (int)$r['total_sessions'];
            $percentage = $total > 0
                ? ($attended / $total) * 100
                : 0;
            ?>
            <tr>
                <td><?= e($r['admission_no']) ?></td>
                <td><?= e($r['full_name']) ?></td>
                <td><?= e($r['class_name']) ?></td>
                <td><?= e($r['present']) ?></td>
                <td><?= e($r['absent']) ?></td>
                <td><?= e($r['late']) ?></td>
                <td><?= e($total) ?></td>
                <td>
                    <?= $total > 0
                        ? number_format($percentage, 1) . '%'
                        : 'N/A' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <button onclick="window.print()">Print Report</button>
</main>
</body>
</html>
