<?php
declare(strict_types=1);

session_start();

const DB_HOST = '127.0.0.1';
const DB_NAME = 'nbmdb';
const DB_USER = 'app_user';
const DB_PASS = 'StrongPassword123!';

ini_set('display_errors', '1');
error_reporting(E_ALL);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || !is_string($sessionToken) || !hash_equals($sessionToken, $token)) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

function current_username(): string
{
    return is_string($_SESSION['username'] ?? null) ? $_SESSION['username'] : '';
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function ensure_schema(): void
{
    $pdo = db();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM products");
    $count = (int)($countStmt->fetch()['total'] ?? 0);

    if ($count === 0) {
        $products = [
            ['NBM UltraBook 14', 'Lightweight 14-inch laptop with 16GB RAM and 512GB SSD.', 799.99, 12],
            ['NBM ProStation X', 'High-performance desktop for office and productivity workloads.', 1199.50, 7],
            ['NBM SmartView 27', '27-inch IPS monitor suitable for work and media.', 229.99, 18],
            ['NBM PocketPhone S', 'Compact smartphone with OLED display and 128GB storage.', 649.00, 21],
            ['NBM GamerCore 15', '15.6-inch performance laptop with dedicated graphics.', 1399.99, 5],
            ['NBM SoundBuds Air', 'Wireless earbuds with charging case and noise reduction.', 89.90, 40],
            ['NBM TypeBoard Pro', 'Mechanical keyboard with white backlighting.', 109.95, 15],
            ['NBM ClickMouse V2', 'Wireless ergonomic mouse for daily use.', 39.99, 30],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, stock)
            VALUES (:name, :description, :price, :stock)
        ");

        foreach ($products as [$name, $description, $price, $stock]) {
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':stock' => $stock,
            ]);
        }
    }
}

function find_products(?string $query): array
{
    $pdo = db();

    if ($query === null || trim($query) === '') {
        $stmt = $pdo->query("
            SELECT id, name, description, price, stock
            FROM products
            ORDER BY created_at DESC, id DESC
        ");
        return $stmt->fetchAll();
    }

    $search = '%' . trim($query) . '%';

    $stmt = $pdo->prepare("
        SELECT id, name, description, price, stock
        FROM products
        WHERE name LIKE :search_name OR description LIKE :search_description
        ORDER BY created_at DESC, id DESC
    ");

    $stmt->execute([
        ':search_name' => $search,
        ':search_description' => $search,
    ]);

    return $stmt->fetchAll();
}

ensure_schema();

$action = $_GET['action'] ?? 'home';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        verify_csrf();

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            flash_set('error', 'All registration fields are required.');
            redirect('?action=register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Please enter a valid email address.');
            redirect('?action=register');
        }

        if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            flash_set('error', 'Username must be between 3 and 50 characters.');
            redirect('?action=register');
        }

        if (strlen($password) < 8) {
            flash_set('error', 'Password must be at least 8 characters long.');
            redirect('?action=register');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = db()->prepare("
                INSERT INTO users (username, email, password_hash)
                VALUES (:username, :email, :password_hash)
            ");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);

            flash_set('success', 'Registration successful. You can now log in.');
            redirect('?action=login');
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                flash_set('error', 'Username or email already exists.');
                redirect('?action=register');
            }
            throw $e;
        }
    }

    if ($action === 'login') {
        verify_csrf();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            flash_set('error', 'Username and password are required.');
            redirect('?action=login');
        }

        $stmt = db()->prepare("
            SELECT id, username, password_hash
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            flash_set('error', 'Invalid login credentials.');
            redirect('?action=login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = (string)$user['username'];

        flash_set('success', 'You have logged in successfully.');
        redirect('?action=home');
    }

    if ($action === 'logout') {
        verify_csrf();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        session_start();
        flash_set('success', 'You have been logged out.');
        redirect('?action=home');
    }
}

