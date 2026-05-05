<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="utf-8" />
    <title>Login Perpustakaan Polije</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #f7f8fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }

        .login-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .header-logo { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; justify-content: center; }
        .header-logo img { width: 30px; height: 30px; }
        .brand-name-login { font-weight: 700; color: #0050ad; font-size: 16px; }

        .login-title { text-align: center; margin-bottom: 25px; }
        .login-title h1 { font-size: 20px; color: #0b1220; margin-bottom: 8px; }
        .login-title p { font-size: 13px; color: #8b95a1; }

        .login-form { display: flex; flex-direction: column; gap: 15px; }
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 13px; font-weight: 600; color: #0b1220; }
        .input-group input {
            width: 100%; height: 46px; padding: 0 14px;
            background-color: #f1f6ff; border: 1px solid transparent;
            border-radius: 8px; font-size: 13px; transition: 0.3s;
        }
        .input-group input:focus { outline: none; border-color: #0050ad; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 80, 173, 0.1); }

        .btn-login {
            width: 100%; height: 46px; background-color: #0050ad;
            color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn-login:hover { background-color: #003d85; }

        /* DIVIDER */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #b0b8c1;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ebedf0; }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        /* TOMBOL GOOGLE SSO DI BAWAH */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 48px;
            background-color: #ffffff;
            border: 1px solid #dcdfe6;
            border-radius: 8px;
            color: #3c4043;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-google:hover { background-color: #f8f9fa; border-color: #c0c4cc; }

        .footer-note { margin-top: 20px; text-align: center; font-size: 12px; color: #8b95a1; }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="header-logo">
            <img src="../user/gambar/logo-polije.png" alt="logo">
            <span class="brand-name-login">Perpustakaan Polije</span>
        </div>

        <div class="login-title">
            <h1>Selamat Datang</h1>
            <p>Silakan masuk untuk mengakses sistem perpustakaan.</p>
        </div>

        <!-- FORM MANUAL UNTUK ADMIN (SEKARANG DI ATAS) -->
        <form action="proses-login.php" method="POST" class="login-form">
            <div class="input-group">
                <label for="email">Email Admin</label>
                <input type="text" id="email" name="email" placeholder="email@perpus.com" required>
            </div>

            <div class="input-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="" required>
            </div>

            <button type="submit" name="login_admin" class="btn-login">Masuk Sebagai Admin</button>
        </form>

        <div class="divider">Atau Mahasiswa</div>

        <!-- TOMBOL SSO UNTUK MAHASISWA (SEKARANG DI BAWAH) -->
        <a href="proses-login.php" class="btn-google">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="18" alt="G">
            Masuk dengan Akun SSO
        </a>

        <div class="footer-note">
            Sistem Informasi Perpustakaan v1.0
        </div>
    </div>
</body>
</html>