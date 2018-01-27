<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>SELFPORTAL</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/custom-style.css">
	<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src="/js/index.js"></script>

</head>

<body>
	<div class="login-page">
		<div class="form">

			<?php
      include("authenticate.php");


      // check to see if user is logging out
      if(isset($_GET['out'])) {
          // destroy session
          session_unset();
          $_SESSION = array();
          unset($_SESSION['user'],$_SESSION['access']);
          session_destroy();
      }

      // check to see if login form has been submitted
      if(isset($_POST['username'])){
		  echo authenticate($_POST['username'],$_POST['password']);
          // run information through authenticator
          if(authenticate($_POST['username'],$_POST['password']))
          {
              // authentication passed
              header("Location: /user/");
             # die();

          } else {
              // authentication failed
              $error = 1;
          }
      }

      ?>
				<?php require_once("config/config.php");
  if(isset($_SESSION['user'])) die(header("Location: /user/"));
    ?>

					<form class="login-form" method="post" action="index.php">
						<image src="img/logo.png" width="150"></image>
						<h3>SELFPORTAL</h3>
						<div>
							<span class="input-addon"><?php echo LDAP_USR_DOM; ?></span>
							<input type="text" name="username" placeholder="username" style="width: 55%; display: block;">
						</div>
						<input type="password" name="password" placeholder="password">
						<button>login</button>
						<br>
						<br>
						<font color="red">
							<?php
          // output error to user
if(isset($error)) echo "Login failed: Incorrect user name or password<br /-->";
          // output logout success
          if(isset($_GET['out'])) echo "Logout successful<br /-->";
?>
						</font>
					</form>
		</div>
	</div>
</body>

</html>
