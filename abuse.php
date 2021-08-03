<?php

    require('./templates/header.php');

    if (isset($_POST['submit']))
    {
        if (hash_equals($_SESSION['token'], $_POST['token']))
            {
                $quiz_id = $_POST['id'];
                $sql = "UPDATE quizzes SET visibility = 3 WHERE id='$quiz_id'";

                if (!mysqli_query($conn, $sql))
                {   
                    echo "Error: ".mysqli_error($conn);
                }

                // change token to avoid reloads or cross site abuse reports
                $_SESSION['token'] = bin2hex(random_bytes(32));
                
            }
            else
            {
                // possible csrf or user reloading the page
                header("location: 404.php");
            }
    }
    else
    {
        header("location: 404.php");
    }
?>

<div class="container my-5 py-5">
    <div class="display-5 py-3 text-primary">Your report has been made note of anonymously</div>
    <p class="lead my-5 text-muted">The quiz has been taken down for admin verification</p>
</div>


<?php
    require('./templates/footer.php');

?>