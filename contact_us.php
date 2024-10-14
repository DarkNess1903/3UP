<?php
session_start();
include 'connectDB.php';
include 'topnavbar.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>ติดต่อเรา</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- JavaScript Links -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>ติดต่อเรา</h1>
    </header>

    <main class="container mt-4">
        <!-- Contact Information -->
        <section class="contact-info">
            <h3>ข้อมูลการติดต่อ</h3>
            <p><strong>ที่อยู่:</strong> 50/1 ถนนราเมศวร ตำบลหอรัตนไชย อำเภอพระนครศรีอยุธยา จังหวัดพระนครศรีอยุธยา 13000</p>
            <p><strong>เบอร์:</strong> 062 386 8314</p>
            <p><strong>อีเมล:</strong> <a href="mailto:prapaapornpj@icloud.com">prapaapornpj@icloud.com</a></p>
            <p>
                <strong>ติดต่อเรา:</strong>
                <a href="https://www.facebook.com/3upbistro" target="_blank" class="text-decoration-none me-2" title="Facebook">
                    <i class="fab fa-facebook fa-lg"></i>
                </a>
                <a href="https://lin.ee/x6CVj6e" target="_blank" class="text-decoration-none" title="LINE">
                    <i class="fab fa-line fa-lg" style="color: green;"></i> <!-- Set LINE icon color to green -->
                </a>
            </p>
            <p><strong>เวลาทำการ:</strong> จันทร์ - ศุกร์: 09.00 - 18.00 น.</p>
        </section>

        <!-- Map -->
        <section class="map mt-4">
            <h3>ที่อยู่ร้าน</h3>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123687.20648578063!2d100.42593049726561!3d14.356375000000016!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e275dc585717db%3A0xa0c2b30ca6a24821!2z4LmA4LiZ4Li34LmJ4Lit4Lir4Lit4Lih4Lih4Liy4Lil4Lit4LiH4LmA4LiL4LmIIOC4quC5gOC4leC5iuC4gSbguYHguIjguYjguKfguK7guYnguK3guJk!5e0!3m2!1sth!2sth!4v1726213849438!5m2!1sth!2sth"
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </section>
    </main>
</body>
</html>

<?php
mysqli_close($conn);
include 'footer.php';
?>
