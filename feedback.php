<?php

    // header file
    require('./templates/header.php');
    $received = false;

    if (isset($_POST['submit']))
    {
        $id = uniqid();
        $title = mysqli_escape_string($conn, $_POST['title']);
        $content = mysqli_escape_string($conn, $_POST['content']);
        $sql = "INSERT INTO feedback(id, title, content) VALUES('$id', '$title', '$content')";
        if (!mysqli_query($conn, $sql))
        {
            echo "Error: ".mysqli_error($conn);
        }
        else
        {
            $received = true;
        }
    }

?>

<div class="container my-5" ng-controller="reportProblemController">

    <div ng-hide="received" class="row">
        <h1 class="display-1 text-primary my-3">Feedback</h1>
        <form action="feedback.php" method="POST">
            <div class="mb-3 form-floating col-10">
                <input type="text" placeholder="Subject" name="title" id="title" maxlength="220" class="form-control" required>
                <label for="title">Subject</label>
            </div>
            <div class="mb-3 form-floating col-10">
                <textarea placeholder="Your feedback" name="content" class="form-control" id="content" maxlength="1000" style="height: 150px; resize: none;" required></textarea>
                <label for="content">Your feedback</label>
            </div>
            <div class="mb-3">
                <input type="submit" name="submit" value="Done" class="col-auto btn btn-lg btn-outline-success">
            </div>
            <small class="text-muted">Your feedback is anonymous</small>
        </form>
    </div>

    <div ng-show="received" class="my-5 py-5 row justify-content-center">
        <div class="display-4 col-auto text-primary my-5 py-5">Thank you for your valuable time!</div>
        <div class="col-12"></div>
        <p class="lead col-auto text-muted">We will look into your feedback soon.</p>
    </div>

</div>

<script>
    catglueQuiz.controller('reportProblemController', $scope => {
        $scope.received = <?php echo $received ? 'true' : 'false'; ?>;
    })
</script>


<?php

    // footer file
    require('./templates/footer.php');


?>