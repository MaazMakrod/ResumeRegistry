<!DOCTYPE html>
<html>
<head>
<title>Maaz Makrod's Index Page</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Welcome to the Resume Registry</h1>
<p>
<a href="login.php">Please log in</a>
</p>

<?php
  $count = 1;
  require_once "pdo.php";

  echo('<table border="1" width = "100%">'."\n");
  $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    if($count === 1){
        echo('<tr><th width = "30%">Name</th><th width = "30%">Headline</th></tr>');
    }

    $count++;

    echo "<tr><td>";
    echo('<a href="details.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']) . ' ' . htmlentities($row['last_name']).'</a>');
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td></tr>\n");
  }
  echo('</table>');
  echo('<br/>');

  if($count === 1){
      echo('<p>No Rows Found</p>');
  }
?>

</div>
</body>
