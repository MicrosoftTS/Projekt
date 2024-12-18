<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: index.php?page=home");
        exit;
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 'home';

    ob_start();
    switch ($page) {
        case 'radnici':
            include 'stranice/radnici.php';
            break;
        case 'kontakti':
            include 'stranice/kontakti.php';
            break;
        case 'kalendar':
            include 'stranice/kalendar.php';
            break;
        case 'radno-vrijeme':
            include 'stranice/radno-vrijeme.php';
            break;
        default:
            include 'stranice/home.php';
            break;
    }
    $content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Firma - Interna aplikacija</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php include 'sadrzi/header.php'; ?>
        <div class="content">
            <?php echo $content; ?>
        </div>
        <?php include 'sadrzi/footer.php'; ?>
    </body>
</html>