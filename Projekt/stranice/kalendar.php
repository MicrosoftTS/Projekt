<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['korisnik'])) {
        header("Location: index.php?page=home");
        exit;
    }
    $api_url = "https://date.nager.at/api/v3/PublicHolidays/2024/HR";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $holidays = json_decode($response, true);
        } else {
            throw new Exception("Greška s dohvatom API-a. HTTP kod: $http_code");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $holidays = [];
    }
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalendar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="content">
        <h1>Hrvatski Blagdani i Državni Praznici</h1>
        <?php if (!empty($error)): ?><p style="color: red;"><?php echo $error; ?></p><?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Naziv</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($holidays)): ?>
                    <?php foreach ($holidays as $holiday): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($holiday['date']); ?></td>
                            <td><?php echo htmlspecialchars($holiday['localName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Nema dostupnih podataka o blagdanima.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>