<!-- headless page -->

<?php

// start and clear session

session_start();
session_unset();
// session_destroy();

// restore logged status
$_SESSION['logged'] = true;


// redirect to homepage

header('location: index.php')

?>
