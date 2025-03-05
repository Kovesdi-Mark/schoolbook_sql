<?php

require "config.php";
require "sql_commands.php";

showButton();
getTask();

function getTask() {
    if (isset($_POST['create'])) {
        createDB();
    }

    // Osztály hozzáadása
    if (isset($_POST['add_class'])) {
        $name = trim($_POST['class_name']);
        $year = $_POST['class_year'];
        if (!empty($name) && !empty($year)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("INSERT INTO classes (name, year) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $year);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Osztály szerkesztése
    if (isset($_POST['edit_class'])) {
        $id = (int)$_POST['class_id'];
        $name = trim($_POST['class_name']);
        $year = $_POST['class_year'];
        if ($id > 0 && !empty($name) && !empty($year)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("UPDATE classes SET name = ?, year = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $year, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Osztály törlése
    if (isset($_GET['delete_class'])) {
        $id = (int)$_GET['delete_class'];
        $mysqli = getConn();
        $stmt = $mysqli->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?action=edit");
        exit();
    }

    // Tanuló hozzáadása
    if (isset($_POST['add_student'])) {
        $name = trim($_POST['student_name']);
        $class_id = (int)$_POST['class_id'];
        if (!empty($name) && $class_id > 0) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("INSERT INTO students (name, class_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $class_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Tanuló szerkesztése
    if (isset($_POST['edit_student'])) {
        $id = (int)$_POST['student_id'];
        $name = trim($_POST['student_name']);
        $class_id = (int)$_POST['class_id'];
        if ($id > 0 && !empty($name) && $class_id > 0) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("UPDATE students SET name = ?, class_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $class_id, $id);
            $result = $stmt->execute();
            if ($result && $stmt->affected_rows > 0) {
                $stmt->close();
                header("Location: index.php?action=edit");
                exit();
            } else {
                echo "Error updating student or no change: " . $stmt->error;
                $stmt->close();
            }
        } else {
            echo "Invalid data for student edit.";
        }
    }

    // Tanuló törlése
    if (isset($_GET['delete_student'])) {
        $id = (int)$_GET['delete_student'];
        if ($id > 0) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("DELETE FROM students WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            if ($result && $stmt->affected_rows > 0) {
                $mysqli->query("SET @new_id = 0;");
                $mysqli->query("UPDATE students SET id = (@new_id := @new_id + 1) ORDER BY id;");
                $mysqli->query("ALTER TABLE students AUTO_INCREMENT = 1;");
                $stmt->close();
                header("Location: index.php?action=edit");
                exit();
            } else {
                echo "Error deleting student: " . $stmt->error;
                $stmt->close();
            }
        }
    }

    // Tantárgy hozzáadása
    if (isset($_POST['add_subject'])) {
        $name = trim($_POST['subject_name']);
        if (!empty($name)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Tantárgy szerkesztése
    if (isset($_POST['edit_subject'])) {
        $id = (int)$_POST['subject_id'];
        $name = trim($_POST['subject_name']);
        if ($id > 0 && !empty($name)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("UPDATE subjects SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Tantárgy törlése
    if (isset($_GET['delete_subject'])) {
        $id = (int)$_GET['delete_subject'];
        $mysqli = getConn();
        $check = $mysqli->prepare("SELECT COUNT(*) FROM marks WHERE subject_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count == 0) {
            $stmt = $mysqli->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mysqli->query("SET @new_id = 0;");
            $mysqli->query("UPDATE subjects SET id = (@new_id := @new_id + 1) ORDER BY id;");
            $mysqli->query("ALTER TABLE subjects AUTO_INCREMENT = 1;");
        } else {
            echo "<script>alert('Nem törölhető! Először töröld a hozzá tartozó jegyeket.');</script>";
        }
        header("Location: index.php?action=edit");
        exit();
    }

    // Jegy hozzáadása
    if (isset($_POST['add_mark'])) {
        $student_id = (int)$_POST['student_id'];
        $subject_id = (int)$_POST['subject_id'];
        $mark = $_POST['mark'];
        $date = $_POST['date'];
        if ($student_id > 0 && $subject_id > 0 && !empty($mark) && !empty($date)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("INSERT INTO marks (student_id, subject_id, mark, date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $student_id, $subject_id, $mark, $date);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Jegy szerkesztése
    if (isset($_POST['edit_mark'])) {
        $id = (int)$_POST['mark_id'];
        $student_id = (int)$_POST['student_id'];
        $subject_id = (int)$_POST['subject_id'];
        $mark = $_POST['mark'];
        $date = $_POST['date'];
        if ($id > 0 && $student_id > 0 && $subject_id > 0 && !empty($mark) && !empty($date)) {
            $mysqli = getConn();
            $stmt = $mysqli->prepare("UPDATE marks SET student_id = ?, subject_id = ?, mark = ?, date = ? WHERE id = ?");
            $stmt->bind_param("iiisi", $student_id, $subject_id, $mark, $date, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?action=edit");
            exit();
        }
    }

    // Jegy törlése
    if (isset($_GET['delete_mark'])) {
        $id = (int)$_GET['delete_mark'];
        $mysqli = getConn();
        $stmt = $mysqli->prepare("DELETE FROM marks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $mysqli->query("SET @new_id = 0;");
        $mysqli->query("UPDATE marks SET id = (@new_id := @new_id + 1) ORDER BY id;");
        $mysqli->query("ALTER TABLE marks AUTO_INCREMENT = 1;");
        header("Location: index.php?action=edit");
        exit();
    }

    $mysqli = getConn();
    $marks = $mysqli->query("SELECT marks.*, students.name AS student_name, subjects.name AS subject_name 
                             FROM marks 
                             JOIN students ON marks.student_id = students.id 
                             JOIN subjects ON marks.subject_id = subjects.id 
                             ORDER BY marks.id");
    $subjects = $mysqli->query("SELECT * FROM subjects");
    $students = $mysqli->query("SELECT * FROM students");
}

function showButton() {
    echo "
    <form method='post' action=''>
        <button name='create'>Create Database</button>
    </form>
    ";
}

function createDB() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'classbook';

    $mysqli = new mysqli($host, $username, $password);
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }

    $mysqli->query("DROP DATABASE IF EXISTS $database");
    $mysqli->query("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($database);

    $mysqli->query("CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year INT NOT NULL,
        name VARCHAR(10) NOT NULL
    );");

    $mysqli->query("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        class_id INT NOT NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id)
    );");

    $mysqli->query("CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    );");

    $mysqli->query("CREATE TABLE IF NOT EXISTS marks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        mark INT NOT NULL,
        date DATE NOT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    );");

    foreach (SUBJECTS as $subject) {
        $stmt = $mysqli->prepare("INSERT INTO subjects (name) VALUES (?) ON DUPLICATE KEY UPDATE name=name");
        $stmt->bind_param('s', $subject);
        $stmt->execute();
        $stmt->close();
    }

    foreach (CLASSES as $class) {
        $year = (strpos($class, '11') !== false) ? 2024 : 2025;
        $stmt = $mysqli->prepare("INSERT INTO classes (year, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name");
        $stmt->bind_param('is', $year, $class);
        $stmt->execute();
        $classId = $mysqli->insert_id;
        $stmt->close();

        $studentsCount = rand(MIN_CLASS_COUNT, MAX_CLASS_COUNT);
        for ($i = 0; $i < $studentsCount; $i++) {
            $lastname = NAMES['lastnames'][array_rand(NAMES['lastnames'])];
            $gender = (rand(0, 1) == 0) ? 'men' : 'women';
            $firstname = NAMES['firstnames'][$gender][array_rand(NAMES['firstnames'][$gender])];
            $fullName = "$lastname $firstname";

            $stmt = $mysqli->prepare("INSERT INTO students (name, class_id) VALUES (?, ?)");
            $stmt->bind_param('si', $fullName, $classId);
            $stmt->execute();
            $studentId = $mysqli->insert_id;
            $stmt->close();

            $subjectStmt = $mysqli->query("SELECT id FROM subjects");
            $subjects = $subjectStmt->fetch_all(MYSQLI_ASSOC);
            foreach ($subjects as $subject) {
                $subjectId = $subject['id'];
                $marksCount = rand(3, 5);
                for ($j = 0; $j < $marksCount; $j++) {
                    $mark = rand(1, 5);
                    $date = date('Y-m-d', strtotime("2024-09-01 +".rand(0, 180)." days"));
                    $stmt = $mysqli->prepare("INSERT INTO marks (student_id, subject_id, mark, date) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('iiis', $studentId, $subjectId, $mark, $date);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
    header("Location: index.php");
    exit();
}

function getClasses() {
    $mysqli = getConn();
    $result = $mysqli->query('SELECT * FROM classes');
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentsByClass($classId) {
    $mysqli = getConn();
    $stmt = $mysqli->prepare('SELECT * FROM students WHERE class_id = ? ORDER BY name');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getClassAverage($classId) {
    $mysqli = getConn();
    $stmt = $mysqli->prepare('SELECT AVG(marks.mark) AS average FROM marks
                              JOIN students ON marks.student_id = students.id
                              WHERE students.class_id = ?');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['average'] ?: 0;
}

function getStudentAverage($studentId) {
    $mysqli = getConn();
    $stmt = $mysqli->prepare('SELECT AVG(mark) AS average FROM marks WHERE student_id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['average'] ?: 0;
}

function getClassSubjectAverage($classId) {
    $mysqli = getConn();
    $stmt = $mysqli->prepare('SELECT subjects.name, AVG(marks.mark) AS average
                              FROM marks
                              JOIN students ON marks.student_id = students.id
                              JOIN subjects ON marks.subject_id = subjects.id
                              WHERE students.class_id = ?
                              GROUP BY subjects.id');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTop10Students() {
    $mysqli = getConn();
    $stmt = $mysqli->prepare('SELECT students.name, AVG(marks.mark) AS average
                              FROM marks
                              JOIN students ON marks.student_id = students.id
                              GROUP BY students.id
                              ORDER BY average DESC
                              LIMIT 10');
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getHallOfFame() {
    $classes = getClasses();
    $bestClass = null;
    $bestClassAverage = 0;

    foreach ($classes as $class) {
        $classAverage = getClassAverage($class['id']);
        if ($classAverage > $bestClassAverage) {
            $bestClass = $class;
            $bestClassAverage = $classAverage;
        }
    }

    $topStudents = getTop10Students();
    return [
        'best_class' => $bestClass,
        'top_students' => $topStudents
    ];
}

function getSubjects() {
    $mysqli = getConn();
    $result = $mysqli->query('SELECT * FROM subjects');
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Classbook</title>
</head>
<body>

<nav>
    <a href="?">Home</a>
    <a href="?action=classes">Classes</a>
    <a href="?action=top_students">Top 10 Students</a>
    <a href="?action=hall_of_fame">Hall of Fame</a>
    <a href="?action=edit">Edit</a>
</nav>

<div class="container">
    <?php
    $mysqli = getConn();
    if (isset($_GET['action']) && $_GET['action'] == 'classes') {
        $classes = getClasses();
        echo "<div class='content'>";
        echo "<h2>Classes</h2><table><tr><th>Class Name</th><th>Year</th><th>Average Grade</th></tr>";
        foreach ($classes as $class) {
            $classAverage = getClassAverage($class['id']);
            echo "<tr><td><a href='?action=class_students&class_id={$class['id']}'>{$class['name']}</a></td><td>{$class['year']}</td><td>{$classAverage}</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'class_students' && isset($_GET['class_id'])) {
        $classId = $_GET['class_id'];
        $students = getStudentsByClass($classId);
        $classAverage = getClassAverage($classId);
        $subjectAverages = getClassSubjectAverage($classId);

        echo "<div class='content'>";
        echo "<h2>Students</h2><table><tr><th>Student Name</th><th>Average</th></tr>";
        foreach ($students as $student) {
            $studentAverage = getStudentAverage($student['id']);
            echo "<tr><td>{$student['name']}</td><td>{$studentAverage}</td></tr>";
        }
        echo "</table>";
        echo "<h3>Class Average: {$classAverage}</h3>";
        echo "<h2>Class Subject Averages</h2><table><tr><th>Subject</th><th>Average</th></tr>";
        foreach ($subjectAverages as $subject) {
            echo "<tr><td>{$subject['name']}</td><td>{$subject['average']}</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'top_students') {
        $top10Students = getTop10Students();
        echo "<div class='content'>";
        echo "<h2>Top 10 Students</h2><table><tr><th>Student Name</th><th>Average Grade</th></tr>";
        foreach ($top10Students as $student) {
            echo "<tr><td>{$student['name']}</td><td>{$student['average']}</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'hall_of_fame') {
        $hallOfFame = getHallOfFame();
        echo "<div class='content'>";
        echo "<h2>Hall of Fame</h2>";
        echo "<h3>Top Class: {$hallOfFame['best_class']['name']}</h3>";
        echo "<h4>Top 10 Students:</h4><table><tr><th>Student Name</th><th>Average Grade</th></tr>";
        foreach ($hallOfFame['top_students'] as $student) {
            echo "<tr><td>{$student['name']}</td><td>{$student['average']}</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $classes = getClasses();
        $students = $mysqli->query("SELECT * FROM students ORDER BY class_id, name")->fetch_all(MYSQLI_ASSOC);
        $subjects = getSubjects();
        $marks = $mysqli->query("SELECT marks.*, students.name AS student_name, subjects.name AS subject_name 
                                 FROM marks 
                                 JOIN students ON marks.student_id = students.id 
                                 JOIN subjects ON marks.subject_id = subjects.id 
                                 ORDER BY marks.id")->fetch_all(MYSQLI_ASSOC);

        echo "<div class='form-section'>";
        echo "<h2>Manage Classes</h2>";
        echo "<h3>Add New Class</h3>";
        echo "<form method='post' action=''>";
        echo "<input type='text' name='class_name' placeholder='Class Name' required>";
        echo "<input type='number' name='class_year' placeholder='Year' required>";
        echo "<button type='submit' name='add_class'>Add Class</button>";
        echo "</form>";
        echo "<h3>Existing Classes</h3>";
        echo "<table><tr><th>ID</th><th>Name</th><th>Year</th><th>Actions</th></tr>";
        foreach ($classes as $class) {
            echo "<tr>";
            echo "<td>{$class['id']}</td>";
            echo "<td>{$class['name']}</td>";
            echo "<td>{$class['year']}</td>";
            echo "<td>";
            echo "<form method='post' action='' style='display:inline;'>";
            echo "<input type='hidden' name='class_id' value='{$class['id']}'>";
            echo "<input type='text' name='class_name' value='{$class['name']}' required>";
            echo "<input type='number' name='class_year' value='{$class['year']}' required>";
            echo "<button type='submit' name='edit_class'>Edit</button>";
            echo "</form>";
            echo " | <a href='?action=edit&delete_class={$class['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='form-section'>";
        echo "<h2>Manage Students</h2>";
        echo "<h3>Add New Student</h3>";
        echo "<form method='post' action=''>";
        echo "<input type='text' name='student_name' placeholder='Student Name' required>";
        echo "<select name='class_id' required>";
        foreach ($classes as $class) {
            echo "<option value='{$class['id']}'>{$class['name']} ({$class['year']})</option>";
        }
        echo "</select>";
        echo "<button type='submit' name='add_student'>Add Student</button>";
        echo "</form>";
        echo "<h3>Existing Students</h3>";
        echo "<table><tr><th>ID</th><th>Name</th><th>Class</th><th>Actions</th></tr>";
        foreach ($students as $student) {
            $class = array_filter($classes, fn($c) => $c['id'] == $student['class_id'])[array_key_first(array_filter($classes, fn($c) => $c['id'] == $student['class_id']))];
            echo "<tr>";
            echo "<td>{$student['id']}</td>";
            echo "<td>{$student['name']}</td>";
            echo "<td>{$class['name']}</td>";
            echo "<td>";
            echo "<form method='post' action='' style='display:inline;'>";
            echo "<input type='hidden' name='student_id' value='{$student['id']}'>";
            echo "<input type='text' name='student_name' value='{$student['name']}' required>";
            echo "<select name='class_id' required>";
            foreach ($classes as $c) {
                $selected = $c['id'] == $student['class_id'] ? 'selected' : '';
                echo "<option value='{$c['id']}' $selected>{$c['name']} ({$c['year']})</option>";
            }
            echo "</select>";
            echo "<button type='submit' name='edit_student'>Edit</button>";
            echo "</form>";
            echo " | <a href='?action=edit&delete_student={$student['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='form-section'>";
        echo "<h2>Manage Subjects</h2>";
        echo "<h3>Add New Subject</h3>";
        echo "<form method='post' action=''>";
        echo "<input type='text' name='subject_name' placeholder='Subject Name' required>";
        echo "<button type='submit' name='add_subject'>Add Subject</button>";
        echo "</form>";
        echo "<h3>Existing Subjects</h3>";
        echo "<table><tr><th>ID</th><th>Name</th><th>Actions</th></tr>";
        foreach ($subjects as $subject) {
            echo "<tr>";
            echo "<td>{$subject['id']}</td>";
            echo "<td>{$subject['name']}</td>";
            echo "<td>";
            echo "<form method='post' action='' style='display:inline;'>";
            echo "<input type='hidden' name='subject_id' value='{$subject['id']}'>";
            echo "<input type='text' name='subject_name' value='{$subject['name']}' required>";
            echo "<button type='submit' name='edit_subject'>Edit</button>";
            echo "</form>";
            echo " | <a href='?action=edit&delete_subject={$subject['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='form-section'>";
        echo "<h2>Manage Marks</h2>";
        echo "<h3>Add New Mark</h3>";
        echo "<form method='post' action=''>";
        echo "<select name='student_id' required>";
        foreach ($students as $student) {
            echo "<option value='{$student['id']}'>{$student['name']}</option>";
        }
        echo "</select>";
        echo "<select name='subject_id' required>";
        foreach ($subjects as $subject) {
            echo "<option value='{$subject['id']}'>{$subject['name']}</option>";
        }
        echo "</select>";
        echo "<input type='number' name='mark' min='1' max='5' placeholder='Mark (1-5)' required>";
        echo "<input type='date' name='date' required>";
        echo "<button type='submit' name='add_mark'>Add Mark</button>";
        echo "</form>";
        echo "<h3>Existing Marks</h3>";
        echo "<table><tr><th>ID</th><th>Student</th><th>Subject</th><th>Mark</th><th>Date</th><th>Actions</th></tr>";
        foreach ($marks as $mark) {
            echo "<tr>";
            echo "<td>{$mark['id']}</td>";
            echo "<td>{$mark['student_name']}</td>";
            echo "<td>{$mark['subject_name']}</td>";
            echo "<td>{$mark['mark']}</td>";
            echo "<td>{$mark['date']}</td>";
            echo "<td>";
            echo "<form method='post' action='' style='display:inline;'>";
            echo "<input type='hidden' name='mark_id' value='{$mark['id']}'>";
            echo "<select name='student_id' required>";
            foreach ($students as $student) {
                $selected = $student['id'] == $mark['student_id'] ? 'selected' : '';
                echo "<option value='{$student['id']}' $selected>{$student['name']}</option>";
            }
            echo "</select>";
            echo "<select name='subject_id' required>";
            foreach ($subjects as $subject) {
                $selected = $subject['id'] == $mark['subject_id'] ? 'selected' : '';
                echo "<option value='{$subject['id']}' $selected>{$subject['name']}</option>";
            }
            echo "</select>";
            echo "<input type='number' name='mark' value='{$mark['mark']}' min='1' max='5' required>";
            echo "<input type='date' name='date' value='{$mark['date']}' required>";
            echo "<button type='submit' name='edit_mark'>Edit</button>";
            echo "</form>";
            echo " | <a href='?action=edit&delete_mark={$mark['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class='content'>";
        echo "<h2>Welcome to School Classbook</h2>";
        echo "</div>";
    }
    ?>
</div>

</body>
</html>