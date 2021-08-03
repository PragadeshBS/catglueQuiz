<!-- head less page -->
<?php

    // header file
    require('./templates/header.php');

    // prevent direct page response
    if (empty($_POST))
    {
        header("location: 404.php");
    }

    // id of the quiz to like/dislike
    $quiz_id = $_POST['id'];

    // to like or dislike
    $action = $_POST['action'];

    // updated list of quiz ids that the user has liked
    $liked_ids_json = $_POST['liked_ids_json'];
    
    // current username
    $user = $_POST['user'];

    if ($action == "like")
    {
        // increment like
        $quiz_sql = "UPDATE quizzes SET likes = likes + 1 WHERE id = '$quiz_id'";
    }
    else if ($action == "dislike")
    {
        // decrement like
        $quiz_sql = "UPDATE quizzes SET likes = likes - 1 WHERE id = '$quiz_id'";
    }

    // update quiz ids list that user likes
    $user_sql = "UPDATE users SET liked_quiz_ids = '$liked_ids_json' WHERE username='$user'";


    mysqli_query($conn, $quiz_sql);
    mysqli_query($conn, $user_sql);

?>