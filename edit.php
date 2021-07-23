<?php
require_once "pdo.php";
require_once "bootstrap.php";
require_once "jquery.php";
session_start();

if(isset($_POST['cancel'])){
  header('Location: view.php');
  return;
}

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['profile_id']) ) {

    // Data validation
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = 'Missing data';
        header("Location: edit.php?profile_id=".$_GET['profile_id']);
        return;
    }
    elseif(strrpos($_POST['email'], '@') == FALSE){
      $_SESSION['error'] = 'Email address must contain @';
      header("Location: edit.php?profile_id=".$_GET['profile_id']);
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
            $_SESSION['error'] = "All fields are required";
            header("Location: edit.php?profile_id=".$_GET['profile_id']);
            return;
        }

        if ( ! is_numeric($year) ) {
            $_SESSION['error'] = "Position year must be numeric";
            header("Location: edit.php?profile_id=".$_GET['profile_id']);
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

    $sql = "UPDATE profile SET first_name = :first_name, last_name = :last_name, email = :email, headline = :headline, summary = :summary WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':profile_id' => $_GET['profile_id']));

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_GET['profile_id']));

    // Insert the position entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $_GET['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc));
        $rank++;
    }

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_GET['profile_id']));

    // Insert the position entries
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
      $stmt->execute(array(':profile_id' => $_GET['profile_id'],
                           ':rank' => $ranks,
                           ':year' => $year,
                           ':institution_id' => $institution_id));
     $ranks++;
    }

    $_SESSION['success'] = 'Profile updated';
    header('Location: view.php') ;
    return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: view.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: view.php' ) ;
    return;
}
?>

<script>
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

<h1>Edit Profile</h1>

<?php
// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>


<form method="post">
<label for="first">First Name: </label><br/>
<input type="text" name="first_name" id="first" value="<?= htmlentities($row['first_name']) ?>"><br/><br/>
<label for="last">Last Name: </label><br/>
<input type="text" name="last_name" id="last" value = "<?= htmlentities($row['last_name'])?>"><br/><br/>
<label for="email">Email: </label><br/>
<input type="text" name="email" id="email" value = "<?= htmlentities($row['email'])?>"><br/><br/>
<label for="headline">Headline: </label><br/>
<input type="text" name="headline" id="headline" value = "<?= htmlentities($row['headline'])?>"><br/><br/>
<label for="summary">Summary: </label><br/>
<textarea name="summary" rows="8" cols="80"><?= htmlentities($row['summary'])?></textarea><br/><br/>
<label for="addEdu">Education: </label>
<input type="submit" id="addEdu" value="+"><br/><br/>

<div id = "edu_fields">

<?php
$ranks = 1;
$lastFound = 0;

for($i=1; $i<=9; $i++) {
  $stmt = $pdo->prepare('SELECT year, institution_id FROM education WHERE profile_id = :profile_id AND rank = :rank');
  $stmt->execute(array( ':profile_id' => $_GET['profile_id'], ':rank' => $ranks));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if($row !== false){
    $stmt = $pdo->prepare('SELECT name FROM Institution WHERE institution_id = :institution_id');
    $stmt->execute(array( ':institution_id' => $row['institution_id']));
    $school = $stmt->fetch(PDO::FETCH_ASSOC);


    echo('<div id="edu'.$i.'">
    <p>Year: <input type="text" name="edu_year'.$i.'" value="'.htmlentities($row['year']).'" />
    <input type="button" value="-" onclick="$(\'#edu'.$i.'\').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school'.$i.'" class="school" value="'. htmlentities($school['name']) .'" />
    </p></div><br/><br/>');

    $lastFound = $ranks;
  }

  $ranks++;
}
?>

<script>
let countEdu = <?= $lastFound ?>;
</script>


</div>

<label for="addPos">Position: </label>
<input type="submit" id="addPos" value="+"><br/><br/>

<div id = "position_fields">

<?php
$ranks = 1;
$lastFound = 0;

for($i=1; $i<=9; $i++) {
  $stmt = $pdo->prepare('SELECT year, description FROM position WHERE profile_id = :profile_id AND rank = :rank');
  $stmt->execute(array( ':profile_id' => $_GET['profile_id'], ':rank' => $ranks));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if($row !== false){
    echo('<div id="position'.$i.'">
    <p>Year: <input type="text" name="year'.$i.'" value="'.htmlentities($row['year']).'" />
    <input type="button" value="-"
        onclick="$(\'#position'.$i.'\').remove();return false;"></p><br/>
    <textarea name="desc'.$i.'" rows="8" cols="80">'. htmlentities($row['description']) .'</textarea>
    </div><br/><br/>');
    $lastFound = $ranks;
  }

  $ranks++;
}
?>

<script>
let countPos = <?= $lastFound ?>;
</script>

</div>

<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<p><input type="submit" value="Save"/><input type="submit" value="Cancel" name = "cancel"/></p>
</form>
