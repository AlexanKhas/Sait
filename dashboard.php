<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__.'/../config/config.php';
require "../includes/header.php";
require "../config/db.php";

if(isset($_SESSION['user_id'])){
    app_log("User {$_SESSION['user_id']} opened dashboard");
}
?>

<!-- ================= HERO ================= -->
<section class="hero-block hero-main fade-up">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <!-- НАДПИСЬ -->
        <span class="hero-badge">
            Корпоративная платформа МТО
        </span>

        <h1>Управление<br>материаллами и ресурсами</h1>
        <p>Современная информационная система<br>для контроля запасов, заявок и поставок</p>

        <div class="hero-actions">
    <!-- Убираем условие входа -->
    <a href="#about" class="btn-main" id="scrollAbout">
        Подробнее о платформе
    </a>
</div>

    </div>
</section>

<!-- JS: плавный медленный скролл к секции #about -->
<script>
document.getElementById('scrollAbout').addEventListener('click', function(e){
    e.preventDefault();
    const target = document.querySelector('#about');
    if(target){
        const targetPosition = target.getBoundingClientRect().top + window.scrollY - 80;
        const startPosition = window.scrollY;
        const distance = targetPosition - startPosition;
        const duration = 1400;

        let start = null;
        function step(timestamp){
            if(!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const ease = 0.5 - 0.5 * Math.cos(Math.PI * progress);
            window.scrollTo(0, startPosition + distance * ease);
            if(progress < 1){
                window.requestAnimationFrame(step);
            } else {
                window.scrollTo(0, targetPosition);
            }
        }
        window.requestAnimationFrame(step);
    }
});
</script>
<!-- ===== ПОКАЗАТЬ HERO ===== -->
<script>
window.addEventListener('DOMContentLoaded', () => {
    const hero = document.querySelector('.hero-block');
    if(hero){
        hero.classList.add('show');
    }
});
</script>

<!-- ================= ABOUT ================= -->
<section class="section about-block fade-up" id="about">
    <div class="about-grid">
        <div class="about-text">
            <h2>О платформе</h2>
            <p>Система МТО предназначена для автоматизации учета материальных ресурсов предприятия.</p>
            <p>Вы получаете прозрачный контроль над поступлениями, расходами и заявками подразделений.</p>
            <ul class="about-list">
                <li>✔ Централизованный учет</li>
                <li>✔ Контроль остатков</li>
                <li>✔ История операций</li>
                <li>✔ Аналитика</li>
            </ul>
        </div>
        <div class="about-image">
            <img src="https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=1600" alt="Система">
        </div>
    </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="section features-section fade-up">
    <h2 class="section-title">Возможности системы</h2>
    <div class="features-grid">

        <div class="feature-card">
            <div class="feature-bg" style="background-image:url('https://images.pexels.com/photos/267614/pexels-photo-267614.jpeg?auto=compress&cs=tinysrgb&w=1600')"></div>
            <div class="feature-overlay"></div>
            <div class="feature-content">
                <i class="fa-solid fa-box"></i>
                <h3>Материалы</h3>
                <p>Полный каталог материалов с актуальными остатками</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-bg" style="background-image:url('https://images.pexels.com/photos/3183197/pexels-photo-3183197.jpeg?auto=compress&cs=tinysrgb&w=1600')"></div>
            <div class="feature-overlay"></div>
            <div class="feature-content">
                <i class="fa-solid fa-arrow-down"></i>
                <h3>Поступления</h3>
                <p>Регистрация и контроль всех поставок</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-bg" style="background-image:url('https://images.pexels.com/photos/3182834/pexels-photo-3182834.jpeg?auto=compress&cs=tinysrgb&w=1600')"></div>
            <div class="feature-overlay"></div>
            <div class="feature-content">
                <i class="fa-solid fa-arrow-up"></i>
                <h3>Расход</h3>
                <p>Учет списаний по подразделениям</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-bg" style="background-image:url('https://images.pexels.com/photos/3184297/pexels-photo-3184297.jpeg?auto=compress&cs=tinysrgb&w=1600')"></div>
            <div class="feature-overlay"></div>
            <div class="feature-content">
                <i class="fa-solid fa-clipboard"></i>
                <h3>Заявки</h3>
                <p>Управление заявками в реальном времени</p>
            </div>
        </div>

    </div>
</section>

<!-- ================= ANALYTICS ================= -->
<section class="section analytics-section fade-up">
    <div class="analytics-box">

        <div class="analytics-text">
            <h2>Аналитика заявок</h2>
            <p>Динамика всех заявок по датам</p>
            <div class="analytics-total">
                Всего заявок: <span id="totalRequests">0</span>
            </div>
        </div>

        <div class="analytics-chart">
            <canvas id="requestsChart"></canvas>
        </div>

    </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta-section fade-up">
    <div class="cta-overlay"></div>
    <div class="cta-content">
        <h2>Готовы начать работу?</h2>
        <p>Подключайтесь к системе и оптимизируйте управление ресурсами</p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="requests.php" class="btn-main">Открыть систему</a>
        <?php else: ?>
            <a href="../auth/login.php" class="btn-main">Начать сейчас</a>
        <?php endif; ?>
    </div>
</section>

<?php require "../includes/footer.php"; ?>

<!-- ================= CHART ================= -->

<style>
.analytics-box {
    max-width: 900px;
    margin: 0 auto;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}

.analytics-text {
    text-align: center;
    margin-bottom: 30px;
}

.analytics-total {
    font-size: 22px;
    font-weight: 600;
    margin-top: 10px;
}

.analytics-chart {
    height: 400px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart = null;

async function loadStats(){
    const r = await fetch("../api/stats_requests.php");
    const d = await r.json();

    const labels = d.map(i=>i.d);
    const values = d.map(i=>i.c);

    const total = values.reduce((a,b)=>a+b,0);
    document.getElementById('totalRequests').innerText = total;

    if(!chart){
        chart = new Chart(document.getElementById('requestsChart'),{
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Количество заявок',
                    data: values,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display:false }
                },
                scales: {
                    y: { 
                        beginAtZero:true 
                    }
                }
            }
        });
    } else {
        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.update();
    }
}

loadStats();
setInterval(loadStats,15000);
</script>