<nav>
    <link rel="stylesheet" href="css/nav.css">
    <ul>
        <li><img  src="img/capimovies.png" alt="logo" style="width: 200px; height: 30px;"></li>
        <li><a href="index.php">REPERTUAR</a></li>
        <li><a href="movies.php">Filmy</a></li>
        <li><a href="schedule.php">Plan kina</a></li>
            
        <!-- Gdy niezalogowany -->
        <?php if (!isset($_SESSION['logged_in'])): ?>
        <li><a href="login.php">Zaloguj</a></li>
        <li><a href="register.php">Zarejestruj</a></li>

        <!-- Gdy zalogowany -->
        <?php else: ?>
        <li><a href="user.php">Profil</a></li>

        <?php if ($_SESSION['is_admin'] == 1)
            echo '<li><a href="admin.php">Panel Admina</a></li>';
        ?>
            
        <li>
            <span class="nav-username">Witaj, <?php echo $_SESSION['username'] ?>!</span>
            <a href="includes/logout.php">Wyloguj</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>