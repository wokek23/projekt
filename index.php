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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/navigation.php'; ?>
  
  <div class="slider">
    <div class="slides">
      <?php
        include 'includes/connect.php';
        $query = "SELECT img_url FROM movies ORDER BY RAND() LIMIT 5";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
          foreach ($result as $row) {
            echo "<img src='" 
                 . (isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png') 
                 . "' class='slide'>";
          }
        }
      ?>
    </div>
  </div>

  <section class="movies">
    <?php
      $query = "SELECT title, description, director, img_url FROM movies ORDER BY RAND() LIMIT 5";
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

  <script>
    const slides = document.querySelector('.slides');
    const slideCount = document.querySelectorAll('.slide').length;

    let index = 0;

    setInterval(() => {
      index = (index + 1) % slideCount;
      slides.style.transform = `translateX(-${index * 100}%)`; }, 3000);
  </script>
<?php include 'includes/footer.php'; ?>