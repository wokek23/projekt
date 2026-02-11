<?php
ini_set('display_errors', 'Off');
include 'includes/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['movie_id'], $_POST['day'])) {
        $movie_id = $_POST['movie_id'];
        $day = $_POST['day'];
        
        $query = "SELECT s.id, s.date, m.title, m.price AS mPri, h.cols, h.rows, h.price AS hPri
                FROM schedule AS s
                INNER JOIN movies AS m ON s.movie_id = m.id
                INNER JOIN halls AS h ON s.hall_id = h.id
                WHERE s.movie_id = $movie_id AND DATE(s.date) = '$day'";
        
        $result = $conn->query($query);

        echo "<script>
                const schedule = [";
                foreach ($result as $s) {
                    echo "['" . date('H:i', strtotime($s['date'])) . "', " . $s['mPri'] + $s['hPri'] . "],";
                }
        echo "]</script>";
    } else {
        header('Location: schedule.php');
        exit;
    }
} else {
    header('Location: schedule.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kup Bilet</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/buy.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="main">
        <h1>Zakup biletów</h1>
        <p><?php echo "Film <b>" . htmlspecialchars($result->fetch_assoc()['title']) . "</b> w dniu <b>" . $day . "</b>"; ?></p>

        <form>
            <label for="time">Wybierz godzinę:</label>
            <select require id="time" name="time">
                <?php
                foreach ($result as $row) {
                    echo "<option onchanged='update()' value='" . $row['id'] . "'>" . date('H:i', strtotime($row['date'])) . "</option>";
                }
                ?>
            </select><br><br>

            <label for="ticket_adult">Bilet normalny:</label>
            <input onchanged="update()" require type="number" id="ticket_adult" name="ticket_adult" min="0" value="1"><br><br>

            <label for="ticket_child">Bilet ulgowy:</label>
            <input onchanged="update()" require type="number" id="ticket_child" name="ticket_child" min="0" value="0"><br><br>

            <button type="submit" class="buy-btn">Kup bilet</button>
        </form>
        
        <?php
            foreach ($result as $row) {
                echo "<div class='movie_info'>";
                echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
                echo "<p>Cena: " . htmlspecialchars($row['mPri'] + $row['hPri']) . " zł</p>";
                echo "<p>Sala: " . htmlspecialchars($row['cols']) . "x" . htmlspecialchars($row['rows']) . "</p>";
                echo "</div>";
            }
        ?>
    </div>

<?php include 'includes/footer.php'; ?>