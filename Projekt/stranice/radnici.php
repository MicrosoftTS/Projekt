<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Provjera je li korisnik prijavljen
if (!isset($_SESSION['korisnik'])) {
    header("Location: index.php?page=home");
    exit;
}

// Provjera je li korisnik administrator
$is_admin = $_SESSION['korisnik']['is_admin'];

// Povezivanje s bazom podataka
try {
    $pdo = new PDO("mysql:host=localhost;dbname=firma_app;charset=utf8", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Greška pri povezivanju s bazom podataka: " . $e->getMessage());
}

// Obrada unosa novog radnika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $ime = $_POST['ime'];
    $prezime = $_POST['prezime'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    try {
        // Počinjemo transakciju
        $pdo->beginTransaction();

        // Umetanje podataka u tablicu `radnici`
        $stmt = $pdo->prepare("INSERT INTO radnici (ime, prezime, email)
                               VALUES (:ime, :prezime, :email)");
        $stmt->execute([
            ':ime' => $ime,
            ':prezime' => $prezime,
            ':email' => $email
        ]);
        $radnik_id = $pdo->lastInsertId();

        // Umetanje podataka u tablicu `korisnici`
        $stmt = $pdo->prepare("INSERT INTO korisnik (username, password, is_admin, radnik_id)
                               VALUES (:username, SHA2(:password, 256), :is_admin, :radnik_id)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':is_admin' => $is_admin,
            ':radnik_id' => $radnik_id
        ]);

        // Zatvaranje transakcije
        $pdo->commit();
        $success = "Radnik uspješno dodan!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Došlo je do greške: " . $e->getMessage();
    }
}

// Dohvat svih radnika za prikaz u tablici
$stmt = $pdo->query("SELECT ime, prezime, email FROM radnici");
$radnici = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radnici</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="content">
        <h1>Radnici</h1>

        <?php if ($is_admin): ?>
            <button onclick="document.getElementById('addForm').style.display='block'">Dodaj novog radnika</button>
            <div id="addForm" style="display: none; margin-top: 20px;">
                <form method="POST">
                    <label>Ime:</label>
                    <input type="text" name="ime" required>
                    
                    <label>Prezime:</label>
                    <input type="text" name="prezime" required>
                    
                    <label>Email:</label>
                    <input type="email" name="email" required>
                    
                    <label>Username:</label>
                    <input type="text" name="username" required>
                    
                    <label>Lozinka:</label>
                    <input type="password" name="password" required>
                    
                    <label>Administrator:</label>
                    <input type="checkbox" name="is_admin">
                    
                    <button type="submit">Spremi</button>
                </form>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radnici as $radnik): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($radnik['ime']); ?></td>
                        <td><?php echo htmlspecialchars($radnik['prezime']); ?></td>
                        <td><?php echo htmlspecialchars($radnik['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
