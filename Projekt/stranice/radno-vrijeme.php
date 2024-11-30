<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funkcija za dohvaćanje trenutnog datuma i vremena s API-ja ili lokalno
function getCurrentDateTime() {
    $api_url = "http://worldtimeapi.org/api/timezone/Europe/Zagreb";
    $response = @file_get_contents($api_url);

    if ($response === false) {
        return date("d.m.Y H:i");
    }

    $data = json_decode($response, true);

    if (isset($data['datetime'])) {
        $dateTime = new DateTime($data['datetime']);
        return $dateTime->format('d.m.Y H:i'); // Format DD.MM.YYYY HH:MM
    } else {
        return date("d.m.Y H:i");
    }
}

// Priprema sesije za evidenciju radnog vremena
$radnoVrijeme = $_SESSION['radno_vrijeme'] ?? [];
$imaAktivnuPrijavu = !empty($radnoVrijeme) && end($radnoVrijeme)['odjava'] === null;

// Obrada podataka iz forme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['prijava']) && !$imaAktivnuPrijavu) {
        $_SESSION['radno_vrijeme'][] = [
            'prijava' => getCurrentDateTime(),
            'odjava' => null,
            'lokacija' => $_POST['lokacija'],
            'napomena' => $_POST['napomena'],
            'rad_od_kuce' => isset($_POST['rad_od_kuce']) ? 'Da' : 'Ne',
        ];
        // Preusmjeravanje s očuvanjem trenutne stranice
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=radno-vrijeme");
        exit;
    }

    if (isset($_POST['odjava'])) {
        $last_entry_key = array_key_last($_SESSION['radno_vrijeme']);
        if ($last_entry_key !== null && $_SESSION['radno_vrijeme'][$last_entry_key]['odjava'] === null) {
            $_SESSION['radno_vrijeme'][$last_entry_key]['odjava'] = getCurrentDateTime();
        }
        // Preusmjeravanje s očuvanjem trenutne stranice
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=radno-vrijeme");
        exit;
    }
}

$radnoVrijeme = $_SESSION['radno_vrijeme'] ?? [];
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evidencija radnog vremena</title>
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const imaAktivnuPrijavu = <?php echo json_encode($imaAktivnuPrijavu); ?>;
            const prijavaButton = document.querySelector("button[name='prijava']");

            if (imaAktivnuPrijavu) {
                prijavaButton.disabled = true;
            }
        });
    </script>
</head>
<body>
    <div class="content">
        <h2>Evidencija radnog vremena</h2>

        <!-- Forma za unos radnog vremena -->
        <form method="POST" class="radno-vrijeme-form">
            <h3>Prijava radnog vremena</h3>
            <label for="lokacija">Lokacija *</label>
            <input type="text" name="lokacija" id="lokacija" value="Firma d.o.o." required>

            <div class="checkbox-group">
                <input type="checkbox" name="rad_od_kuce" id="rad_od_kuce">
                <label for="rad_od_kuce">Rad od kuće</label>
            </div>

            <label for="napomena">Napomena</label>
            <input type="text" name="napomena" id="napomena" placeholder="Unesite napomenu">

            <button type="submit" name="prijava">Prijava</button>
        </form>

        <!-- Gumb za odjavu (vidljiv samo ako postoji prijava bez odjave) -->
        <?php if ($imaAktivnuPrijavu): ?>
            <form method="POST">
                <button type="submit" name="odjava">Odjava</button>
            </form>
        <?php endif; ?>

        <!-- Tablica za prikaz evidencije -->
        <h3>Evidencija</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Prijava</th>
                    <th>Odjava</th>
                    <th>Lokacija</th>
                    <th>Rad od kuće</th>
                    <th>Napomena</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radnoVrijeme as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['prijava']); ?></td>
                        <td><?php echo htmlspecialchars($entry['odjava'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($entry['lokacija']); ?></td>
                        <td><?php echo htmlspecialchars($entry['rad_od_kuce']); ?></td>
                        <td><?php echo htmlspecialchars($entry['napomena']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
