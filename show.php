<?php
session_start();
include("config/db.php");

$pageTitle = "MovieTime";
include("includes/site_header.php");

$moviesQuery = "SELECT * FROM movies WHERE is_active = 1 ORDER BY id DESC";
$moviesResult = $conn->query($moviesQuery);
?>

<div class="main">
  <img src="./assets/toxic1.jpg" class="banner-img" alt="">
</div>

<br>

<div class="container">
  <h2>Recommended Movies</h2>
  <br>

  <div class="card movies-grid">
    <?php if ($moviesResult && $moviesResult->num_rows > 0): ?>
      <?php while($movie = $moviesResult->fetch_assoc()): ?>
        <?php
          $movieId = (int)($movie['id'] ?? 0);
          $movieTitle = $movie['title'] ?? 'Untitled Movie';
          $moviePoster = !empty($movie['image']) ? $movie['image'] : './assets/image.png';
          $movieRating = $movie['rating'] ?? 'New Release';
          $movieVotes = $movie['votes'] ?? '';
          $movieLanguage = $movie['language'] ?? 'Language N/A';
          $movieDuration = $movie['duration'] ?? '';
          $movieGenre = $movie['genre'] ?? '';
        ?>

        <div class="movie"
             data-id="<?= $movieId ?>"
             data-name="<?= htmlspecialchars($movieTitle) ?>"
             data-image="<?= htmlspecialchars($moviePoster) ?>"
             data-rating="<?= htmlspecialchars($movieRating) ?>">

          <a href="movie_details.php?id=<?= $movieId ?>">
            <img src="<?= htmlspecialchars($moviePoster) ?>" alt="<?= htmlspecialchars($movieTitle) ?>">
          </a>

          <p>
            <i class="fa-solid fa-star" style="color: rgb(252, 150, 116);"></i>
            <?= htmlspecialchars($movieRating) ?>
            <?= !empty($movieVotes) ? htmlspecialchars($movieVotes) : '' ?>
          </p>

          <h3>
            <a href="movie_details.php?id=<?= $movieId ?>" style="color:white;text-decoration:none;">
              <?= htmlspecialchars($movieTitle) ?>
            </a>
          </h3>

          <small style="color:#aaa; display:block; margin-top:4px;">
            <?= htmlspecialchars($movieLanguage) ?>
            <?php if (!empty($movieDuration)): ?>
              • <?= htmlspecialchars($movieDuration) ?>
            <?php endif; ?>
            <?php if (!empty($movieGenre)): ?>
              • <?= htmlspecialchars($movieGenre) ?>
            <?php endif; ?>
          </small>

          <button onclick="openSeatSelection(<?= $movieId ?>)">
            Book Now
          </button>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="color:white; padding:20px;">No movies found in database.</p>
    <?php endif; ?>
  </div>
</div>

<br><br>

<div class="main">
  <img src="./assets/my banner.png" alt="my banner">
</div>

<div class="container">
  <h2>Best of Live Events</h2>

  <div class="card">
    <img src="./assets/comedy-shows-collection-202211140440.avif" alt="">
    <img src="./assets/theatre-shows-collection-202211140440.avif" alt="">
    <img src="./assets/music-shows-collection-202211140440.avif" alt="">
    <img src="./assets/kids-banner-desktop-collection-202503251132.avif" alt="">
    <img src="./assets/workshop-and-more-web-collection-202211140440.avif" alt="">
  </div>

  <div class="premiere">
    <h2>PREMIERE</h2>
    <br>

    <div class="card">
      <img src="./assets/et00423962-kharxtjzwd-portrait.avif" alt="">
      <img src="./assets/et00311714-mbkzgfdlyy-portrait.avif" alt="">
      <img src="./assets/et00478731-ymmfbnvbaq-portrait.avif" alt="">
      <img src="./assets/et00479213-bhldtwxzcq-portrait.avif" alt="">
    </div>
  </div>
</div>

<script>
function searchMovies() {
  let input = document.getElementById("searchInput");
  let resultsBox = document.getElementById("searchResults");
  let movies = document.getElementsByClassName("movie");

  if (!input || !resultsBox) return;

  let value = input.value.toLowerCase().trim();
  resultsBox.innerHTML = "";

  if (value === "") {
    resultsBox.style.display = "none";
    return;
  }

  let found = false;

  for (let i = 0; i < movies.length; i++) {
    let movieId = movies[i].dataset.id;
    let name = movies[i].dataset.name;
    let image = movies[i].dataset.image;
    let rating = movies[i].dataset.rating || "";

    if (!name) continue;

    if (name.toLowerCase().includes(value)) {
      found = true;

      let item = document.createElement("div");
      item.classList.add("search-item");

      item.innerHTML = `
        <img src="${image}">
        <div>
          <strong>${name}</strong>
          <p style="margin:0;font-size:14px;color:gray;">⭐ ${rating}</p>
        </div>
      `;

      item.onclick = function() {
        window.location.href = "movie_details.php?id=" + movieId;
      };

      resultsBox.appendChild(item);
    }
  }

  if (!found) {
    resultsBox.innerHTML = "<p style='padding:10px'>No movie found</p>";
  }

  resultsBox.style.display = "block";
}

function handleSearch(event) {
  if (event.key === "Enter") {
    goToMovie();
  } else {
    searchMovies();
  }
}

function goToMovie() {
  let input = document.getElementById("searchInput");
  let movies = document.getElementsByClassName("movie");

  if (!input) return;

  let value = input.value.toLowerCase().trim();

  for (let i = 0; i < movies.length; i++) {
    let name = movies[i].dataset.name;
    if (!name) continue;

    if (name.toLowerCase().includes(value)) {
      movies[i].scrollIntoView({ behavior: "smooth" });
      movies[i].style.border = "3px solid red";

      setTimeout(() => {
        movies[i].style.border = "none";
      }, 2000);

      return;
    }
  }

  alert("Movie not found!");
}

function openSeatSelection(movieId) {
  window.location.href = "seat.php?movie_id=" + movieId;
}
</script>

<?php include("includes/site_footer.php"); ?>