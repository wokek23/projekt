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

    
    <div class="mar-t">
        <?php
            $query = "SELECT id, username, email, registration_date FROM users WHERE id = {$_SESSION['user_id']};";
            $result = $conn->query($query);

            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
                echo "
                <br><br><h2 class='mar'>Edytuj dane</h2>
                <form method='POST'>
                    <input type='hidden' name='id' value='" . $_GET["id"] . "'>";

                    if ($_GET["action"] == "changeMail") {
                        echo "<input type='email' name='new'>";
                        echo "<button type='submit' name='action' value='changeMail'>Zmień email</button>";
                    } elseif ($_GET["action"] == "changePass") {
                        echo "<input type='password' name='new'>";
                        echo "<button type='submit' name='action' value='changePass'>Zmień hasło</button>";
                    } else {
                        echo "<input type='text' name='new'>";
                        echo "<button type='submit' name='action' value='rename'>Zmień nazwę</button>";
                    }
                    
                echo "</form>";
            }
        ?>
    </div>

    <div id="dane" class="box center mar-t">
        <h2 class="center-text">Twoje dane</h2>
        Nazwa użytkownika: <?php echo $_SESSION['username']; 
        foreach ($result as $r) {
            echo "<form method='GET'>
                <input type='hidden' name='id' value='" . $r['id'] . "'>
                <button type='submit' name='action' value='rename'>Zmień nazwę</button>
                <button type='submit' name='action' value='changePass'>Zmień hasło</button>
            </form>";
        }
        ?><br>
        Email: <?php
            echo $r['email'];
            foreach ($result as $r) {
                echo"<form method='GET'>
                        <input type='hidden' name='id' value='" . $r['id'] . "'>
                        <button type='submit' name='action' value='changeMail'>Zmień email</button>
                    </form>";
            }
            ?>
    </div>

    <div id="tickets" class="box center mar-t">
        <h2 class="center-text">Twoje bilety</h2>
    </div>

<?php include 'includes/footer.php'; ?>