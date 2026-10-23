
<?php
require 'db.php';

$date = $_GET['date'] ?? date('Y-m-d');

if (
    !is_string($date) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
    !checkdate(
        (int)substr($date, 5, 2),
        (int)substr($date, 8, 2),
        (int)substr($date, 0, 4)
    )
) {
    $date = date('Y-m-d');
}

$stmt = $pdo->prepare(
    "SELECT s.admission_no, s.full_name, s.class_name,
            a.attendance_date, a.status
     FROM attendance a
     JOIN students s ON s.id = a.student_id
     WHERE a.attendance_date = ?
     ORDER BY s.class_name, s.full_name"
);
$stmt->execute([$date]);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Records</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Attendance Records</h1></header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="records.php">Records</a>
    <a href="report.php">Reports</a>
</nav>

<main>
    <h2>Search Records by Date</h2>

    <form method="get">
        <label>Select Date</label>
        <input type="date" name="date"
               value="<?= e($date) ?>" required>
        <button type="submit">Search</button>
    </form>

    <h3>Records for <?= e($date) ?></h3>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Admission No.</th>
            <th>Student</th>
            <th>Class</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($records as $record): ?>
        <tr>
            <td><?= e($record['admission_no']) ?></td>
            <td><?= e($record['full_name']) ?></td>
            <td><?= e($record['class_name']) ?></td>
            <td>
                <span class="status <?= strtolower(e($record['status'])) ?>">
                    <?= e($record['status']) ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (!$records): ?>
        <tr>
            <td colspan="4">No attendance records found for this date.</td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</main>
</body>
</html>
