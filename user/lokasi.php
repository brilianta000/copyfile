<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="/workshop-perpus/user/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <title>Lokasi Perpustakaan</title>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="hero-section-lokasi">
        <div class="hero-content-lokasi">
            <div class="hero-icon-lokasi">
                <img src="gambar/lakasi.png" alt="Icon Profil">
            </div>
            <h1 class="hero-title-lokasi">Lokasi & Jam Kerja</h1>
            <p class="hero-subtitle-lokasi">Perpustakaan POLIJE</p>
        </div>
    </div>

    <section class="main-location-content">
        <div class="map-wrapper">
            <div class="map-frame">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.423521235!2d113.720613!3d-8.1601!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMKwMDknMzYuNCJTIDExM8KwNDMnMTQuMiJF!5e0!3m2!1sid!2sid!4v1620000000000!5m2!1sid!2sid" 
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
            <div class="map-label">
                <img src="user/gambar/lakasi.png" alt="Pin">
                <h2>Google Map</h2>
            </div>
        </div>

        <hr class="section-divider">

        <div class="operational-card">
            <div class="card-top">
                <div class="clock-icon-bg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="#0050AD"/>
                        <path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3>Jam Operasional</h3>
                <span class="tag-label">Umum</span>
            </div>

            <div class="schedule-grid">
                <div class="day-col holiday">
                    <span class="label-day">Minggu</span>
                    <span class="val-time">Tutup</span>
                </div>
                <div class="day-col">
                    <span class="label-day">Senin</span>
                    <span class="val-time">08.00 - 16.00</span>
                </div>
                <div class="day-col">
                    <span class="label-day">Selasa</span>
                    <span class="val-time">08.00 - 16.00</span>
                </div>
                <div class="day-col">
                    <span class="label-day">Rabu</span>
                    <span class="val-time">08.00 - 16.00</span>
                </div>
                <div class="day-col">
                    <span class="label-day">Kamis</span>
                    <span class="val-time">08.00 - 16.00</span>
                </div>
                <div class="day-col">
                    <span class="label-day">Jumat</span>
                    <span class="val-time">08.00 - 16.30</span>
                </div>
                <div class="day-col active holiday">
                    <span class="label-day">Sabtu</span>
                    <span class="val-time">Tutup</span>
                </div>
            </div>
        </div>
    </section>

    <?php include 'foot.php'; ?>
</body>
</html>