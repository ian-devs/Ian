
<?php
require 'db.php';

$today = date('Y-m-d');
$date = $_POST['attendance_date']
    ?? $_GET['date']
    ?? $today;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedDate = $_POST['attendance_date'] ?? '';
    $statuses = $_POST['status'] ?? [];

    if (
        !is_string($submittedDate) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $submittedDate) ||
        !checkdate(
            (int)substr($submittedDate, 5, 2),
            (int)substr($submittedDate, 8, 2),
            (int)substr($submittedDate, 0, 4)
        ) ||
        $submittedDate > $today
    ) {
        $error = "Please select a valid date, not in the future.";
    } elseif (!is_array($statuses)) {
        $error = "Invalid attendance data.";
    } else {
        try {
            $pdo->beginTransaction();

            $studentIds = $pdo->query(
                "SELECT id FROM students"
            )->fetchAll(PDO::FETCH_COLUMN);

            $save = $pdo->prepare(
                "INSERT INTO attendance
                    (student_id, attendance_date, status)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE status = VALUES(status)"
            );

            foreach ($studentIds as $id) {
                if (!array_key_exists((string)$id, $statuses)) {
                    continue;
                }

                $status = $statuses[$id];

                if (!in_array(
                    $status,
                    ['Present', 'Absent', 'Late'],
                    true
                )) {
                    throw new RuntimeException(
                        "Invalid status submitted."
                    );
                }

                $save->execute([
                    $id,
                    $submittedDate,
                    $status
                ]);
            }

            $pdo->commit();
            $date = $submittedDate;
            $message = "Attendance saved successfully.";
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $ex instanceof RuntimeException
                ? $ex->getMessage()
                : "Unable to save attendance.";
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT s.id, s.admission_no, s.full_name, s.class_name,
            a.status
     FROM students s
     LEFT JOIN attendance a
       ON a.student_id = s.id
      AND a.attendance_date = ?
     ORDER BY s.class_name, s.full_name"
);
$stmt->execute([$date]);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Record Attendance</h1></header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="records.php">Records</a>
    <a href="report.php">Reports</a>
</nav>

<main>
    <h2>Daily Attendance</h2>

    <?php if ($message): ?>
        <p class="success"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Attendance Date</label>
        <input type="date" name="attendance_date"
               value="<?= e($date) ?>"
               max="<?= e($today) ?>" required>

        <button type="submit" name="load" value="1">
            Load Selected Date
        </button>

        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Admission No.</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?= e($student['admission_no']) ?></td>
                <td><?= e($student['full_name']) ?></td>
                <td><?= e($student['class_name']) ?></td>
                <td>
                    <select name="status[<?= e($student['id']) ?>]">
                        <option value="">Not marked</option>
                        <?php foreach (
                            ['Present', 'Absent', 'Late'] as $status
                        ): ?>
                            <option value="<?= e($status) ?>"
                                <?= $student['status'] === $status
                                    ? 'selected' : '' ?>>
                                <?= e($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <button type="submit">Save Attendance</button>
    </form>
</main>
</body>
</html>
