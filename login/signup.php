<?php

require '../proses/functions.php';

if (isset($_POST["signup"])) {
  if (signup($_POST) > 0) {
    echo "<script>alert('User baru telah di tambahkan, silahkan klik Sign In');</script>";
  } else {
    echo mysqli_error($conn);
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign-Up</title>
  <link rel="stylesheet" href="signup.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,300,0,0" />
</head>

<body>
  <div class="login-card-container">
    <div class="login-card">
      <div class="login-card-logo">
        <img src="coffee-beans.png" alt="logo" />
      </div>
      <div class="login-card-header">
        <h1>Sign Up</h1>
        <div>Please Sign Up to use platform</div>
      </div>
      <form class="login-card-form" action="" method="post">
        <!-- Username -->
        <div class="form-item">
          <label class="form-item-icon material-symbols-rounded">person</label>
          <input type="text" placeholder="Username" name="username" required autofocus />
        </div>
        <!-- Email -->
        <div class="form-item">
          <label class="form-item-icon material-symbols-rounded">mail</label>
          <input type="text" placeholder="Email" name="email" required autofocus />
        </div>
        <!-- Password -->
        <div class="form-item">
          <label class="form-item-icon material-symbols-rounded">lock</label>
          <input type="password" placeholder="Password" name="password" required />
        </div>
        <!-- Re-Password -->
        <div class="form-item">
          <label class="form-item-icon material-symbols-rounded">lock</label>
          <input type="password" placeholder="Re-Password" name="password2" required />
        </div>
        <!-- Sign Up -->
        <button type="submit" name="signup">
          Sign Up
        </button>
      </form>
      <div class="login-card-footer">
        Have an account? <a href="login.php">Sign In</a>
      </div>
      <div class="back-dusk-station">
        Back to <a href="../index.php">Home Page</a>
      </div>
    </div>
    <div class="login-card-social">
      <div>other Sign-in Platform</div>
      <div class="login-card-social-btns">
        <a href="#">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-facebook" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"></path>
          </svg>
        </a>
        <a href="#">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-google" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <path d="M17.788 5.108a9 9 0 1 0 3.212 6.892h-8"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</body>

</html>