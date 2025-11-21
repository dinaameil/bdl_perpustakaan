<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #0f172a; /* Warna background gelap modern */
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .brand-title {
            color: #4361ee;
            font-weight: 700;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #4361ee;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #3a0ca3;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-title">
            <i class="fas fa-book-open"></i> PERPUS<span style="color: #0f172a;">APP</span>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center p-2 mb-3" style="font-size: 0.9rem;">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">USERNAME</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">PASSWORD</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-pill">MASUK KE SISTEM</button>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">Kelompok: Dina, Citra, Giska</small>
        </div>
    </div>

</body>
</html>