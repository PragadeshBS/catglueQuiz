<?php

require('./templates/header.php');

// redirect signed out users to login page
if (!isset($_SESSION['user']))
{
    header("location: login.php?flow=create");
}

$description_placeholder = "e.g. Think you know everything about technology? Take this quiz and prove it, hotshot.";

if (isset($_POST['initial_submit']))
{
    $title = mysqli_escape_string($conn, $_POST['title']);
    $description = mysqli_escape_string($conn, $_POST['description']);
    
    $category = substr($_POST['category'], 7);
    $category = mysqli_escape_string($conn, $category);

    $timer = substr($_POST['timer'], 7)*60;

    $title_terms = explode(' ', $title);

    $sql = "SELECT * FROM quizzes WHERE title LIKE '%";

    $sql .= implode("%' OR title LIKE '%", $title_terms)."%'";;

    $result = mysqli_query($conn, $sql);

    $arr_json = json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
}


if (isset($_POST['post_submit']))
{
    $id = uniqid();
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    $category = $_POST['category'];
    $category = $category;

    $timer = $_POST['timer'];
    $created_by = $_SESSION['user'];

    $sql = "INSERT INTO quizzes(id, title, description, category, timer, created_by) VALUES('$id', '$title', '$description', '$category', '$timer', '$created_by')";

    if (mysqli_query($conn, $sql))
    {
        header("Location: create-qs.php?id=$id");
    }
    else
    {
        echo mysqli_error($conn);
    }


}

