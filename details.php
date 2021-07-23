<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Maaz Makrod's Detail Page</title>
</head>
<body>
<div class="container">
<h1>Profile Information</h1>

<?php
  session_start();
  require_once("pdo.php");

  $stmt = $pdo->prepare('SELECT first_name, last_name, email, headline, summary FROM profile WHERE profile_id = :profile_id');
  $stmt->execute(array( ':profile_id' => $_GET['profile_id']));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  echo('<p><strong>First Name</strong>: '. htmlentities($row['first_name']) . '</p>');
  echo('<p><strong>Last Name</strong>: '. htmlentities($row['last_name']) . '</p>');
  echo('<p><strong>Email</strong>: '. htmlentities($row['email']) . '</p>');
  echo('<p><strong>Headline</strong>:</p>' . '<p>' . htmlentities($row['headline']) . '</p>');
  echo('<p><strong>Summary</strong>: </p>' . '<p>' . htmlentities($row['summary']) . '</p>');

  $rank = 1;
  $found = false;
  $first = true;

  for($i=1; $i<=9; $i++) {
    $stmt = $pdo->prepare('SELECT year, institution_id FROM education WHERE profile_id = :profile_id AND rank = :rank');
    $stmt->execute(array( ':profile_id' => $_GET['profile_id'], ':rank' => $rank));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row === false){
      continue;
    }

    $found  = true;

    if($found && $first){
      echo('<p><strong>Education</strong>:</p>');
      echo('<ul>');
      $first = false;
    }

    $year = $row['year'];

    $stmt = $pdo->prepare('SELECT name FROM Institution WHERE institution_id = :institution_id');
    $stmt->execute(array( ':institution_id' => $row['institution_id']));
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    echo('<li>' . $year . ': ' . $school['name'] . '</li>');

    $rank++;
  }

  echo('</ul>');

  $rank = 1;
  $found = false;
  $first = true;

  for($i=1; $i<=9; $i++) {
    $stmt = $pdo->prepare('SELECT year, description FROM position WHERE profile_id = :profile_id AND rank = :rank');
    $stmt->execute(array( ':profile_id' => $_GET['profile_id'], ':rank' => $rank));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row === false){
      continue;
    }

    $found  = true;

    if($found && $first){
      echo('<p><strong>Position</strong>:</p>');
      echo('<ul>');
      $first = false;
    }

    $year = $row['year'];
    $desc = $row['description'];

    echo('<li>' . $year . ': ' . $desc . '</li>');

    $rank++;
  }

  echo('</ul>');

  if(isset($_SESSION['user_id'])){
    echo('<a href = "view.php">Done</a>');
  } else{
    echo('<a href = "index.php">Done</a>');
  }
?>

</div>
</body>
