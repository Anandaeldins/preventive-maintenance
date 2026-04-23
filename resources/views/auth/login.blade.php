<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ===== THEME VARIABLES ===== */
:root {
    --bg: #f8fafc;
    --card: #ffffff;
    --text: #0f172a;
    --input: #ffffff;
    --border: #c7d2fe;
    --primary: #2563eb;
    --gradient: linear-gradient(135deg, #60a5fa, #2563eb);
}

/* DARK MODE */
body.dark {
    --bg: #020617;
    --card: #020617;
    --text: #e5e7eb;
    --input: #020617;
    --border: #334155;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Inter, Poppins, system-ui, sans-serif;
    background: var(--bg);
    color: var(--text);
}

/* ===== WRAPPER ===== */
.login-wrapper {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
}

/* ===== LEFT IMAGE ===== */
.login-image {
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ===== CAROUSEL ===== */
.carousel {
    position: relative;
    width: 90%;
    max-width: 520px;
    height: 520px;
    border-radius: 24px;
    overflow: hidden;
    background: #020617;
    box-shadow: 0 40px 80px rgba(0,0,0,0.6);
}

.slides {
    position: relative;
    width: 100%;
    height: 100%;
}

.slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.8s ease;
}

.slide.active {
    opacity: 1;
}

.carousel::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0,0,0,0.65),
        rgba(0,0,0,0.15)
    );
    z-index: 1;
}

/* ===== TEXT ON IMAGE ===== */
.carousel-text {
    position: absolute;
    bottom: 80px;
    left: 32px;
    right: 32px;
    color: white;
    z-index: 2;
}

.carousel-text h3 {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 8px;
}

.carousel-text p {
    font-size: 15px;
    opacity: 0.85;
}

/* ===== DOTS ===== */
.carousel-dots {
    position: absolute;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 2;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.4);
    cursor: pointer;
    transition: 0.3s;
}

.dot.active {
    width: 28px;
    background: white;
}

/* ===== RIGHT FORM ===== */
.login-form {
    position: relative;
    background: var(--card);
    padding: 48px 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* TOGGLE */
.theme-toggle {
    position: absolute;
    top: 24px;
    right: 32px;
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
}

/* LOGO */
.login-logo {
    width: 120px;
    margin: 0 auto 32px;
    display: block;
}

/* TITLE */
.login-form h2 {
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 36px;
    letter-spacing: 0.3px;
}

/* FORM */
.form-group {
    margin-bottom: 20px;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

input {
    width: 100%;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid var(--border);
    font-size: 15px;
    background: var(--input);
    color: var(--text);
}

input:focus {
    outline: none;
    border-color: var(--primary);
}

/* BUTTON */
.btn-login {
    margin-top: 16px;
    background: var(--gradient);
    color: white;
    border: none;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 999px;
    cursor: pointer;
    transition: 0.3s;
}

.btn-login:hover {
    transform: translateY(-2px);
}

/* LINK */
.back-link {
    margin-top: 24px;
    text-align: center;
}

.back-link a {
    text-decoration: none;
    color: var(--primary);
    font-weight: 600;
}

.alert-error {
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    font-size: 14px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .login-wrapper {
        grid-template-columns: 1fr;
    }
    .login-image {
        display: none;
    }
    .login-form {
        padding: 40px 28px;
    }
}
</style>
</head>

<body>

<div class="login-wrapper">

    <!-- LEFT -->
    <div class="login-image">
        <div class="carousel">

            <div class="slides">
                <img src="{{ asset('assets/image/dashboard.png') }}" class="slide active">
                <img src="{{ asset('assets/image/dashboard2.png') }}" class="slide">
                <img src="{{ asset('assets/image/dashboard3.png') }}" class="slide">
            </div>

            <div class="carousel-text">
                <h3>Preventive Maintenance System</h3>
                <p>Monitor aset, analisis risiko, dan jaga keandalan jaringan.</p>
            </div>

            <div class="carousel-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>

        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-form">
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>

        <img src="{{ asset('assets/image/pgncomlogo.png') }}" class="login-logo" alt="Logo">

        <h2>Sign In</h2>

        <form method="POST" action="/login">
            @csrf

            @if (session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-group">
                <label>Username / Email</label>
                <input type="text" name="username" value="{{ old('username') }}" required>
                @error('username')
                    <div class="alert-error" style="margin-top:8px; margin-bottom:0;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                @error('password')
                    <div class="alert-error" style="margin-top:8px; margin-bottom:0;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="back-link">
            <a href="/">← Back to Home</a>
        </div>
    </div>

</div>

<script>
/* ===== CAROUSEL ===== */
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
let index = 0;

function showSlide(i) {
    slides.forEach(s => s.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));
    slides[i].classList.add('active');
    dots[i].classList.add('active');
    index = i;
}

dots.forEach((dot, i) => {
    dot.addEventListener('click', () => showSlide(i));
});

setInterval(() => {
    index = (index + 1) % slides.length;
    showSlide(index);
}, 5000);

/* ===== DARK MODE ===== */
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') document.body.classList.add('dark');

function toggleTheme() {
    document.body.classList.toggle('dark');
    localStorage.setItem(
        'theme',
        document.body.classList.contains('dark') ? 'dark' : 'light'
    );
}
</script>

</body>
</html>
