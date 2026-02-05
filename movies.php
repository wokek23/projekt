<?php
  include 'includes/connect.php';
  session_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja strona</title>
    <link rel="stylesheet" href="css/movies.css">
</head>
<body>

  <?php include 'includes/navigation.php'; ?>

  <main class="main-content">
    <section class="movies">
      <h1>All movies</h1>
      <?php
        $querry = "SELECT title, description, director, img_url FROM movies ORDER BY title";
        $result = $conn->query($querry);

        if ($result->num_rows > 0) {
          foreach ($result as $row) {
            echo "<div class='movie'>
                    <img src='" 
                    . (isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png') . "' alt='Placeholder'>
                    <div class='movie-info'>
                      <h3>" . htmlspecialchars($row['title']) . "</h3>
                      <p>" . htmlspecialchars($row['description']) . "</p>
                    </div>
                  </div>";
          }
        }
      ?>
    </section>

    <section class="movies">
      <h1>Top Rated Movies</h1>
      <?php
        $querry = "SELECT title, description, director, img_url FROM movies ORDER BY title"; #"SELECT m.title, m.description, m.director, m.img_url FROM movies AS m INNER JOIN rating AS r ON r.movie_id = m.id ORDER BY rate DESC";
        $result = $conn->query($querry);

        if ($result->num_rows > 0) {
          foreach ($result as $row) {
            echo "<div class='movie'>
                    <img src='" 
                    . (isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png') . "' alt='Placeholder'>
                    <div class='movie-info'>
                      <h3>" . htmlspecialchars($row['title']) . "</h3>
                      <p>" . htmlspecialchars($row['description']) . "</p>
                    </div>
                  </div>";
          }
        }
      ?>
    </section>
  </main>
<?php include 'includes/footer.php'; ?>