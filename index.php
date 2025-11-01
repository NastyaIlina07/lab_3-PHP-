<?php
session_start();
require 'vendor/autoload.php';

// Подключение к базе
$PDO = new PDO('mysql:dbname=php_users;host=localhost;charset=utf8mb4', 'root', '');
$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $bg_color = $_POST['bg_color'] ?? '#ffffff';
    $text_color = $_POST['text_color'] ?? '#000000';

    if (empty($username) || empty($password)) {
        $error = "Имя и пароль обязательны!";
    } else {
        $stmt = $PDO->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Пользователь с таким именем уже существует!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $PDO->prepare("INSERT INTO users(username, password, bg_color, text_color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hash, $bg_color, $text_color]);
            $success = "Регистрация прошла успешно!";
        }
    }
}

// вход
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $loginError = "Имя и пароль обязательны!";
    } else {
        $stmt = $PDO->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Сохраняем настройки пользователя в cookies на 30 дней
            setcookie('bg_color', $user['bg_color'], time() + 30*24*3600, '/');
            setcookie('text_color', $user['text_color'], time() + 30*24*3600, '/');

            header("Location: index.php");
            exit;
        } else {
            $loginError = "Неверное имя или пароль!";
        }
    }
}

// выход
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('bg_color', '', time() - 3600, '/');
    setcookie('text_color', '', time() - 3600, '/');
    header('Location: index.php');
    exit;
}


$bg = $_COOKIE['bg_color'] ?? '#ffffff';
$text = $_COOKIE['text_color'] ?? '#000000';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($_SESSION['user_id']) ? 'Личный кабинет' : 'Регистрация / Вход'; ?></title>
    <style>
        body {
            background-color: <?php echo htmlspecialchars($bg); ?>;
            color: <?php echo htmlspecialchars($text); ?>;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .form-container {
            max-width: 500px;
            margin: 20px 0;
        }
        input[type="text"], input[type="password"], input[type="color"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
    <h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Ваши настройки применены.</p>
    <a href="?logout">Выйти</a>

    <div class="form-container">
        <h2>Изменить настройки</h2>
        <form method="post" action="save_settings.php">
            <label>Фон: <input type="color" name="bg_color" value="<?php echo htmlspecialchars($bg); ?>"></label><br>
            <label>Текст: <input type="color" name="text_color" value="<?php echo htmlspecialchars($text); ?>"></label><br>
            <button type="submit">Сохранить</button>
        </form>
    </div>

<?php else: ?>
    <div class="form-container">
        <h2>Регистрация</h2>
        <?php if (!empty($error)) echo '<p class="error">' . htmlspecialchars($error) . '</p>'; ?>
        <?php if (!empty($success)) echo '<p class="success">' . htmlspecialchars($success) . '</p>'; ?>
        <form method="post">
            <p>Имя:</p>
            <input type="text" name="username" placeholder="Введите ваше имя" required>
            <p>Пароль:</p>
            <input type="password" name="password" placeholder="Введите пароль" required>
            <p>Цвет фона:</p>
            <input type="color" name="bg_color" value="#ffffff">
            <p>Цвет текста:</p>
            <input type="color" name="text_color" value="#000000">
            <button type="submit" name="register">Зарегистрироваться</button>
        </form>
    </div>

    <div class="form-container">
        <h2>Вход</h2>
        <?php if (!empty($loginError)) echo '<p class="error">' . htmlspecialchars($loginError) . '</p>'; ?>
        <form method="post">
            <p>Имя:</p>
            <input type="text" name="username" placeholder="Ваше имя" required>
            <p>Пароль:</p>
            <input type="password" name="password" placeholder="Ваш пароль" required>
            <button type="submit" name="login">Войти</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>

