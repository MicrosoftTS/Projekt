<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add']) && $is_admin) {
        $naziv = $_POST['naziv'];
        $kontakt_broj = $_POST['kontakt_broj'];
        $email = $_POST['email'];
        try {
            $stmt = $pdo->prepare("INSERT INTO partneri (naziv, kontakt_broj, email) VALUES (:naziv, :kontakt_broj, :email)");
            $stmt->execute([':naziv' => $naziv, ':kontakt_broj' => $kontakt_broj, ':email' => $email]);
            $success = "Partner uspješno dodan!";
        } catch (PDOException $e) {
            $error = "Greška prilikom dodavanja: " . $e->getMessage();
        }
    }
    $edit_partner = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit']) && $is_admin) {
        $partner_id = $_POST['partner_id'];
        $stmt = $pdo->prepare("SELECT * FROM partneri WHERE id = :id");
        $stmt->execute([':id' => $partner_id]);
        $edit_partner = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update']) && $is_admin) {
        $partner_id = $_POST['partner_id'];
        $naziv = $_POST['naziv'];
        $kontakt_broj = $_POST['kontakt_broj'];
        $email = $_POST['email'];
        try {
            $stmt = $pdo->prepare("UPDATE partneri SET naziv = :naziv, kontakt_broj = :kontakt_broj, email = :email WHERE id = :id");
            $stmt->execute([':naziv' => $naziv, ':kontakt_broj' => $kontakt_broj, ':email' => $email, ':id' => $partner_id]);
            $success = "Podaci o partneru uspješno ažurirani!";
            $edit_partner = null;
        } catch (PDOException $e) {
            $error = "Greška prilikom ažuriranja: " . $e->getMessage();
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $is_admin) {
        $partner_id = $_POST['partner_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM partneri WHERE id = :id");
            $stmt->execute([':id' => $partner_id]);
            $success = "Partner uspješno obrisan!";
        } catch (PDOException $e) {
            $error = "Greška prilikom brisanja: " . $e->getMessage();
        }
    }
    $stmt = $pdo->query("SELECT * FROM partneri");
    $partneri = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Partneri</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="content">
            <h1>Partneri</h1>
            <?php if (!empty($success)): ?><p style="color: green;"><?php echo $success; ?></p><?php endif; ?>
            <?php if (!empty($error)): ?><p style="color: red;"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($is_admin): ?>
                <form method="POST">
                    <input type="hidden" name="partner_id" value="<?php echo $edit_partner['id'] ?? ''; ?>">
                    <label>Naziv partnera:</label>
                    <input type="text" name="naziv" value="<?php echo htmlspecialchars($edit_partner['naziv'] ?? ''); ?>" required>
                    <label>Kontakt broj:</label>
                    <input type="text" name="kontakt_broj" value="<?php echo htmlspecialchars($edit_partner['kontakt_broj'] ?? ''); ?>">
                    <label>Email adresa:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_partner['email'] ?? ''); ?>" required>
                    <br><br>
                    <button type="submit" name="<?php echo $edit_partner ? 'update' : 'add'; ?>">
                        <?php echo $edit_partner ? 'Spremi' : 'Dodaj'; ?>
                    </button>
                </form>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Naziv</th>
                        <th>Kontakt broj</th>
                        <th>Email</th>
                        <?php if ($is_admin): ?><th>Akcije</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partneri as $partner): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($partner['naziv']); ?></td>
                            <td><?php echo htmlspecialchars($partner['kontakt_broj']); ?></td>
                            <td><?php echo htmlspecialchars($partner['email']); ?></td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="partner_id" value="<?php echo $partner['id']; ?>">
                                        <button type="submit" name="edit">Uredi</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="partner_id" value="<?php echo $partner['id']; ?>">
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