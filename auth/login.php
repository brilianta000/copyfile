<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="utf-8" />
    <title>Login Perpustakaan Polije</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

html, body {
    height: 100%;
}

body {
    background-color: #f7f8fa;
    overflow: hidden; 
}


.login-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
    align-items: center;
    justify-content: center;
    padding: 0; 
}

.login-card {
    background-color: #ffffff;
    width: 100%;
    max-width: 400px;

    padding: 28px 26px;

    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);

    display: flex;
    flex-direction: column;
    justify-content: center;

    max-height: 92vh; 
}

.header-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
}

.header-logo img {
    width: 24px;
    height: 24px;
}

.brand-name-login {
    font-weight: 700;
    color: #0050ad;
    font-size: 14px;
}

.login-title {
    text-align: center;
    margin-bottom: 18px;
}

.login-title h1 {
    font-size: 20px;
    font-weight: 700;
    color: #0b1220;
    margin-bottom: 6px;
}

.login-title p {
    font-size: 13px;
    color: #8b95a1;
    line-height: 1.4;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.input-group label {
    font-size: 13px;
    font-weight: 600;
    color: #0b1220;
}

.input-group input {
    width: 100%;
    height: 46px;
    padding: 0 14px;
    background-color: #f1f6ff;
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 13px;
    transition: 0.3s;
}

.input-group input:focus {
    outline: none;
    border-color: #0050ad;
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(0, 80, 173, 0.1);
}

.forgot-link {
    text-align: right;
    margin-top: -4px;
}

.forgot-link a {
    font-size: 13px;
    color: #0050ad;
    text-decoration: none;
    font-weight: 600;
}

.btn-login {
    width: 100%;
    height: 46px;
    background-color: #0050ad;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 6px;
    transition: 0.3s;
}

.btn-login:hover {
    background-color: #003d85;
}

.register-footer {
    margin-top: 18px;
    text-align: center;
}

.register-footer p {
    font-size: 13px;
    color: #8b95a1;
}

.register-footer a {
    color: #0050ad;
    text-decoration: none;
    font-weight: 600;
}

.register-footer a:hover {
    text-decoration: underline;
}
@media (max-width: 480px) {
    .login-card {
        max-width: 92%;
        padding: 22px 18px;
    }

    .login-title h1 {
        font-size: 18px;
    }

    .input-group input {
        height: 44px;
    }

    .btn-login {
        height: 44px;
    }
}
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">

            <div class="header-logo">
              <img src="../user/gambar/logo-polije.png" alt="logo">
                <span class="brand-name-login">Perpustakaan Polije</span>
            </div>

            <div class="login-title">
                <h1>Selamat datang kembali</h1>
                <p>Silahkan masukkan email dan password untuk mengakses sistem perpustakaan.</p>
            </div>

            <form action="proses-login.php" method="POST" class="login-form">

                <div class="input-group">
                    <label for="email">Email atau ID Anggota</label>
                    <input type="text" id="email" name="email" placeholder="Masukkan email / ID" required>
                </div>

                <div class="input-group">
                    <label for="password">Kata sandi</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <div class="forgot-link">
                    <a href="#">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="btn-login">Masuk</button>

            </form>

            <div class="register-footer">
                <p>Belum memiliki akun? <a href="register.php">Daftar sebagai anggota</a></p>
            </div>

        </div>
    </div>

</body>
</html>