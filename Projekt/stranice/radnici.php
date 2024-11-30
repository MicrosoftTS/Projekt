<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['korisnik'])) {
        header("Location: index.php?page=home");
        exit;
    }
    $is_admin = $_SESSION['korisnik']['is_admin'];
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=firma_app;charset=utf8", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Greška pri povezivanju s bazom: " . $e->getMessage());
    }
    $edit_radnik = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['edit']) && $is_admin) {
            $radnik_id = $_POST['radnik_id'];
            $stmt = $pdo->prepare("SELECT radnici.id AS radnik_id, ime, prezime, email, korisnici.username, korisnici.is_admin 
                                FROM radnici 
                                LEFT JOIN korisnici ON radnici.id = korisnici.radnik_id 
                                WHERE radnici.id = :id");
            $stmt->execute([':id' => $radnik_id]);
            $edit_radnik = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (isset($_POST['update']) && $is_admin) {
            $radnik_id = $_POST['radnik_id'];
            $ime = $_POST['ime'];
            $prezime = $_POST['prezime'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE radnici SET ime = :ime, prezime = :prezime, email = :email WHERE id = :id");
                $stmt->execute([':ime' => $ime, ':prezime' => $prezime, ':email' => $email, ':id' => $radnik_id]);
                $stmt = $pdo->prepare("UPDATE korisnici SET username = :username, password = SHA2(:password, 256), is_admin = :is_admin WHERE radnik_id = :radnik_id");
                $stmt->execute([':username' => $username, ':password' => $password, ':is_admin' => $is_admin, ':radnik_id' => $radnik_id]);
                $pdo->commit();
                $success = "Radnik uspješno ažuriran!";
                $edit_radnik = null;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Greška prilikom ažuriranja: " . $e->getMessage();
            }
        } elseif (isset($_POST['add']) && $is_admin) {
            $ime = $_POST['ime'];
            $prezime = $_POST['prezime'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO radnici (ime, prezime, email) VALUES (:ime, :prezime, :email)");
                $stmt->execute([':ime' => $ime, ':prezime' => $prezime, ':email' => $email]);
                $radnik_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO korisnici (username, password, is_admin, radnik_id) 
                                    VALUES (:username, SHA2(:password, 256), :is_admin, :radnik_id)");
                $stmt->execute([':username' => $username, ':password' => $password, ':is_admin' => $is_admin, ':radnik_id' => $radnik_id]);
                $pdo->commit();
                $success = "Radnik uspješno dodan!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Greška prilikom dodavanja: " . $e->getMessage();
            }
        } elseif (isset($_POST['delete']) && $is_admin) {
            $radnik_id = $_POST['radnik_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM radnici WHERE id = :id");
                $stmt->execute([':id' => $radnik_id]);
                $success = "Radnik uspješno obrisan!";
            } catch (PDOException $e) {
                $error = "Greška prilikom brisanja: " . $e->getMessage();
            }
        }
    }
    $stmt = $pdo->query("SELECT radnici.id AS radnik_id, ime, prezime, email, korisnici.username, korisnici.is_admin 
                        FROM radnici 
                        LEFT JOIN korisnici ON radnici.id = korisnici.radnik_id");
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
            <?php if (!empty($success)): ?><p style="color: green;"><?php echo $success; ?></p><?php endif; ?>
            <?php if (!empty($error)): ?><p style="color: red;"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($is_admin): ?>
                <form method="POST">
                    <input type="hidden" name="radnik_id" value="<?php echo $edit_radnik['radnik_id'] ?? ''; ?>">
                    <label>Ime:</label>
                    <input type="text" name="ime" value="<?php echo htmlspecialchars($edit_radnik['ime'] ?? ''); ?>" required>
                    <label>Prezime:</label>
                    <input type="text" name="prezime" value="<?php echo htmlspecialchars($edit_radnik['prezime'] ?? ''); ?>" required>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_radnik['email'] ?? ''); ?>" required>
                    <label>Username:</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($edit_radnik['username'] ?? ''); ?>" required>
                    <label>Lozinka:</label>
                    <input type="password" name="password" placeholder="Unesite lozinku">
                    <label>Administrator:</label>
                    <input type="checkbox" name="is_admin" <?php echo isset($edit_radnik['is_admin']) && $edit_radnik['is_admin'] ? 'checked' : ''; ?>>
                    <br><br>
                    <button type="submit" name="<?php echo $edit_radnik ? 'update' : 'add'; ?>"><?php echo $edit_radnik ? 'Spremi' : 'Dodaj'; ?></button>
                </form>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Ime</th>
                        <th>Prezime</th>
                        <th>Email</th>
                        <?php if ($is_admin): ?><th>Akcije</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($radnici as $radnik): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($radnik['ime']); ?></td>
                            <td><?php echo htmlspecialchars($radnik['prezime']); ?></td>
                            <td><?php echo htmlspecialchars($radnik['email']); ?></td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="radnik_id" value="<?php echo $radnik['radnik_id']; ?>">
                                        <button type="submit" name="edit">Uredi</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="radnik_id" value="<?php echo $radnik['radnik_id']; ?>">
                                        <button type="submit" name="delete">Obriši</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
</html>