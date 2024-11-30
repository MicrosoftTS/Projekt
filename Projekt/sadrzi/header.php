<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    $menuItems = [
        'home' => ['name' => 'Početna', 'icon' => '🏠'],
        'radno-vrijeme' => ['name' => 'Radno vrijeme', 'icon' => '🕒'],
        'kontakti' => ['name' => 'Kontakti', 'icon' => '📞'],
        'radnici' => ['name' => 'Radnici', 'icon' => '👥'],
        'kalendar' => ['name' => 'Kalendar', 'icon' => '🗓']
    ];
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
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
    <?php if (isset($_SESSION['korisnik'])): ?>
        <div class="logout-button">
            <a href="index.php?logout=true">Odjava</a>
        </div>
    <?php endif; ?>
    <div class="sidebar">
        <div class="logo">
            <h1><a href="index.php">Firma</a></h1>
        </div>
        <ul class="menu">
            <?php if (isset($_SESSION['korisnik'])): ?>
                <?php foreach ($menuItems as $key => $item): ?>
                    <li class="<?php echo $key === $currentPage ? 'active' : ''; ?>">
                        <a href="index.php?page=<?php echo $key; ?>">
                            <span class="icon"><?php echo $item['icon']; ?></span>
                            <?php echo $item['name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="<?php echo $currentPage === 'home' ? 'active' : ''; ?>">
                    <a href="index.php?page=home">
                        <span class="icon">🏠</span> Početna
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="content">