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
    <h1 class="mar-t">Panel Użytkownika</h1>
    <?php //if (isset($returnMsg)) { echo "<p><b><i>" . $returnMsg . "</i></b><p/>"; } ?>
    <br><p class="mar-t">Co tam u ciebie <?php echo $_SESSION['username']; ?>?</p>
    
    <div class="mar-t">
        <?php
            $query = "SELECT id, username, email, registration_date, is_admin FROM users WHERE id = {$_SESSION['user_id']};";
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

    <div class="box center">
        <h2 class="center-text">Twoje dane</h2>
        Nazwa użytkownika: <?php echo $_SESSION['username']; ?><br>
        Email: <?php
            $row = $result->fetch_assoc();
            echo $row['email'];
            ?></tr>
                <?php
                foreach ($result as $r) {
                    echo "<tr>
                    <td>" . $r['username'] . "</td>
                    <td>" . $r['email'] . "</td>
                    <td>" . $r['registration_date'] . "</td>
                    <td class='center-text'>" . ($r['is_admin'] == 1 ? 'Tak' : 'Nie') . "</td>
                    <td class='mar-t'>
                        <form method='GET'>
                            <input type='hidden' name='id' value='" . $r['id'] . "'>
                            <button type='submit' name='action' value='rename'>Zmień nazwę</button>
                            <button type='submit' name='action' value='changeMail'>Zmień email</button>
                            <button type='submit' name='action' value='changePass'>Zmień hasło</button>
                        </form>
                    </td>
                <tr>";
                }
                ?>
            </tr>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>