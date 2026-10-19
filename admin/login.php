<?php
// login.php
require __DIR__ . '/../config/config.php';

// nama syarikat untuk branding (fallback jika tiada)
$pdo_lbl = new PDO($dsn, $db_user, $db_pass, $options);
$settings_lbl = $pdo_lbl->query("SELECT company_name FROM settings ORDER BY id DESC LIMIT 1")->fetch();
$brand_name = $settings_lbl['company_name'] ?? 'Kedai Mini Online';

// redirect if already logged in
if (!empty($_SESSION['is_admin'])) {
    header('location: admin.php');
    exit;
}

$error = '';

// --- Limit Login Attempts ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lock_time'])) {
    $_SESSION['lock_time'] = 0;
}

// Check lock status
$locked = false;
if ($_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['lock_time'] < 300) { 
        $locked = true;
        $error = 'terlalu banyak percubaan gagal. tunggu 5 minit sebelum cuba semula.';
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

// generate new math values
if (empty($_SESSION['math_a']) || empty($_SESSION['math_b'])) {
    $_SESSION['math_a'] = rand(1, 9);
    $_SESSION['math_b'] = rand(1, 9);
}

$math_a = $_SESSION['math_a'];
$math_b = $_SESSION['math_b'];
$answer_correct = false;

// PROCESS LOGIN FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    $math_answer = trim($_POST['math'] ?? '');

    if ($math_answer == ($math_a + $math_b)) {
        $answer_correct = true;
    } else {
        $error = 'jawapan keselamatan matematik salah.';
    }

    if ($answer_correct) {
        // semak username & password (guna password_verify untuk hash)
        if ($user === $admin_user && password_verify($pass, $admin_pass_hash)) {
            session_regenerate_id(true); // elak session fixation
            $_SESSION['is_admin'] = true;
            $_SESSION['login_attempts'] = 0;
            header('location: admin.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_time'] = time();
            }
            $error = 'nama pengguna atau kata laluan salah.';
        }

    }

    // regenerate math
    $_SESSION['math_a'] = rand(1, 9);
    $_SESSION['math_b'] = rand(1, 9);
    $math_a = $_SESSION['math_a'];
    $math_b = $_SESSION['math_b'];
}
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<title>Log Masuk Admin | <?php echo htmlspecialchars($brand_name, ENT_QUOTES, 'utf-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<script src="https://cdn.tailwindcss.com"></script>

<style>
body { box-sizing: border-box; }
html, body { height: 100%; width: 100%; margin: 0; padding: 0; }

.app-wrapper { width: 100%; height: 100%; }
@keyframes float { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-20px) rotate(5deg); } }
@keyframes pulse-glow { 0%, 100% { opacity: 0.5; } 50% { opacity: 0.8; } }

.float-animation { animation: float 6s ease-in-out infinite; }
.float-animation-delayed { animation: float 6s ease-in-out infinite; animation-delay: 2s; }
.pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }

.login-card { backdrop-filter: blur(20px); background: rgba(255,255,255,0.95); }
.input-field { transition: all 0.3s ease; }
.input-field:focus { transform: translateY(-2px); }
</style>

</head>
<body>

<div class="app-wrapper flex items-center justify-center p-4">

    <!-- background effects -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-64 h-64 rounded-full pulse-glow float-animation"
             style="top:10%; left:10%; background: radial-gradient(circle, rgba(139,92,246,0.3) 0%, transparent 70%);"></div>

        <div class="absolute w-96 h-96 rounded-full pulse-glow float-animation-delayed"
             style="bottom:10%; right:10%; background: radial-gradient(circle, rgba(236,72,153,0.3) 0%, transparent 70%);"></div>
    </div>

    <main class="w-full max-w-md relative z-10">
        <div class="login-card rounded-3xl shadow-2xl p-8 md:p-10">

            <!-- logo -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg bg-purple-500 text-white">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            <!-- title -->
            <header class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">Selamat Kembali</h1>
                <p class="text-base opacity-70"><?php echo htmlspecialchars($brand_name, ENT_QUOTES, 'utf-8'); ?> &middot; Panel Admin</p>
            </header>

            <!-- login form -->
            <form method="post" class="space-y-5">

                <div>
                    <label class="block text-sm font-semibold mb-2">Nama pengguna</label>
                    <input type="text" name="user" class="input-field w-full px-4 py-3 rounded-xl border-2"
                           placeholder="masukkan nama pengguna" autocomplete="username" autofocus required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Kata laluan</label>
                    <div class="relative">
                        <input type="password" name="pass" id="passField" class="input-field w-full px-4 py-3 pr-12 rounded-xl border-2"
                               placeholder="masukkan kata laluan" autocomplete="current-password" required>
                        <button type="button" id="togglePass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-sm opacity-60 hover:opacity-100"
                                aria-label="Papar/sorok kata laluan">👁️</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Keselamatan</label>

                    <div class="flex items-center gap-3">
                        <div class="flex-1 px-4 py-3 rounded-xl border-2 text-center font-semibold bg-gray-100">
                            <?= $math_a ?> + <?= $math_b ?> = ?
                        </div>

                        <input type="number" name="math" class="input-field w-24 px-4 py-3 rounded-xl border-2 text-center"
                               placeholder="?" required>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl text-base font-bold bg-purple-600 text-white 
                       shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                    log masuk
                </button>

                <?php if (!empty($error)) : ?>
                    <div class="mt-3 p-3 text-center rounded-xl bg-red-200 text-red-800 text-sm font-medium">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'utf-8') ?>
                    </div>
                <?php endif; ?>

            </form>

            <footer class="mt-8 text-center text-xs opacity-60">
                Dilindungi dengan keselamatan tahap tinggi 🔒
                <br>
                <a href="../index.php" class="underline hover:opacity-100">&larr; Kembali ke laman utama</a>
            </footer>

        </div>
    </main>
</div>

<script>
document.getElementById('togglePass').addEventListener('click', function(){
    const f = document.getElementById('passField');
    f.type = (f.type === 'password') ? 'text' : 'password';
    this.textContent = (f.type === 'password') ? '👁️' : '🙈';
});
</script>

</body>
</html>
