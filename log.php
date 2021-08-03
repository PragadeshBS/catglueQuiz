<!-- head less page -->
<?php

    // header file
    require('./templates/header.php');

    // prevent direct page response
    if (empty($_POST))
    {
        header("location: 404.php");
    }

    $id = uniqid();
    $loc = $_POST['loc'];
    $uag = $_POST['uag'];

    $sql = "INSERT INTO site_logs(id, loc, uag) VALUES('$id', '$loc', '$uag')";

    mysqli_query($conn, $sql);

?>