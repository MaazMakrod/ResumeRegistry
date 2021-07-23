<?php
//if ( ! isset($_GET['name']) || strlen($_GET['name']) < 1  ) {
  //  die('Name parameter missing');
//}

session_start();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['name'])){
  die('Not logged in');
}
?>

<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Maaz Makrod's View Page</title>
</head>
<body>
<div class="container">
<h1>Welcoming to the Resume Registry</h1>

<p>
<?php
if (isset($_SESSION['success'])) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>
</p>

<?php
  $count = 1;
  require_once "pdo.php";

  echo('<table border="1" width = "100%">'."\n");
  $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    if($count === 1){
        echo('<tr><th width = "30%">Name</th><th width = "30%">Headline</th><th width = "30%">Action</th></tr>');
    }

    $count++;

    echo "<tr><td>";
    echo('<a href="details.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']) . ' ' . htmlentities($row['last_name']).'</a>');
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td><td>");
    echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
    echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
    echo("</td></tr>\n");
  }
  echo('</table>');
  echo('<br/>');

  if($count === 1){
      echo('<p>No Rows Found</p>');
  }
?>

<p><a href = "add.php">Add New Entry</a></p>
<p><a href = "logout.php">Logout</a></p>

</div>
</body>
