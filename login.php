<?php

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

require_once("pdo.php");

$salt = 'XyZzy12*_';
$check = '';

session_start();

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION["failure"] = "Email and password are required";
    } elseif(strrpos($_POST['email'], '@') == FALSE){
      $_SESSION["failure"] = "Email must include at-sign (@)";
    } else {
        $check = hash('md5', $salt.$_POST['pass']);
        $check = hash('md5', $salt.$_POST['pass']);
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
        $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( $row !== false ) {
          $_SESSION['name'] = $row['name'];
          $_SESSION['user_id'] = $row['user_id'];
          error_log("Login success ".$_SESSION['who']);
          header("Location: view.php");
          return;
        } else {
            $_SESSION["failure"] = "Incorrect password";
        }
    }
    error_log("Login fail ".$_SESSION['who']." $check");
    header("Location: login.php");
    return;
}
?>

<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>

<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Maaz Makrod's Login Page</title>
</head>
<body>
<div class="container">
<h1>Please Log In</h1>
<?php

if (isset($_SESSION["failure"])) {
    echo('<p style="color: red;">'.htmlentities($_SESSION["failure"])."</p>\n");
    unset($_SESSION["failure"]);
}
?>
<form method="POST">
<label for="nam">User Name</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
For a password hint, view source and find a password hint
in the HTML comments.
</p>
</div>
</body>