$flash = flash_get();
$searchQuery = trim((string)($_GET['q'] ?? ''));
$products = find_products($searchQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NBM </title>
    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #1d4ed8;
            --accent-dark: #1e40af;
            --border: #dbe2ea;
            --success-bg: #ecfdf5;
            --success-text: #065f46;
            --error-bg: #fef2f2;
            --error-text: #991b1b;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            width: min(1100px, 92%);
            margin: 0 auto;
        }

        header {
            background: #0f172a;
            color: white;
            padding: 18px 0;
            margin-bottom: 28px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 1.4rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a, .nav-links button {
            text-decoration: none;
            color: white;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.22);
            padding: 9px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .nav-links a:hover, .nav-links button:hover {
            background: rgba(255,255,255,0.08);
        }

        .hero, .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .hero {
            margin-bottom: 24px;
        }

        .hero h1 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.9rem;
        }

        .hero p {
            color: var(--muted);
            margin-bottom: 0;
            line-height: 1.55;
        }

        .flash {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .flash.success {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: #a7f3d0;
        }

        .flash.error {
            background: var(--error-bg);
            color: var(--error-text);
            border-color: #fecaca;
        }

        .search-box {
            margin-bottom: 24px;
        }

        form.inline-search {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            background: white;
        }

        .inline-search input[type="text"] {
            flex: 1 1 280px;
        }

        button, .btn {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.98rem;
        }

        button:hover, .btn:hover {
            background: var(--accent-dark);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
            gap: 18px;
        }

        .product h3 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .product p {
            color: var(--muted);
            line-height: 1.5;
            min-height: 72px;
        }

        .price {
            font-weight: bold;
            font-size: 1.15rem;
            margin-top: 12px;
        }

        .stock {
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .auth-wrap {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .muted {
            color: var(--muted);
        }

        footer {
            padding: 30px 0 50px;
            color: var(--muted);
            text-align: center;
        }

        .small {
            font-size: 0.92rem;
        }

        .welcome {
            color: #cbd5e1;
            font-size: 0.95rem;
        }

        .empty {
            text-align: center;
            padding: 26px;
            color: var(--muted);
        }
    </style>
</head>
<body>
<header>
    <div class="container nav">
        <div class="brand">NBM</div>
        <div class="nav-links">
            <a href="?action=home">Home</a>
            <a href="?action=home#products">Products</a>
            <?php if (is_logged_in()): ?>
                <span class="welcome">Signed in as <?= e(current_username()) ?></span>
                <form method="post" action="?action=logout" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="?action=login">Login</a>
                <a href="?action=register">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?php if ($flash): ?>
        <div class="flash <?= e((string)$flash['type']) ?>">
            <?= e((string)$flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'register'): ?>
        <section class="card" style="max-width: 560px; margin: 0 auto 24px;">
            <h2>Create Account</h2>
            <p class="muted">Register a new user account.</p>

            <form method="post" action="?action=register" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" maxlength="255" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" minlength="8" required>
                </div>

                <button type="submit">Register</button>
            </form>
        </section>

    <?php elseif ($action === 'login'): ?>
        <section class="card" style="max-width: 560px; margin: 0 auto 24px;">
            <h2>Login</h2>
            <p class="muted">Use your account credentials to sign in.</p>

            <form method="post" action="?action=login" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <button type="submit">Login</button>
            </form>
        </section>

    <?php else: ?>
        <section class="card search-box">
            <h2>Search Products</h2>
            <form class="inline-search" method="get" action="">
                <input type="hidden" name="action" value="home">
                <input
                    type="text"
                    name="q"
                    value="<?= e($searchQuery) ?>"
                    placeholder="Search laptops, phones, monitors..."
                    maxlength="100"
                >
                <button type="submit">Search</button>
                <a class="btn" href="?action=home">Clear</a>
            </form>
        </section>

        <section id="products">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:14px;">
                <h2 style="margin:0;">Products</h2>
                <span class="muted small"><?= count($products) ?> item(s) found</span>
            </div>

            <?php if (!$products): ?>
                <div class="card empty">
                    No products matched your search.
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($products as $product): ?>
                        <article class="card product">
                            <h3><?= e((string)$product['name']) ?></h3>
                            <p><?= e((string)$product['description']) ?></p>
                            <div class="price">£<?= e(number_format((float)$product['price'], 2)) ?></div>
                            <div class="stock">Stock: <?= e((string)$product['stock']) ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<footer>
    <div class="container">

    </div>
</footer>
</body>
</html>
