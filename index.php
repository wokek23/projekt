<?php
include 'includes/header.php';
include 'includes/connect.php';
?>

  <?php include 'includes/navigation.php'; ?>
  
  <div class="slider">
    <div class="slides">
      <img src="img/placeholder.png" class="slide">
      <img src="img/placeholder.png" class="slide">
      <img src="img/placeholder.png" class="slide">
    </div>
  </div>

  <section class="movies">
    <div class="movie">
      <img src="img/filmimg.png" alt="Placeholder">
      <div class="movie-info">
        <h3>Nazwa filmu</h3>
        <p>Opis filmu</p>
      </div>
    </div>

    <div class="movie">
      <img src="img/filmimg.png" alt="Placeholder">
      <div class="movie-info">
        <h3>Nazwa filmu</h3>
        <p>Opis filmu</p>
      </div>
    </div>

    <div class="movie">
      <img src="img/filmimg.png" alt="Placeholder">
      <div class="movie-info">
        <h3>Nazwa filmu</h3>
        <p>Opis filmu</p>
      </div>
    </div>

    <div class="movie">
      <img src="img/filmimg.png" alt="Placeholder">
      <div class="movie-info">
        <h3>Nazwa filmu</h3>
        <p>Opis filmu</p>
      </div>
    </div>
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