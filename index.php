<?php
  include 'includes/connect.php';
  session_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repertuar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <main>
  <?php include 'includes/navigation.php'; ?>
  
  <div class="slider">
    <div class="slides">
      <!-- <?php
        #include 'includes/connect.php';
        #$query = "SELECT baner_img FROM movies WHERE visible = 1 ORDER BY RAND() LIMIT 5";
        #$result = $conn->query($query);

        #if ($result->num_rows > 0) {
          #foreach ($result as $row) {
            #echo "<img src='" 
                 #. (isset($row['baner_img']) ? htmlspecialchars($row['baner_img']) : 'img/placeholder.png') 
                 #. "' class='slide'>";
          #}
        #}
      #?>-->
      <img  src="img/placeholder.png" class="slide">
    <img src="img/placeholder.png" class="slide">
    <img src="img/placeholder.png" class="slide">
    </div>
  </div>
  </main>

  <?php
    // #$query = "SELECT id, title, description, director, img_url FROM movies WHERE visible = 1 ORDER BY RAND() LIMIT 5"; #"SELECT m.title, m.description, m.director, m.img_url FROM movies AS m INNER JOIN rating AS r ON r.movie_id = m.id ORDER BY rate DESC";
    // #$result = $conn->query($query);

    // if ($result->num_rows > 0) {
    //   foreach ($result as $row) {
    //     $stars_query = "SELECT rate FROM rating WHERE movie_id = " . intval($row['id']) . " ORDER BY rate DESC";
    //     $stars_result = $conn->query($stars_query);
    //     $rate = 0;
    //     $count = 0;
    //     if ($stars_result->num_rows > 0) {
    //       $total_rate = 0;
    //       foreach ($stars_result as $star_row) {
    //         $total_rate += intval($star_row['rate']);
    //         $count++;
    //       }
    //       $rate = $total_rate / $count;
    //     }
    //     $rounded = round($rate);
    //     echo "<div class='movie'>
    //             <img src='" 
    //             . (isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png') . "' alt='Placeholder'>
    //             <div class='movie-info'>
    //               <h3>" . htmlspecialchars($row['title']) . "</h3>
    //               <p>" . htmlspecialchars($row['description']) . "</p>";
    //               if ($count > 0) {
    //                 echo "<p>";
    //                   for ($i = 1; $i <= 5; $i++) {   
    //                     if ($i <= $rounded) {
    //                       echo "<span class='fa fa-star' style='color: orange;'></span>";
    //                     } else {
    //                       echo "<span class='fa fa-star'></span>";
    //                     }
    //                   }
    //                   echo "<span> " . $rounded . "/5</span></p>";
    //               } else {
    //                 echo "<p><i>No ratings yet.</i></p>";
    //               }
    //           echo "</div>
    //               #</div>";
    //       #}
    //     #}
      #?>

  <div class="days">
      <?php
      $dayNames = ['niedz.', 'pon.', 'wt.', 'śr.', 'czw.', 'pt.', 'sob.'];
      $today = new DateTime();

      for ($i = 0; $i < 6; $i++) {
          $date = clone $today;
          $date->modify("+$i day");
          $dataDay = $date->format('Y-m-d');
          $text = $i === 0 ? 'Dzisiaj' : $dayNames[$date->format('w')];
          $activeClass = $i === 0 ? 'active' : '';
          echo "<button class='day $activeClass' data-day='$dataDay'>$text</button>";
      }
      ?>
      <button class ="wiecej"><a href="schedule.php">Wiecej dni..</a></button>;
      </div>

    <?php
      $query = "SELECT movies.title, schedule.date FROM movies INNER JOIN schedule ON movies.id = schedule.movie_id";
      $result = $conn->query($query);
    ?>

    <section class="movies">
    <?php
      foreach ($result as $movie):
      $dateOnly = date('Y-m-d', strtotime($movie['date']));
    ?>
      <div class="movie" data-day="<?= $dateOnly ?>">
        <img src="img/filmimg.png" alt="Placeholder">
        <div class="movie-info">
          <h3><?= $movie['title'] ?></h3>
          <p>Opis filmu</p>
        </div>
      </div>
    <?php endforeach ?>
    </section>

  <script>
    //do slajdow
    const slides = document.querySelector('.slides');
    const slideCount = document.querySelectorAll('.slide').length;

    let index = 0;

    setInterval(() => {
      index = (index + 1) % slideCount;
      slides.style.transform = `translateX(-${index * 100}%)`; }, 3000);

    //do kategori
    const days = document.querySelectorAll('.day');
    const movies = document.querySelectorAll('.movie');

    days.forEach(day => {
      day.addEventListener('click', () => {

        days.forEach(d => d.classList.remove('active'));
        day.classList.add('active');

        const selectedDay = day.dataset.day;

        movies.forEach(movie => {
          if (movie.dataset.day === selectedDay) {
            movie.style.display = 'flex';
          } else {
            movie.style.display = 'none';
          }
        });
      });
    });
    const firstDay = document.querySelector('.day.active').dataset.day;
    movies.forEach(movie => {
      movie.style.display = movie.dataset.day === firstDay ? 'flex' : 'none';
    });
  </script>
<?php include 'includes/footer.php'; ?>