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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

  <?php include 'includes/navigation.php'; ?>

  <main class="main-content">
    <section class="movies">
      <h1>All movies</h1>
      <?php
        $query = "SELECT title, description, director, img_url FROM movies ORDER BY title";
        $result = $conn->query($query);

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
        $query = "SELECT id, title, description, director, img_url FROM movies ORDER BY title"; #"SELECT m.title, m.description, m.director, m.img_url FROM movies AS m INNER JOIN rating AS r ON r.movie_id = m.id ORDER BY rate DESC";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
          foreach ($result as $row) {
            $stars_query = "SELECT rate FROM rating WHERE movie_id = " . intval($row['id']) . " ORDER BY rate DESC";
            $stars_result = $conn->query($stars_query);
            $rate = 0;
            $count = 0;
            if ($stars_result->num_rows > 0) {
              $total_rate = 0;
              foreach ($stars_result as $star_row) {
                $total_rate += intval($star_row['rate']);
                $count++;
              }
              $rate = $total_rate / $count;
            }
            $rounded = round($rate);
            echo "<div class='movie'>
                    <img src='" 
                    . (isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png') . "' alt='Placeholder'>
                    <div class='movie-info'>
                      <h3>" . htmlspecialchars($row['title']) . "</h3>
                      <p>" . htmlspecialchars($row['description']) . "</p>";
                      if ($count > 0) {
                        echo "<p>";
                          for ($i = 1; $i <= 5; $i++) {   
                            if ($i <= $rounded) {
                              echo "<span class='fa fa-star' style='color: orange;'></span>";
                            } else {
                              echo "<span class='fa fa-star'></span>";
                            }
                          }
                          echo "<span> " . $rounded . "/5</span></p>";
                      } else {
                        echo "<p><i>No ratings yet.</i></p>";
                      }
                  echo "</div>
                      </div>";
          }
        }
      ?>
    </section>
  </main>
<?php include 'includes/footer.php'; ?>