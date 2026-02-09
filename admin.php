<?php 
include 'includes/checkSession.php';
include 'includes/connect.php';

if($_SESSION['is_admin'] != 1){
    header("Location: index.php");
    exit;
}

$returnMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    # SEANSE
    if (isset($_POST["add-schedule"], $_POST["movie_id"], $_POST["hall_id"], $_POST["datetime"], $_POST["is_private"])) {
        $movie_id = (int) $_POST["movie_id"];
        $hall_id = (int) $_POST["hall_id"];
        $date = $_POST["datetime"];
        $is_private = ($_POST["is_private"] == '1') ? 1 : 0;
        
        $date_time = strtotime($date);
        if (!$date_time) {
            $returnMsg = "Nieprawidłowa data!";
            return;
        }
        
        if ($date_time < time()) {
            $returnMsg = "Data nie może być w przeszłości!";
            return;
        }
        
        $formatted_date = date('Y-m-d H:i:s', $date_time);
        
        $stmt = $conn->prepare("INSERT INTO schedule (movie_id, hall_id, date, is_private) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $movie_id, $hall_id, $formatted_date, $is_private);
        
        if ($stmt->execute()) {
            $returnMsg = "Sukces! ID nowego seansu: " . $conn->insert_id;
        } else {
            $returnMsg = "Błąd: " . $conn->error;
        }
        
        $stmt->close();
    }
    if (isset($_POST["delete-schedule"])) {
        $scheduleId = (int) $_POST["delete-schedule"];

        $checkTickets = $conn->query("SELECT id FROM tickets WHERE schedule_id = $scheduleId;");
        if ($checkTickets->num_rows > 0) {
            $returnMsg = "Nie można usunąć seansu, który ma wykupione bilety!";
        } else {
            $conn->query("DELETE FROM schedule WHERE id = $scheduleId");
        }
    }
    if (isset($_POST["clean-schedule"])) { # usuwa wszystkie seanse (i powiązane z nimi bilety)
        $conn->query("DELETE FROM tickets WHERE schedule_id IN (SELECT id FROM schedule WHERE date < NOW() - INTERVAL 1 DAY);");
        $conn->query("DELETE FROM schedule WHERE date < NOW() - INTERVAL 1 DAY;");
    }

    # FILMY
    if (isset($_POST["toggle-visibility"])) {
        $movieId = (int) $_POST["toggle-visibility"];

        $conn->query("UPDATE movies SET visible = NOT visible WHERE id = $movieId");
    }
    if (isset($_POST["delete-movie"])) {
        $movieId = (int) $_POST["delete-movie"];

        $checkSchedule = $conn->query("SELECT id, date FROM schedule WHERE movie_id = $movieId AND date > NOW();");
        if ($checkSchedule->num_rows > 0) {
            $returnMsg = "Nie można usunąć filmu, który ma nadchodzące seanse! Najbliższy seans: " . $checkSchedule->fetch_assoc()['date'];
        } else {
            $conn->query("DELETE FROM movies WHERE id = $movieId");
        }
    }

    # BILETY
    if (isset($_POST["change-paid"], $_POST["ticket-id"])) {
        $ticketId = (int) $_POST["ticket-id"];
        $newPaid = $_POST["change-paid"] == "1" ? 1 : 0;

        $conn->query("UPDATE tickets SET paid = $newPaid WHERE id = $ticketId");
    }
    if (isset($_POST["delete-ticket"])) {
        $ticketId = (int) $_POST["delete-ticket"];

        $conn->query("DELETE FROM tickets WHERE id = $ticketId");
    }

    # UŻYTKOWNICY
    if (isset($_POST["accion"], $_POST["id"])) {
        if ($_POST["action"] == "remove") {
            $query = "DELETE FROM users WHERE `users`.`id` = " . $_POST["id"] . ";";
            $result = $conn->query($query);

            $returnMsg = "Usunięto użytkownika!";
            
        } elseif ($_POST["action"] == "rename" && isset($_POST["new"])) {
            $new = mysqli_real_escape_string($conn, $_POST["new"]);

            $query = "UPDATE `users` SET `username` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
            $result = $conn->query($query);
            
            $returnMsg = "Zmieniono nazwę użytkownika!";
            
        } elseif ($_POST["action"] == "changeMail" && isset($_POST["new"])) {
            $new = mysqli_real_escape_string($conn, $_POST["new"]);

            $query = "UPDATE `users` SET `email` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
            $result = $conn->query($query);

            $returnMsg = "Zmieniono adres email!";
            
        } elseif ($_POST["action"] == "changePass" && isset($_POST["new"])) {
            $new = password_hash(mysqli_real_escape_string($conn, $_POST["new"]), PASSWORD_DEFAULT);
            
            $query = "UPDATE `users` SET `password` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
            $result = $conn->query($query);

            $returnMsg = "Zmieniono hasło użytkownika!";
            
        } else {
            $returnMsg = "Nieznana akcja!";
        }

        if ($_POST["id"] == $_SESSION['user_id']) {
            session_unset();
            session_destroy();
            header("Location: login.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <div class="box center center-text mar-t">
        <h1>Panel Administratora</h1>
        <ul class="list-none">
            <li><a href="#user-list">Zarządzanie użytkownikami</a></li>
            <li><a href="#tickets">Zarządzanie biletami</a></li>
            <li><a href="#schedule">Zarządzanie seansami</a></li>
            <li><a href="#movies">Zarządzanie filmami</a></li>
        </ul>
        <?php if (isset($returnMsg)) { echo "<p style='color: red;'><b>" . $returnMsg . "</b></p>"; } ?>
    </div>

    <div class="mar-t">
        <?php
            $query = "SELECT id, username, email, registration_date, is_admin FROM users;";
            $result = $conn->query($query);

            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
                if (isset($_GET["action"]) == "changeAdmin") {
                    $query = "SELECT is_admin FROM users WHERE id = " . $_GET["id"] . ";";
                    $res = $conn->query($query);
                    $row = $res->fetch_assoc();
                    $newAdminValue = ($row['is_admin'] == 1) ? 0 : 1;

                    $query = "UPDATE `users` SET `is_admin` = " . $newAdminValue . " WHERE `users`.`id` = " . $_GET["id"] . ";";
                    $result = $conn->query($query);

                    return;
                }
                echo "
                <br><br><h2 class='mar'>Edytuj użytkownika</h2>
                <form method='POST'>
                    <input type='hidden' name='id' value='" . $_GET["id"] . "'>";

                    if ($_GET["action"] == "changeMail") {
                        echo "<input type='email' name='new'>";
                        echo "<button class='btn' type='submit' name='action' value='changeMail'>Zmień email</button>";
                    } elseif ($_GET["action"] == "changePass") {
                        echo "<input type='password' name='new'>";
                        echo "<button class='btn' type='submit' name='action' value='changePass'>Zmień hasło</button>";
                    } else {
                        echo "<input type='text' name='new'>";
                        echo "<button class='btn' type='submit' name='action' value='rename'>Zmień nazwę</button>";
                    }
                    
                echo "</form>";
            }
        ?>
    </div>

    <div id="user-list" class="box center">
        <h2 class="center-text">Lista użytkowników</h2>
        <table>
            <tr>
                <th>Nazwa</th>
                <th>Email</th>
                <th>Utworzono</th>
                <th>Admin</th>
                <th>Akcje</th>
            </tr>
                <?php
                foreach ($result as $r) {
                    echo "<tr>
                    <td>" . $r['username'] . "</td>
                    <td>" . $r['email'] . "</td>
                    <td>" . $r['registration_date'] . "</td>
                    <td class='center-text'>" . ($r['is_admin'] == 1 ? 'Tak' : 'Nie') . "</td>
                    <td class='mar-t'>";
                        if ($_SESSION['username'] == $r['username']) {
                            echo "<form method='GET'>
                                <input type='hidden' name='id' value='" . $r['id'] . "'>
                                <button class='btn' type='submit' name='action' value='rename'>Zmień nazwę</button>
                                <button class='btn' type='submit' name='action' value='changeMail'>Zmień email</button>
                                <button class='btn' type='submit' name='action' value='changePass'>Zmień hasło</button>
                                <button class='danger-btn' type='submit' name='action' value='changeAdmin'>Zmień admina</button>
                            </form>";
                        } else {
                            echo "<form method='GET'>
                                <input type='hidden' name='id' value='" . $r['id'] . "'>
                                <button class='btn' type='submit' name='action' value='rename'>Zmień nazwę</button>
                                <button class='btn' type='submit' name='action' value='changeMail'>Zmień email</button>
                                <button class='btn' type='submit' name='action' value='changePass'>Zmień hasło</button>
                            </form>";
                            
                            echo "<form method='POST' style='margin-top: 5px;'>
                                <input type='hidden' name='id' value='" . $r['id'] . "'>
                                <button class='danger-btn' type='submit' name='action' value='remove'>Usuń konto</button>
                            </form>";
                        }
                    echo "</td>
                    <tr>";
                }
                ?>
            </tr>
        </table>
    </div>

    <div id="tickets" class="box center mar-t">
        <h2 class="center-text">Zarządzanie biletami</h2>

        <form method="POST">
            <label for='ticket-code'>Kod biletu:</label>
            <input type="text" name="ticket-code" id='ticket-code' minlength="8" maxlength="8" required>
            <button class='btn' type="submit">Pokaż bilet</button>
        </form>

        <?php
        $ticketResult = null;
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ticket-code"])) {
            $code = mysqli_real_escape_string($conn, $_POST["ticket-code"]);

            $ticketResult = $conn->query("
                SELECT t.id, u.username, u.email, t.code, t.price, t.paid,
                    t.creation_date, t.expiration_date, s.date, m.title
                FROM tickets t
                JOIN users u ON u.id = t.user_id
                JOIN schedule s ON s.id = t.schedule_id
                JOIN movies m ON m.id = s.movie_id
                WHERE t.code = '$code'
            ");
        }
        ?>

        <?php if ($ticketResult && $ticketResult->num_rows === 1): 
        $r = $ticketResult->fetch_assoc(); ?>

        <table style="margin-top:20px;">
        <tr>
            <th>Kod</th><th>Użytkownik</th><th>Email</th><th>Film</th>
            <th>Data seansu</th><th>Cena</th><th>Status</th>
        </tr>
        <tr class='center-text'>
            <td><?= $r['code'] ?></td>
            <td><?= $r['username'] ?></td>
            <td><?= $r['email'] ?></td>
            <td><?= $r['title'] ?></td>
            <td><?= $r['date'] ?></td>
            <td><?= $r['price'] ?> zł</td>
            <td><?= $r['paid'] ? "Opłacony" : "Nieopłacony" ?></td>
        </tr>
        </table>

        <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="ticket-id" value="<?= $r['id'] ?>">
            <input type="hidden" name="ticket-code" value="<?= $r['code'] ?>">
            <button class='btn' name="change-paid" value="<?= $r['paid'] ? 0 : 1 ?>">
                <?= $r['paid'] ? "Oznacz jako nieopłacony" : "Oznacz jako opłacony" ?>
            </button>
            <button name="delete-ticket" value="<?= $r['id'] ?>">
                Usuń bilet
            </button>
        </form>

        <?php elseif ($ticketResult): ?>
        <p style='color: red;'><b>Nie znaleziono biletu</b></p>
        <?php endif; ?>
    </div>

    <div id="schedule" class="box center mar-t mar-b">
        <h2 class="center-text">Zarządzanie seansami</h2>

        <form method='POST' style='margin-top: 5px;'>
            <input type='hidden' name='clean-schedule' value='1'>
            <button type='submit' class='danger-btn'>Usuń stare seanse</button>
        </form>

        <?php
        setlocale(LC_TIME, 'polish'); # pl_PL

        $query = "SELECT s.id AS schedule_id, s.date, m.title, m.img_url, s.hall_id
                FROM schedule AS s 
                INNER JOIN movies AS m ON s.movie_id = m.id 
                INNER JOIN halls AS h ON s.hall_id = h.id 
                ORDER BY s.date ASC, m.title ASC";

        $result = $conn->query($query);

        $schedule_tree = [];

        foreach ($result as $row) {
            $date_part = date('Y-m-d', strtotime($row['date']));
            $time_part = date('H:i', strtotime($row['date']));
            $title = $row['title'];
            
            if (!isset($schedule_tree[$date_part])) {
                $schedule_tree[$date_part] = [];
            }

            if (!isset($schedule_tree[$date_part][$title])) {
                $schedule_tree[$date_part][$title] = [
                    'screenings' => []
                ];
            }

            $schedule_tree[$date_part][$title]['screenings'][] = [
                'schedule_id' => $row['schedule_id'],
                'time' => $time_part,
                'hall_id' => $row['hall_id']
            ];
        }

        foreach ($schedule_tree as $day => $movies) {
            echo "<details class='mar-t'><summary>" . strftime('%e %B %Y', strtotime($day)) . "</summary>";

            foreach ($movies as $title => $details) {
                echo "<details style='margin-left: 20px;'><summary>" . htmlspecialchars($title) . "</summary>";
                
                echo "<div style='margin-left: 20px; margin-top: 10px;'>";
                echo "<p style='margin-bottom: 5px; margin-top: 5px;'><strong>Seanse:</strong></p>";
                
                foreach ($details['screenings'] as $screening) {
                    echo "<div class='schedule-item'>";
                    echo "<strong>Godzina:</strong> " . htmlspecialchars($screening['time']) . "<br>";
                    echo "<strong>Sala:</strong> " . htmlspecialchars($screening['hall_id']) . "<br>";
                    
                    echo "<form method='POST' style='margin-top: 5px;'>";
                    echo "<input type='hidden' name='delete-schedule' value='" . $screening['schedule_id'] . "'>";
                    echo "<button class='danger-btn' type='submit'>Usuń seans</button>";
                    echo "</form>";
                    
                    echo "</div>";
                }
                
                echo "</div>";
                echo "</details>";
            }

            echo "</details>";
        }
        ?>
        <h2 class="center-text">Dodaj seans</h2>
        <form method="POST" class="center">
            <input type="hidden" name="add-schedule" value="1">
            <label for="movie">Film:</label>
            <select name="movie_id" id="movie" required>
                <?php
                $moviesResult = $conn->query("SELECT id, title FROM movies WHERE visible = 1 ORDER BY title ASC;");
                while ($movie = $moviesResult->fetch_assoc()) {
                    echo "<option value='" . $movie['id'] . "'>" . htmlspecialchars($movie['title']) . "</option>";
                }
                ?>
            </select>

            <label for="hall">Sala:</label>
            <select name="hall_id" id="hall" required>
                <?php
                $hallsResult = $conn->query("SELECT id FROM halls ORDER BY id ASC;");
                while ($hall = $hallsResult->fetch_assoc()) {
                    echo "<option value='" . $hall['id'] . "'>" . htmlspecialchars($hall['id']) . "</option>";
                }
                ?>
            </select>

            <label for="datetime">Data i godzina:</label>
            <input type="datetime-local" name="datetime" id="datetime" required>
            <script>
                const now = new Date();
                
                const offset = now.getTimezoneOffset() * 60000;
                const localISOTime = new Date(now - offset).toISOString().slice(0, 16);

                document.getElementById('datetime').setAttribute('min', localISOTime);
            </script>

            <label for="private">Prywatny?</label>
            <select name="is_private" id="private" required>
                <option value='0'>Nie</option>
                <option value='1'>Tak</option>
            </select>

            <button class='btn' type="submit">Dodaj seans</button>
        </form>
    </div>

    <div id="movies" class="box center mar-t mar-b">
        <h2 class="center-text">Zarządzanie filmami</h2>
        <?php
        $query = "SELECT * FROM movies ORDER BY visible DESC, title ASC;";
        $result = $conn->query($query);
        foreach ($result as $r) {
            $sum = ($r['visible'] ? "" : "<i>") . $r['title'] . " - " . $r['director'] . ($r['visible'] ? "" : "</i>");
            echo "<details>
                <summary>" . $sum . "</summary>
                <p><b>Opis:</b> " . $r['description'] . "</p>
                <p><b>Długość:</b> " . $r['lenght'] . " min</p>
                <p><b>Cena:</b> " . $r['price'] . " zł</p>

                <form style='margin-bottom: 10px;' method='POST'>
                    <button class='btn' type='submit' name='toggle-visibility' value='" . $r['id'] . "'>" . ($r['visible'] ? "Ukryj film" : "Pokaż film") . "</button>
                    <button class='danger-btn' type='submit' name='delete-movie' value='" . $r['id'] . "'>Usuń film</button>
                </form>
            </details>";
        }
        ?>
    </div>
<?php include 'includes/footer.php'; ?>