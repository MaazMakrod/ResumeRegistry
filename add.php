<?php
if(isset($_POST['canc'])){
  header("Location: view.php");
  return;
}

session_start();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['name'])){
  die('Access Denied');
}

require_once "pdo.php";
require_once "jquery.php";

if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])){
  if(strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1){
    $_SESSION['failure'] = 'All fields are required';
    header("Location: add.php");
    return;
  }
  elseif(strrpos($_POST['email'], '@') == FALSE){
    $_SESSION['failure'] = 'Email address must contain @';
    header("Location: add.php");
    return;
  }

  //Validate the positions
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) )
        continue;
      if ( ! isset($_POST['desc'.$i]) )
        continue;

      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
          $_SESSION['failure'] = "All fields are required";
          header("Location: add.php");
          return;
      }

      if ( ! is_numeric($year) ) {
          $_SESSION['failure'] = "Position year must be numeric";
          header("Location: add.php");
          return;
      }
  }

  //Validate Education
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year'.$i]) )
        continue;
      if ( ! isset($_POST['edu_school'.$i]) )
        continue;

      $year = $_POST['edu_year'.$i];
      $desc = $_POST['edu_school'.$i];

      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
          $_SESSION['failure'] = "All fields are required";
          header("Location: add.php");
          return;
      }

      if ( ! is_numeric($year) ) {
          $_SESSION['failure'] = "Position year must be numeric";
          header("Location: add.php");
          return;
      }
  }

  //All data is valid so create profile in profile table
  $sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary) VALUES (:user_id, :first_name, :last_name, :email, :headline, :summary)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(':user_id' => $_SESSION['user_id'],
                       ':first_name' => $_POST['first_name'],
                       ':last_name' => $_POST['last_name'],
                       ':email' => $_POST['email'],
                       ':headline' => $_POST['headline'],
                       ':summary' => $_POST['summary']));

 $profile_id = $pdo->lastInsertId();//Get profile
 $ranks = 1;

 //Add the positions in the position table
 for($i=1; $i<=9; $i++) {
     if ( ! isset($_POST['year'.$i]) )
       continue;
     if ( ! isset($_POST['desc'.$i]) )
       continue;

     $year = $_POST['year'.$i];
     $desc = $_POST['desc'.$i];

     $sql = "INSERT INTO position (profile_id, rank, year, description) VALUES (:profile_id, :rank, :year, :description)";
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(':profile_id' => $profile_id,
                          ':rank' => $ranks,
                          ':year' => $year,
                          ':description' => $desc));
    $ranks++;
 }

 //Add education to education mysql_list_tables
 $ranks = 1;

 for($i=1; $i<=9; $i++) {
     if ( ! isset($_POST['edu_year'.$i]) )
       continue;
     if ( ! isset($_POST['edu_school'.$i]) )
       continue;

     $year = $_POST['edu_year'.$i];
     $school = $_POST['edu_school'.$i];
     $institution_id = false;

     $sql = 'SELECT institution_id FROM Institution WHERE name = :name';
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(':name' => $school));
     $row = $stmt->fetch(PDO::FETCH_ASSOC);

     if($row !== false){
          $institution_id = $row['institution_id'];
     }
     else{
       $sql = "INSERT INTO Institution (name) VALUES (:school)";
       $stmt = $pdo->prepare($sql);
       $stmt->execute(array(':school' => $school));
       $institution_id = $pdo->lastInsertId();
     }


     $sql = "INSERT INTO Education (profile_id, rank, year, institution_id) VALUES (:profile_id, :rank, :year, :institution_id)";
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(':profile_id' => $profile_id,
                          ':rank' => $ranks,
                          ':year' => $year,
                          ':institution_id' => $institution_id));
    $ranks++;
 }

  $_SESSION['success'] = 'added';//change to Profile Inserted
  header("Location: view.php");
  return;
}
else if(isset($_POST['add'])){
  $_SESSION['failure'] = 'All fields are required';
}
?>

<script>
let countPos = 0;
let countEdu = 0;

$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p><br/> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div><br/><br/>');
    });

    $('#addEdu').click(function(event){
      event.preventDefault();
      if ( countEdu >= 9 ) {
          alert("Maximum of nine education entries exceeded");
          return;
      }
      countEdu++;
      window.console && console.log("Adding education "+countEdu);

      $('#edu_fields').append(
          '<div id="edu'+countEdu+'"> \
          <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
          <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
          <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
          </p></div><br/><br/>');

      $('.school').autocomplete({
          source: "school.php"
      });
    });
});
</script>

<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Maaz Makrod's Add Page</title>
</head>
<body>
<div class="container">
<h1>Adding Profile for <?= $_SESSION['name']?></h1>

<p>
<?php
if (isset($_SESSION['failure'])) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['failure'])."</p>\n");
    unset($_SESSION['failure']);
}
?>
</p>

<form method="POST">
  <label for="first">First Name: </label><br/>
  <input type="text" name="first_name" id="first"><br/><br/>
  <label for="last">Last Name: </label><br/>
  <input type="text" name="last_name" id="last"><br/><br/>
  <label for="email">Email: </label><br/>
  <input type="text" name="email" id="email"><br/><br/>
  <label for="headline">Headline: </label><br/>
  <input type="text" name="headline" id="headline"><br/><br/>
  <label for="summary">Summary: </label><br/>
  <textarea name="summary" rows="8" cols="80"></textarea><br/><br/>
  <label for="addEdu">Education: </label>
  <input type="submit" id="addEdu" value="+"><br/><br/>

  <div id = "edu_fields">

  </div>

  <label for="addPos">Position: </label>
  <input type="submit" id="addPos" value="+"><br/><br/>

  <div id = "position_fields">

  </div>

  <input type="submit" name="add" value="Add">
  <input type="submit" name="canc" value="Cancel">
</form>

</div>
</body>
