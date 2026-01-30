<?php 
include 'includes/checkSession.php';
include 'includes/header.php';
include 'includes/connect.php';

?>

    <?php include 'includes/navigation.php'; ?>
    <h1 class="mar-t">Panel użytkownika</h1>
    <br><p class="mar-t">Co tam u ciebie <?php echo $_SESSION['username']; ?>?</p>
    
    <div class="mar-t">
        <ul>
            <li><b>Nazwa użytkownika:</b> <?php echo $_SESSION['username']; ?></li>
            <li><b>Adres e-mail:</b> <?php echo $_SESSION['email']; ?></li>
            <li><b>Uprawnienia administratora:</b> <?php echo ($_SESSION['is_admin'] == 1) ? 'Tak' : 'Nie'; ?></li>
        </ul>
    </div>
<?php include 'includes/footer.php'; ?>