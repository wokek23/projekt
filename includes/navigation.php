<nav>
    <ul>
        <a href="index.php" class="logo">
            <img src="img/logo.png" alt="logo">
        </a>
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