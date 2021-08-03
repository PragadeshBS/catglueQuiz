<?php
    session_start();

    if (!isset($_SESSION['token']))
    {
      $_SESSION['token'] = bin2hex(random_bytes(32));
    }

    // connect to db
    $conn = mysqli_connect('localhost', 'forcol', 'test1234', 'catglue_quiz');

    if(!$conn)
    {
        die("Connection error");
    }
?>
<!DOCTYPE html>
<html lang="en" ng-app="catglueQuiz">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- angularjs -->

    <!-- enable in production -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>

    <!-- for dev -->
    <!-- <script src="./config/angular.min.js"></script> -->

    <!-- Bootstrap CSS -->
    <link href="./css/main.min.css" rel="stylesheet">

    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <!-- local script -->
    <script src="./config/app.js"></script>

    <!-- custom CSS -->
    <style>
      html{
        min-height: 100%;
      }
      .cursor-pointer{
        cursor: pointer;
      }
    </style>

    <title>catglue Quiz</title>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-light bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><span class="display-6 text-light">catglue Quiz</span></a>
    <button class="navbar-toggler bg-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <!-- <a class="nav-link active" aria-current="page" href="login.php">Login</a> -->
        </li>
        <li class="nav-item">
          <!-- <a class="nav-link" href="create.php">Create</a> -->
        </li>
        <li class="nav-item dropdown">
          <!-- <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            More
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item" href="signup.php">Sign Up</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul> -->
        </li>
      </ul>

      <!-- <div class="me-3"><?php //echo isset($_SESSION['user']) ? "<a href='profile.php'>".htmlspecialchars($_SESSION['user'])."</a>" : "not logged in"; ?></div> -->

      <!-- search quizzes only on index page -->
      <?php if (substr_count($_SERVER['PHP_SELF'], 'index.php') > 0): ?>

        <!-- search box -->
        <form class="d-flex">
          <input class="form-control me-2 py-2 border-altlight border-2" ng-model="searchTerm" type="search" placeholder="Search quizzes" aria-label="Search">
          <!-- <button class="btn btn-outline-success" type="submit">Search</button> -->
        </form>

      <?php endif; ?>

      <?php if (isset($_SESSION['user'])): ?>
        <?php if ($_SESSION['user'] != "admin"): ?>
          <button class="mx-3 btn btn-sm btn-altlight my-3"><a class="nav-link text-dark" href="create.php">Create Quiz</a></button>
        <?php endif; ?>
        <div class="dropdown">
          <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($_SESSION['user']); ?>
          </a>
          <ul class="dropdown-menu bg-secondary" aria-labelledby="navbarDropdown">            
            <?php if ($_SESSION['user'] == "admin"): ?>
              <li><a class="dropdown-item" href="admin.php">Manage</a></li>
              <li><hr class="dropdown-divider"></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </div>

      <?php else: ?>

        <!-- links to show once logged in -->
        <button class="mx-2 btn btn-sm border-altlight my-3">
          <a class="nav-link text-altlight" aria-current="page" href="login.php">Login</a>
        </button>

        <button class="mx-2 btn btn-sm btn-altlight">
          <a class="nav-link text-dark" aria-current="page" href="signup.php">Sign Up</a>
        </button>

      <?php endif; ?>

    </div>
  </div>
</nav>