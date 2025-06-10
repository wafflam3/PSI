<?php
session_start();
require_once 'includes/db.php';

if (isset($_POST['delete_account'])) {
    $userId = $_SESSION['user_id'];

    // Usuń zadania użytkownika
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Usuń konto
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    // Zakończ sesję i przekieruj
    session_destroy();
    header("Location: register.php");
    exit;
}


if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['save_edit'])) {
    $editId = $_POST['edit_id'];
    $newDescription = $_POST['edit_description'];

    $stmt = $pdo->prepare("UPDATE tasks SET description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$newDescription, $editId, $_SESSION['user_id']]);

    header("Location: dashboard.php");
    exit;
}

// Dodawanie zadania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
    $task = trim($_POST['task']);
    if ($task !== '') {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $task]);
    }
}

// Usuwanie zadania
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
}

// Oznaczenie jako wykonane/niewykonane
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE tasks SET done = NOT done WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['toggle'], $_SESSION['user_id']]);
}

// Edycja zadania
if (isset($_GET['edit']) && isset($_POST['new_task'])) {
    $stmt = $pdo->prepare("UPDATE tasks SET description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['new_task'], $_GET['edit'], $_SESSION['user_id']]);
}

// Pobierz zadania
$sortOrder = isset($_GET['sort']) && $_GET['sort'] == 'asc' ? 'ASC' : 'DESC';
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT id, description, done FROM tasks WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filter == 'done') {
    $sql .= " AND done = 1";
} elseif ($filter == 'not_done') {
    $sql .= " AND done = 0";
}

$sql .= " ORDER BY id $sortOrder";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();


// Policz liczbę wykonanych i niewykonanych zadań
$stmtDone = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND done = 1");
$stmtDone->execute([$_SESSION['user_id']]);
$doneCount = $stmtDone->fetchColumn();

$stmtNotDone = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND done = 0");
$stmtNotDone->execute([$_SESSION['user_id']]);
$notDoneCount = $stmtNotDone->fetchColumn();
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Witaj, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

        <form method="post">
            <label>Nowe zadanie:
                <input type="text" name="task" required>
            </label>
            <button type="submit">Dodaj</button>
        </form>

        <form method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć swoje konto? To działanie jest nieodwracalne.');">
            <button type="submit" name="delete_account" style="color: red;">❌ Usuń konto</button>
        </form>


        <form method="post" style="text-align: right;">
            <button type="submit" name="logout">Wyloguj się</button>
        </form>

        <h3>Twoje zadania:</h3>
        <p>Wykonane zadania: <?= $doneCount ?> | Niewykonane zadania: <?= $notDoneCount ?></p>
        
        <form method="get">
            <label>Sortuj po:
                <select name="sort" onchange="this.form.submit()">
                 <option value="desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'desc' ? 'selected' : '' ?>>Najnowsze</option>
                 <option value="asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'asc' ? 'selected' : '' ?>>Najstarsze</option>
                </select>
            </label>
        </form>

    <form method="get">
        <label>Filtruj:
            <select name="filter" onchange="this.form.submit()">
                <option value="all" <?= (!isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'selected' : '' ?>>Wszystkie</option>
                <option value="done" <?= (isset($_GET['filter']) && $_GET['filter'] == 'done') ? 'selected' : '' ?>>Wykonane</option>
                <option value="not_done" <?= (isset($_GET['filter']) && $_GET['filter'] == 'not_done') ? 'selected' : '' ?>>Niewykonane</option>
            </select>
        </label>
    </form>

<?php
    if (isset($_GET['edit'])) {
        $editId = $_GET['edit'];
        $stmt = $pdo->prepare("SELECT description FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$editId, $_SESSION['user_id']]);
        $editTask = $stmt->fetch();

        if ($editTask):
?>
        <form method="post">
            <input type="hidden" name="edit_id" value="<?= $editId ?>">
            <input type="text" name="edit_description" value="<?= htmlspecialchars($editTask['description']) ?>" required>
            <button type="submit" name="save_edit">Zapisz zmiany</button>
        </form>
<?php
    endif;
}
?>

        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <span class="<?= $task['done'] ? 'done' : '' ?>">
                        <?= htmlspecialchars($task['description']) ?>
                    </span>
                    [<a href="?toggle=<?= $task['id'] ?>">✔/✘</a>]
                    [<a href="?delete=<?= $task['id'] ?>" onclick="return confirm('Na pewno usunąć to zadanie?')">🗑</a>]
                    [<a href="?edit=<?= $task['id'] ?>">✏️ Edytuj</a>]
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        if (isset($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT description FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
            $taskToEdit = $stmt->fetch();
        ?>
            <h3>Edytuj zadanie</h3>
            <form method="post">
                <input type="text" name="new_task" value="<?= htmlspecialchars($taskToEdit['description']) ?>" required>
                <button type="submit">Zapisz zmiany</button>
            </form>
        <?php } ?>

        <p><a href="logout.php">Wyloguj się</a></p>
    </div>
</body>
</html>