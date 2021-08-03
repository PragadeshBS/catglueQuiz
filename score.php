<!-- post only page -->

<?php

    // header file
    require('./templates/header.php');
    
    if (isset($_POST['submit']))
    {
        if (isset($_POST['token']))
        {
            if (hash_equals($_SESSION['token'], $_POST['token']))
            {
                $user = $_SESSION['user'];
                $score = $_POST['score'];
                $quiz_id = $_POST['id'];

                // to check max score possible
                $quiz_sql = "SELECT question_nums FROM quizzes WHERE id='$quiz_id'";
                $quiz_res = mysqli_query($conn, $quiz_sql);
                $max_score = mysqli_fetch_row($quiz_res)[0];

                $get_sql = "SELECT points FROM users WHERE username='$user'";
                $result = mysqli_query($conn, $get_sql);
                $points = mysqli_fetch_row($result)[0];

                if ($score > 0)
                {   
                    // add score to users table
                    $sql = "UPDATE users SET points = points + $score WHERE username='$user'";
                    if (!mysqli_query($conn, $sql))
                    {
                        echo "Error: ".mysqli_error($conn);
                    }
                }

                // change token to avoid reloads incrementing the score repeatedly
                $_SESSION['token'] = bin2hex(random_bytes(32));
                
            }
            else
            {
                // possible csrf or user reloading the page
                header("location: 404.php");
            }
        }
    }
    // requests without post data
    else
    {
        header("location: 404.php");
    }

?>

<div class="container py-5 my-5" ng-controller="scoreController">
    
    <!-- score 0 -->
    <div ng-show="score == 0" class="display-6 my-5">
        <div class="display-6 text-primary my-3">Your score was 0.</div>
        <div class="display-3">Don't fret, that was a good try <i class='bi bi-emoji-smile-upside-down'></i></div>
    </div>
    
    <!-- score < 70 -->
    <div ng-show="percent <= 70 && score != 0">
        <div class="display-6 mb-5">Cool! Your score was,</div>
        <div class="text-center display-1 text-primary my-3">
            {{ percent }}%
            <small class="h3">({{score}}/{{maxScore}})</small>
        </div>
    </div>
   
    <!-- score 70-99 -->
    <div ng-show="percent > 70 && percent != 100">
        <div class="display-6 mb-5">That's awesome! Your score was,</div>
        <div class="text-center display-1 text-primary my-3">
            {{ percent }}%
            <small class="h3">({{score}}/{{maxScore}})</small>
        </div>
    </div>
    
    <!-- score 100 -->
    <div ng-show="percent == 100">
        <div class="display-6 mb-5">Absolutely perfect! Your score was,</div>
        <div class="text-center display-1 text-primary my-3">
            100%
            <small class="h3">({{score}}/{{maxScore}})</small>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- <button class="col-auto btn btn-lg btn-outline-primary mt-5 mx-3" onclick="window.location='index.php'">Go home</button> -->
        <button class="col-auto btn btn-lg btn-primary mt-5 mx-3" onclick="window.location='quiz.php?id=<?php echo $quiz_id ?>'">Try quiz again</button>
    </div>

</div>

<script>
    catglueQuiz.controller('scoreController', $scope => {
        $scope.score = <?php echo $score; ?>;
        $scope.maxScore = <?php echo $max_score; ?>;
        $scope.percent = ($scope.score / $scope.maxScore) * 100;
        $scope.percent = $scope.percent.toFixed(2);
    })
</script>

<?php
    require('./templates/footer.php');
?>