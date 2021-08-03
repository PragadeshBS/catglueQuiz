<?php

    // header file
    require('./templates/header.php');
    $reported = false;

    if (isset($_POST['submit']))
    {
        $id = uniqid();
        $title = mysqli_escape_string($conn, $_POST['title']);
        $description = mysqli_escape_string($conn, $_POST['description']);
        $sql = "INSERT INTO reported_problems(id, prob_title, prob_description, status) VALUES('$id', '$title', '$description', 1)";
        if (!mysqli_query($conn, $sql))
        {
            echo "Error: ".mysqli_error($conn);
        }
        else
        {
            $reported = true;
        }
    }

?>

<div class="container mb-3 py-5" ng-controller="reportProblemController">

    <div ng-hide="reported">
        <h1 class="display-1 text-primary mb-3">Report a Problem</h1>
        <form action="report-problem.php" method="POST">
            <div class="mb-3 form-floating">
                <input type="text" placeholder="What is the problem about?" maxlength="220" name="title" id="title" class="form-control" required>
                <label for="title">What is the problem about?</label>
            </div>
            <div class="mb-3 form-floating">
                <textarea placeholder="Describe the problem briefly" name="description" class="form-control" id="description" maxlength="1000" style="height: 150px; resize: none;" required></textarea>
                <label for="description">Describe the problem briefly</label>
            </div>
            <div class="mb-3">
                <input type="submit" name="submit" value="Report" class="btn btn-lg btn-outline-danger">
            </div>
            <small class="text-muted">Problems are reported anonymously</small>
        </form>
    </div>

    <div ng-show="reported" class="my-5 py-5 row justify-content-center">
        <h6 class="display-4 col-auto text-primary my-5">Your problem was reported!</h6>
        <div class="col-12"></div>
        <p class="lead col-auto text-muted">We will look into it soon</p>
    </div>

</div>

<script>
    catglueQuiz.controller('reportProblemController', $scope => {
        $scope.reported = <?php echo $reported ? 'true' : 'false'; ?>;
    })
</script>


<?php

    // footer file
    require('./templates/footer.php');


?>