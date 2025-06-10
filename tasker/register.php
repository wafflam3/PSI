<?php
require_once 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Walidacja
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "Wszystkie pola są wymagane.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nieprawidłowy email.";
    }

    if ($password !== $confirm) {
        $errors[] = "Hasła się nie zgadzają.";
    }

    if (empty($errors)) {
        // Sprawdź, czy email/username już istnieje
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = "Użytkownik o takim emailu lub nazwie już istnieje.";
        } else {
            // Zapisz użytkownika
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
</head>
<body>
    <h2>Rejestracja</h2>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <label>Nazwa użytkownika: <input type="text" name="username"></label><br>
        <label>Email: <input type="email" name="email"></label><br>
        <label>Hasło: <input type="password" name="password"></label><br>
        <label>Potwierdź hasło: <input type="password" name="confirm"></label><br>
        <button type="submit">Zarejestruj się</button>
    </form>

    <p><a href="login.php">Masz konto? Zaloguj się</a></p>
</body>
</html>