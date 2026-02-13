<?php
ini_set('display_errors', 'Off');
include 'includes/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['time'], $_POST['ticket_adult'], $_POST['ticket_child'])) {
        $schedule_id = (int)$_POST['time'];
        $amount_a = max(0, (int)$_POST['ticket_adult']);
        $amount_c = max(0, (int)$_POST['ticket_child']);
        $user_id = (int)$_SESSION['user_id'];

        $stmt = $conn->prepare("
            SELECT s.date, m.price AS mPri, h.price AS hPri 
            FROM schedule s
            JOIN movies m ON s.movie_id = m.id
            JOIN halls h ON s.hall_id = h.id
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $schedule_data = $res->fetch_assoc();

        if (!$schedule_data) {
            header('Location: schedule.php');
            exit;
        }

        $stmt_u = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt_u->bind_param("i", $user_id);
        $stmt_u->execute();
        $user_data = $stmt_u->get_result()->fetch_assoc();

        $base_price = $schedule_data['mPri'] + $schedule_data['hPri'];
        $total_price = round(($amount_a * $base_price) + ($amount_c * $base_price * 0.85), 2);
        
        $today = date('Y-m-d H:i:s');
        $expiration = date('Y-m-d H:i:s', strtotime($schedule_data['date']));
        $code = strtoupper(substr($user_data['username'], 0, 2)) . rand(100000, 999999);

        $stmt_i = $conn->prepare("
            INSERT INTO tickets (user_id, schedule_id, code, price, creation_date, expiration_date) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt_i->bind_param("iisdss", $user_id, $schedule_id, $code, $total_price, $today, $expiration);
        
        if ($stmt_i->execute()) {
            header('Location: user.php?success=1');
        } else {
            echo "Błąd zapisu: " . $conn->error;
        }
        exit;

    } else if (isset($_POST['movie_id'], $_POST['day'])) {
        $movie_id = (int)$_POST['movie_id'];
        $day = $_POST['day'];
        
        $stmt = $conn->prepare("
            SELECT s.id, s.date, m.title, m.price AS mPri, h.cols, h.rows, h.price AS hPri
            FROM schedule AS s
            INNER JOIN movies AS m ON s.movie_id = m.id
            INNER JOIN halls AS h ON s.hall_id = h.id
            WHERE s.movie_id = ? AND DATE(s.date) = ?
        ");
        $stmt->bind_param("is", $movie_id, $day);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }

        if (empty($schedules)) {
            header('Location: schedule.php');
            exit;
        }
        
        echo "<script>const scheduleData = " . json_encode($schedules) . ";</script>";
    }
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

        <form method="POST" action="buy.php">
            <label for="time">Wybierz godzinę:</label>
            <select require id="time" name="time">
                <?php
                foreach ($result as $row) {
                    echo "<option onchange='update()' value='" . $row['id'] . "'>" . date('H:i', strtotime($row['date'])) . "</option>";
                }
                ?>
            </select><br><br>

            <label for="ticket_adult">Bilet normalny:</label>
            <input onchange="update()" require type="number" id="ticket_adult" name="ticket_adult" min="0" max="50" value="1"><br><br>

            <label for="ticket_child">Bilet ulgowy:</label>
            <input onchange="update()" require type="number" id="ticket_child" name="ticket_child" min="0" max="50" value="0"><br><br>

            <div id="total-price">Cena: 0.00 zł</div>

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

    <script>
        function update() {
            const timeSelect = document.getElementById('time');
            const adultInput = document.getElementById('ticket_adult');
            const childInput = document.getElementById('ticket_child');
            const selectedSchedule = scheduleData.find(s => s.id == timeSelect.value);
            
            if (selectedSchedule) {
                const basePrice = selectedSchedule.mPri + selectedSchedule.hPri;
                const totalPrice = (adultInput.value * basePrice) + (childInput.value * basePrice * 0.85);
                document.getElementById('total-price').textContent = "Cena: " + totalPrice.toFixed(2) + " zł";
            }
        }

        setInterval(update, 5000);
        update();
    </script>
<?php include 'includes/footer.php'; ?>