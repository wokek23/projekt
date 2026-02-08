<?php
  include 'includes/connect.php';
  session_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan filmów</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <?php
    setlocale(LC_TIME, 'polish'); # pl_PL

    $query = "SELECT m.id, s.date, m.title, m.img_url, s.hall_id 
            FROM schedule AS s 
            INNER JOIN movies AS m ON s.movie_id = m.id 
            WHERE s.date >= CURDATE() 
            ORDER BY s.date ASC, m.title ASC";

    $result = $conn->query($query);

    $schedule_tree = [];

    foreach ($result as $row) {
        $date_part = date('Y-m-d', strtotime($row['date']));
        $time_part = date('H:i', strtotime($row['date']));
        $title = $row['title'];

        if (!isset($schedule_tree[$date_part])) {
            $schedule_tree[$date_part] = [];
        }

        if (!isset($schedule_tree[$date_part][$title])) {
            $schedule_tree[$date_part][$title] = [
                'movie_id' => $row['id'],
                'img' => isset($row['img_url']) ? htmlspecialchars($row['img_url']) : 'img/placeholder.png',
                'times' => []
            ];
        }

        $schedule_tree[$date_part][$title]['times'][] = $time_part;
    }

    foreach ($schedule_tree as $day => $movies) {
        echo "<div class='day'>";
        echo "<h2>" . strftime('%e %B %Y', strtotime($day)) . "</h2>";

        foreach ($movies as $title => $details) {
            echo "<div class='schedule-item' style='display: flex; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;'>";
            echo "<div><img src='" . $details['img'] . "' class='movie_img' alt='Poster' style='width: 100px; margin-right: 15px;'></div>";
            echo "<div>";
            echo "<h3>" . $title . "</h3>";
            echo "<p><strong>Godziny seansów:</strong> " . implode(', ', $details['times']) . "</p>";
            echo "<form method='POST' action='buy.php'>
                    <input type='hidden' name='movie_id' value='" . htmlspecialchars($details['movie_id']) . "'>
                    <input type='hidden' name='day' value='" . htmlspecialchars($day) . "'>
                    <button type='submit' class='buy-btn'>Kup bilet</button>
                </form>";
            echo "</div>";
            echo "</div>";
        }

        echo "</div>";
    }
    ?>

<?php include 'includes/footer.php'; ?>