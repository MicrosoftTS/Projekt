<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obrada prijave
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Povezivanje s bazom podataka
    $pdo = new PDO("mysql:host=localhost;dbname=firma_app;charset=utf8", "root", "root");

    // Provjera korisničkih podataka
    $stmt = $pdo->prepare("SELECT * FROM korisnik WHERE username = :username AND password = SHA2(:password, 256)");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $korisnik = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($korisnik) {
        // Postavljanje sesije za korisnika
        $_SESSION['korisnik'] = [
            'id' => $korisnik['id'],
            'username' => $korisnik['username'],
            'is_admin' => $korisnik['is_admin']
        ];
        header("Location: index.php?page=radno-vrijeme");
        exit;
    } else {
        $error = "Neispravno korisničko ime ili lozinka.";
    }
}
?>

<div class="content">
    <h1>Dobrodošli u internu aplikaciju</h1>

    <?php if (!isset($_SESSION['korisnik'])): ?>
        <!-- Forma za prijavu -->
        <form method="POST" class="login-form">
            <h3>Prijava</h3>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <label for="username">Korisničko ime</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Lozinka</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" name="login">Prijava</button>
        </form>
    <?php else: ?>
        <!-- Pozdrav za prijavljene korisnike -->
        <p>Pozdrav, <strong><?php echo htmlspecialchars($_SESSION['korisnik']['username']); ?></strong>!</p>
        <p>Uloga: <?php echo $_SESSION['korisnik']['is_admin'] ? 'Administrator' : 'Korisnik'; ?></p>
    <?php endif; ?>
</div>