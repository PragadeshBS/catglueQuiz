<!-- post only page -->

<?php

    // header file
    require('./templates/header.php');

    $score = 3;
    
    $quiz_id = "60fad4cfb3467";

    // to check max score possible
    $quiz_sql = "SELECT question_nums FROM quizzes WHERE id='$quiz_id'";

    $quiz_res = mysqli_query($conn, $quiz_sql);
    $max_score = mysqli_fetch_row($quiz_res)[0];

?>

<div class="container py-5 my-5" ng-controller="scoreController">
    
    <!-- score 0 -->
    <div ng-show="score == 0" class="display-6 my-5">
        <div class="display-6 text-primary my-3">Your score was 0.</div>
        <div class="display-3">Don't fret, that was a good try <i class='bi bi-emoji-smile-upside-down'></i></div>
    </div>
    
    <!-- score < 70 -->
    <div ng-show="percent <= 70">
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