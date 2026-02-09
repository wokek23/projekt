<?php 
include 'includes/checkSession.php';
include 'includes/connect.php';

$returnMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {        
    if ($_POST["action"] == "rename" && isset($_POST["new"])) {
        $new = mysqli_real_escape_string($conn, $_POST["new"]);

        $query = "UPDATE `users` SET `username` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
        $result = $conn->query($query);
        
        $_SESSION['username'] = $new;

        $returnMsg = "Zmieniono nazwę!";
        
    } elseif ($_POST["action"] == "changeMail" && isset($_POST["new"])) {
        $new = mysqli_real_escape_string($conn, $_POST["new"]);

        $query = "UPDATE `users` SET `email` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
        $result = $conn->query($query);

        $_SESSION['email'] = $new;

        $returnMsg = "Zmieniono adres email!";
        
    } elseif ($_POST["action"] == "changePass" && isset($_POST["new"])) {
        $new = password_hash(mysqli_real_escape_string($conn, $_POST["new"]), PASSWORD_DEFAULT);
        
        $query = "UPDATE `users` SET `password` = '" . $new ."' WHERE `users`.`id` = " . $_POST["id"] . ";";
        $result = $conn->query($query);

        $returnMsg = "Zmieniono hasło!";
        
    } else {
        $returnMsg = "Nieznana akcja!";
    }

    if ($_POST["id"] == $_SESSION['user_id']) {
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mój profil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <div class="box center center-text mar-t">
        <h1>Panel Użytkownika</h1>
        <ul class="list-none">
            <li><a href="#dane">Twoje dane</a></li>
            <li><a href="#tickets">Twoje bilety</a></li>
        </ul>
        <?php //if (isset($returnMsg)) { echo "<p><b><i>" . $returnMsg . "</i></b><p/>"; } ?>
    </div>

    
    <?php
    $query = "SELECT id, username, email, registration_date FROM users WHERE id = {$_SESSION['user_id']};";
    $result = $conn->query($query);
    $r = $result->fetch_assoc();

    $ticketResult = $conn->query("
                SELECT t.code, t.price, t.paid, t.expiration_date, s.date, s.hall_id, m.title
                FROM tickets t
                JOIN users u ON u.id = t.user_id
                JOIN schedule s ON s.id = t.schedule_id
                JOIN movies m ON m.id = s.movie_id
                WHERE u.id = '{$_SESSION['user_id']}';
                ");
    ?>

    <div id="dane" class="box center mar-t">
        <h2 class="center-text">Twoje dane</h2>
        <p>Nazwa użytkownika: 
        <?php 
        echo $_SESSION['username']; 
        echo "<form method='GET'>
            <input type='hidden' name='id' value='" . $r['id'] . "'>
            <button class='btn' type='submit' name='action' value='rename'>Zmień nazwę</button>
            <button class='btn' type='submit' name='action' value='changePass'>Zmień hasło</button>
        </form>";
        ?>
        </p><br>

        <p>Email: 
            <?php
            echo $r['email'];
            echo"<form method='GET'>
                <input type='hidden' name='id' value='" . $r['id'] . "'>
                <button class='btn' type='submit' name='action' value='changeMail'>Zmień email</button>
            </form>";
            ?>
        </p>
    </div>

    <div id="tickets" class="box center mar-t">
        <h2 class="center-text">Twoje bilety</h2>
        
        <?php 
        if ($ticketResult && $ticketResult->num_rows > 0) {
            echo "<table>
                <tr>
                    <th>Kod</th>
                    <th>Film</th>
                    <th>Data seansu</th>
                    <th>Sala</th>
                    <th>Wygasa</th>
                    <th>Cena</th>
                    <th>Status</th>
                </tr>";
            
            foreach ($ticketResult as $t) {
                $seans_date = date('Y-m-d H:i', strtotime($t['date']));
                $expiration_date = date('Y-m-d', strtotime($t['expiration_date']));
                
                $is_expired = (strtotime($t['expiration_date']) < time());
                $payment_status = $t['paid'] ? "Opłacony" : "Nieopłacony";

                echo "<tr class='center-text'>
                    <td>" . htmlspecialchars($t['code']) . "</td>
                    <td>" . htmlspecialchars($t['title']) . "</td>
                    <td>" . htmlspecialchars($seans_date) . "</td>
                    <td>" . htmlspecialchars($t['hall_id']) . "</td>
                    <td>" . htmlspecialchars($expiration_date) . ($is_expired ? " (WYGASŁ)" : "") . "</td>
                    <td>" . htmlspecialchars($t['price']) . " zł</td>
                    <td>" . htmlspecialchars($payment_status) . "</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='center-text' style='color: red;'><b>Nie posiadasz żadnych biletów</b></p>";
        }
        ?>
    </div>

<?php include 'includes/footer.php'; ?>