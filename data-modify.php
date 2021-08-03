<!-- head less page -->

<?php

    // header file
    require('./templates/header.php');

    // prevent direct page response
    if (empty($_POST))
    {
        header("location: 404.php");
    }

    $token = $_POST['token'];

    if ($token != $_SESSION['token'])
    {
        // possible csrf
        header("location: 403.php");
    }
    
    // type of change to be done
    $action = $_POST['action'];

    // id of post to modify
    $id = $_POST['id'];

    // reported quizzes modification
    if ($action == "approve" || $action == "remove")
    {
        
        if ($action == "approve")
        {
            // set quiz visibility to public        
            $sql = "UPDATE quizzes SET visibility = 1 WHERE id='$id'";
            mysqli_query($conn, $sql);
        }
        else
        {            
            // delete meta data
            $sql = "DELETE FROM quizzes WHERE id='$id'";
            mysqli_query($conn, $sql);
            
            // delete content
            $sql = "DELETE FROM quiz_contents WHERE id='$id'";
            mysqli_query($conn, $sql);
        }
    }

    // feedback modification
    if ($action == "read_feedback")
    {
        // mark feedback as read
        $sql = "UPDATE feedback SET feedback_read = 1 WHERE id = '$id'";
        mysqli_query($conn, $sql);
    }

    // solved a problem in site
    if ($action == "solved_problem")
    {
        // mark feedback as read
        $sql = "UPDATE reported_problems SET status = 0 WHERE id = '$id'";
        mysqli_query($conn, $sql);
    }

?>