?>
<div class="container bg-light rounded py-4 my-3" ng-controller="quizCreateController">
    
    <div class="text-center text-primary">
        <h1 class="display-3">Create Quiz</h1>
        <h6 class="text-muted">Step 1/3</h6>
    </div>

    
    <?php if (!isset($_POST['initial_submit'])): ?>
    <div class="row justify-content-center">
        <div class="col-sm-10 col-lg-6 row justify-content-center">

            <form action="create.php" method="POST" name="quizCreateForm" novalidate>

                <!-- title field -->
                <div class="mb-4 row justify-content-center">
            
                    <div class="input-group">
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter a title for your quiz">
                            <i class="bi bi-card-heading"></i>
                            </span>
                        </span>
                        <div class="form-floating col-10">
                            <input type="text" ng-required="true" ng-pattern="titleReg" ng-model="quiz.title" name="title" id="title"
                            placeholder="Quiz title" class="form-control rounded-0 rounded-end">
                            <label for="title">Quiz title</label>
                        </div>
                    </div>

                    <!-- invalid title warning -->
                    <div class="text-center rounded-pill bg-danger text-light col-7 py-2 border-1 mt-1" 
                    ng-show="quizCreateForm.title.$touched && quizCreateForm.title.$invalid">
                        Title must be 3-220 characters long and cannot contain double quotes
                    </div>

                </div>

                <div class="mb-4 row justify-content-center">

                    <div class="input-group">
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter a short description for your quiz">
                                <i class="bi bi-text-paragraph"></i>
                                </span>
                        </span>

                        <div class="form-floating col-10">
                            <textarea class="form-control rounded-0 rounded-end" name="description"
                            ng-pattern="descriptionReg" ng-required="true" ng-model="quiz.description"
                            id="description" placeholder="A short description of your quiz"
                            style="height: 100px; resize: none">
                            </textarea>
                            <label for="description">A short description</label>
                        </div>

                    </div>

                    <div class="text-center rounded-pill bg-danger text-light col-9 py-2 border-1 mt-1" 
                    ng-show="quizCreateForm.description.$touched && quizCreateForm.description.$invalid">
                        Description can be 3-500 characters long and cannot contain double quotes
                    </div>

                </div>

                <div class="mb-4 row justify-content-center">

                    <div class="input-group">
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter the category of your quiz">
                            <i class="bi bi-tags"></i>
                            </span>
                        </span>
                        <div class="col-10">
                            <select name="category" id="category" class="form-select rounded-0 rounded-end" aria-label="Default select example"
                            ng-required="true" ng-model="quiz.category">
                            <option value="" selected disabled>Category</option>
                            <option ng-repeat="option in categories" ng-value="option">{{option}}</option>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- timer field -->
                <div class="mb-4 row justify-content-center">

                    <div class="input-group">
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter an email that you can use to login">
                            <i class="bi bi-stopwatch"></i>
                            </span>
                        </span>

                        <div class="col-10">
                            <select name="timer" id="timer" ng-required="true" ng-model="quiz.timer" class="form-select rounded-0 rounded-end">
                                <option value="" disabled selected>Timer (Mins)</option>
                                <option ng-repeat = "option in timings" ng-value="option">{{option}}</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="text-muted text-center small col-10">
                    Created quizzes remain private by default. You can make it public once you are done creating the quiz or anytime later.
                </div>

                <div class="my-3 text-center">
                    <input type="submit" class="btn btn-primary" name="initial_submit" value="Next" ng-disabled="quizCreateForm.$invalid">
                </div>

            </form>
            
        </div>

    </div>

    <?php endif; ?>

    <?php if (isset($_POST['initial_submit'])): ?>

        <div class="container row justify-content-center">
            <div ng-show="similarQuizzesJson.length > 1" class="lead">
                The following quizzes look similar to the one you want to create. Do you wish to proceed?
            </div>
            
            <div ng-show="similarQuizzesJson.length == 1" class="lead">
                The following quiz looks similar to the one you want to create. Do you wish to proceed?
            </div>
            <small class="text-muted">This is done to prevent duplicate quizzes</small>
            <div class="my-3">
                <form action="create.php" method="POST">
                    <input type="hidden" name="title" value="<?php echo $title; ?>">
                    <input type="hidden" name="description" value="<?php echo $description; ?>">
                    <input type="hidden" name="category" value="<?php echo $category; ?>">
                    <input type="hidden" name="timer" value="<?php echo $timer; ?>">
                    <button class="btn btn-success" type="submit" id="postSubmitBtn" name="post_submit" value="submit">Proceed to create quiz</button>
                </form>
            </div>
            <div>
                <div ng-repeat = "quiz in similarQuizzesJson" class="card w-50 my-3">
                    <!-- card links to quiz page -->
                    <a href="quiz.php?id={{quiz.id}}" class="text-decoration-none text-reset">
                        <div class="card-body">
                            <!-- quiz title -->
                            <div class="card-title h5">
                                <span class="display-6">{{quiz.title}}</span>
                            </div>
                            <!-- quiz category -->
                            <div>
                                <p>Category: {{ quiz.category }}</p>
                            </div>
                            <!-- quiz description -->
                            <blockquote class="blockquote">
                                <p>{{ quiz.description }}</p>
                                <!-- question no and timer -->
                                <footer class="blockquote-footer mt-3" ng-show="quiz.timer/60 != 1"> {{quiz.question_nums}} questions, {{quiz.timer/60}} mins</footer>
                                <footer class="blockquote-footer mt-3" ng-hide="quiz.timer/60 != 1"> {{quiz.question_nums}} questions, {{quiz.timer/60}} min</footer>
                            </blockquote>
                        </div>
                        <!-- created username -->
                        <div class="card-footer text-muted">
                            Created by {{ quiz.created_by }}
                        </div>
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>



<script>
catglueQuiz.controller('quizCreateController', function($scope){
    $scope.titleReg = /^[^"]{3,220}$/;
    $scope.descriptionReg = /^[^"]{3,500}$/;
    $scope.questionStatementReg = /^[^"]{3,500}$/;
    
    $scope.categories = ['Art and Literature', 'General Knowledge', 'Geography', 'Life and health', 'History', 'Politics', 'Music', 'Science and Nature', 'Sports', 
        'Technology', 'TV and Films', 'Others/Misc'];
    $scope.timings = [1,3,5,10,20];

    $scope.quiz = {category: $scope.categories[0], timer: $scope.timings[1]};
    
    <?php if (isset($_POST['initial_submit'])): ?>

        $scope.similarQuizzesJson = <?php echo $arr_json; ?>;

        if ($scope.similarQuizzesJson.length == 0)
        {
            // simulate post form submit
            document.getElementById('postSubmitBtn').click();
        }

    <?php endif; ?>
    


})
</script>






<?php

require('./templates/footer.php')

?>