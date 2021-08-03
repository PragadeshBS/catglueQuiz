<?php
    require('./templates/header.php');

    if (!isset($_SESSION['user']))
    {
        // modify without signing in
        header("location: 403.php");
    }

    // question form post
    if (isset($_POST['submit']))
    {
        // update no of questions in quiz meta table
        $id = $_POST['id'];

        $title = mysqli_real_escape_string($conn, $_POST['title']);

        $description = mysqli_real_escape_string($conn, $_POST['description']);

        // parse category from angular expression
        $category = substr($_POST['category'], 7);

        $timer = substr($_POST['timer'], 7)*60;

        // parse num of questions from angular expression
        preg_match("/\d+$/", $_POST['question_nums'], $match);
        $question_nums = $match[0];
        
        $meta_update_sql = "UPDATE quizzes SET question_nums = '$question_nums', 
        category='$category', title='$title', description='$description', timer='$timer' WHERE id='$id'";

        // not updated
        if (!mysqli_query($conn, $meta_update_sql))
        {
            echo "Error ". mysqli_error($conn);
        }
        else
        {
            // add quiz content as JSON to db
            $content = mysqli_real_escape_string($conn, json_encode($_POST));

            $content_sql = "UPDATE quiz_contents SET content = '$content' WHERE id='$id'";

            if (!mysqli_query($conn, $content_sql))
            {
                echo "Error ". mysqli_error($conn);
            }
            else
            {
                // success, redirect to preview page
                header("Location: preview.php?id=$id");
            }
        }

    }

    // check page validity
    else if (isset($_GET['id']))
    {
        $id = $_GET['id'];

        $meta_sql = "SELECT * FROM quizzes WHERE id='$id'";
        $content_sql = "SELECT content FROM quiz_contents WHERE id='$id'";

        $meta_result = mysqli_query($conn, $meta_sql);
        $content_result = mysqli_query($conn, $content_sql);

        if (!mysqli_num_rows($meta_result))
        {
            // redirect requests with invalid id
            header("Location: 404.php");
        }
        else
        {
            $meta_arr = mysqli_fetch_assoc($meta_result);
            if ($meta_arr['created_by'] != $_SESSION['user'])
            {
                // access to quiz created by another user
                header("Location: 403.php");
            }
            else
            {
                // success
                $title = htmlspecialchars($meta_arr['title']);
                $description = htmlspecialchars($meta_arr['description']);
                $category = htmlspecialchars($meta_arr['category']);
                $timer = $meta_arr['timer'];

                $content_json = mysqli_fetch_assoc($content_result)['content'];
            }
        }
    }
    else
    {
        // redirect requests without id
        header("location: 404.php");
    }
    
?>
<div class="container bg-light rounded py-4 my-3 px-5" ng-controller="createQuestionsController">

    <h1 class="display-1 text-primary mb-3">Modify Quiz</h1>

    <form action="modify.php" method="POST" name="questionsCreateForm" novalidate>
 
        <div class="row justify-content-center">
            <!-- title field -->
            <div class="mb-3 col-12 row">
                <div class="input-group">

                    <!-- tooltip -->
                    <span class="input-group-text col-auto">
                        <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter a title for your quiz">
                        <i class="bi bi-card-heading"></i>
                        </span>
                    </span>

                    <div class="col-9 form-floating">
                        <input class="form-control rounded-0 rounded-end" placeholder="Title" 
                        type="text" name="title" id="title" ng-model="quiz.title" ng-required="true" ng-pattern="titleReg">
                        <label for="title">Title</label>
                    </div>

                </div>

                <!-- invalid title warning -->
                <div class="text-center ms-5 col-auto rounded-pill bg-danger text-light py-2 mt-1" 
                ng-show="questionsCreateForm.title.$touched && questionsCreateForm.title.$invalid">
                    Title must be 3-220 characters long without double quotes
                </div>
            </div>

            <!-- description field -->
            <div class="mb-3 col-12 row">
                <div class="input-group">
                    <!-- tooltip -->
                    <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter a short description for your quiz">
                                <i class="bi bi-text-paragraph"></i>
                            </span>
                    </span>
                    <div class="col-9 form-floating">
                        <textarea maxlength="500" class="form-control rounded-0 rounded-end" name="description"
                            id="description" placeholder="A short description of your quiz"
                            style="height: 100px; resize: none"
                            ng-model="quiz.description" ng-required="true" ng-pattern="descriptionReg">
                        </textarea>                        
                        <label for="description">Description</label>
                    </div>
                </div>

                <!-- invalid desc warning -->
                <div class="text-center ms-5 col-auto rounded-pill bg-danger text-light py-2 mt-1" 
                ng-show="questionsCreateForm.description.$touched && questionsCreateForm.description.$invalid">
                Description must be 3-500 characters long without double quotes
                </div>
            </div>

            <!-- category field -->
            <div class="mb-3 col-12 row justify-content-center">
                <div class="input-group">
                    <!-- tooltip -->
                    <span class="input-group-text col-auto">
                        <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Select the category of your quiz">
                        <i class="bi bi-tags"></i>
                        </span>
                    </span>
                    <div class="col-9">
                        <select name="category" class="form-select rounded-0 rounded-end" id="category" ng-required="true" ng-model="category">
                            <option value="" disabled>Category</option>
                            <option ng-repeat="option in categories" ng-value="option">{{option}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- timer field -->
            <div class="mb-3 col-12 row justify-content-center">
                <div class="input-group">
                    <!-- tooltip -->
                    <span class="input-group-text col-auto">
                        <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Select the time limit for your quiz">
                        <i class="bi bi-stopwatch"></i>
                        </span>
                    </span>
                    
                    <div class="col-9">
                        <select class="form-select rounded-0 rounded-end" name="timer" id="timer" ng-required="true" ng-model="timer">
                            <option value="" disabled>Timer (Mins)</option>    
                            <option ng-repeat="option in timings" ng-value="option">{{option}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- pass id in POST -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <!-- no of questions -->
            <div class="mb-3 col-12 row justify-content-center">

                <div class="input-group">
                    <!-- tooltip -->
                    <span class="input-group-text col-auto">
                        <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Select the number of questions in your quiz">
                        <i class="bi bi-list-ol"></i>
                        </span>
                    </span>
                    <div class="col-9">
                        <select class="form-select" ng-model="questionNo" name="question_nums">
                            <option value="" disabled>Number of questions</option>
                            <option ng-repeat="option in options" ng-value="option">{{option}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div ng-repeat="n in [] | range:1:questionNo" class="card row justify-content-center my-3 rounded">
            <div class="mb-4 card-body col-12 row justify-content-center">
                <div class="display-6 my-3 col-12 card-title">Question {{n}}</div>
                <div class="mb-4 form-floating col-12">
                    <input ng-pattern="questionStatementReg" class="form-control" type="text" 
                    placeholder="Question statement" id="question_statement_{{n}}" name="question_{{n}}" 
                    ng-model="questions[n]" ng-required="true">
                    <label class="ps-4" for="question_statement_{{n}}">Question statement</label>
                </div>

                <!-- invalid qs statement warning -->
                <div ng-show="!questions[n].match(questionStatementReg)" class="text-center ms-5 col-auto rounded-pill bg-danger text-light py-2 mb-3" >
                    Question statement must be 3-220 characters long without double quotes
                </div>

                <div class="card-text col-12 row justify-content-center">
                    <div class="col-12 row justify-content-center">
                        <div class="col-lg-6 mb-3 form-floating">
                            <input ng-pattern="optionReg" ng-required="true" placeholder="Option 1" 
                            class="form-control" type="text" ng-required="true" name="option_1_question_{{n}}" 
                            ng-model="optionValues[n][1]">
                            <label class="ps-4">Option 1</label>
                        </div>

                        <div class="col-lg-6 mb-3 form-floating">
                            <input ng-pattern="optionReg" ng-required="true" placeholder="Option 2" 
                            class="form-control" type="text" ng-required="true" name="option_2_question_{{n}}" 
                            ng-model="optionValues[n][2]">
                            <label class="ps-4">Option 2</label>
                        </div>

                    </div>
                    <div class="mb-4 col-12 row justify-content-center">
                        <div class="col-lg-6 mb-3 form-floating">
                            <input ng-pattern="optionReg" ng-required="true" placeholder="Option 3" class="form-control" 
                            type="text" ng-required="true" name="option_3_question_{{n}}" ng-model="optionValues[n][3]">
                            <label class="ps-4">Option 3</label>
                        </div>
                        <div class="col-lg-6 mb-3 form-floating">
                            <input ng-pattern="optionReg" ng-required="true" placeholder="Option 4" 
                            class="form-control" type="text" ng-required="true" name="option_4_question_{{n}}" 
                            ng-model="optionValues[n][4]">
                            <label class="ps-4">Option 4</label>
                        </div>
                    </div>

                    <!-- invalid option warning -->
                    <div class="col-auto text-center ms-5 rounded-pill bg-danger text-light py-2 mb-5"
                    ng-show="!optionValues[n][1].match(optionReg) || !optionValues[n][2].match(optionReg)
                    || !optionValues[n][3].match(optionReg) || !optionValues[n][4].match(optionReg)">
                        Option values must be 1-220 characters long without double quotes
                    </div>

                    <div class="col-12"></div>

                    <div class="col-auto">
                        <div class="input-group">
                             <!-- tooltip -->
                             <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Select the number of questions in your quiz">
                                <i class="bi bi-check2-circle"></i>
                                </span>
                            </span>

                            <div>
                                <select class="form-select rounded-0 rounded-end" name="right_option_{{n}}" ng-model="selectedOptionValues[n]" ng-required="true" id="answerSelect{{n}}">
                                    <option value="" disabled>Right answer</option>    
                                    <option ng-repeat="option in answerOptions" ng-value="option">{{option}}</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <button type="submit" name="submit" value="submit" ng-disabled="questionsCreateForm.$invalid" class="btn btn-lg btn-primary">Modify</button>
        </div>
        <!-- for testing disable button disable -->
        <!-- <button type="submit" name="submit" value="submit" class="btn btn-primary">Next</button> -->
    </form>

</div>

<script>
    catglueQuiz.controller('createQuestionsController', function($scope){

        $scope.titleReg = /^[^"]{3,220}$/;
        $scope.descriptionReg = /^[^"]{3,500}$/;

        $scope.questionStatementReg = /^[^"]{3,220}$/;
        $scope.optionReg = /^[^"]{1,220}$/;

        $scope.categories = ['Art and Literature', 'General Knowledge', 'Geography', 'Life and health', 'History', 'Politics', 'Music', 'Science and Nature', 'Sports', 
        'Technology', 'TV and Films', 'Others/Misc'];

        $scope.category = "<?php echo $category; ?>";

        //timer options
        $scope.timings = [1,3,5,10,20];

        $scope.timer = <?php echo $timer; ?>/60;

        $scope.quiz = {
            title: "<?php echo $title; ?>",
            description: "<?php echo $description; ?>"
        }


        // default no of qs
        $scope.questionNo = <?php echo $meta_arr['question_nums']?>;

        $scope.content_json = <?php echo $content_json; ?>;

        $scope.questions = {};
        $scope.optionValues = {};
        $scope.selectedOptionValues = {};

        for (let j =1; j <= $scope.questionNo; j++)
        {
            let questionLabel = "question_" + j.toString();
            $scope.questions[j] = $scope.content_json[questionLabel];

            $scope.optionValues[j] = {};
            for (let k = 1;k < 5;k++)
            {
                let optionsLabel = "option_" + k.toString() + "_question_" + j.toString();
                $scope.optionValues[j][k] = $scope.content_json[optionsLabel];
            }

            let rightOptionLabel = "right_option_" + j.toString();
            $scope.selectedOptionValues[j] = 'Option ' + $scope.content_json[rightOptionLabel].slice(14);

        }

        // number of questions 
        $scope.options = [3,4,5,6,7,8,9,10]

        // options
        $scope.answerOptions = ['Option 1', 'Option 2', 'Option 3', 'Option 4'];

    })
</script>

<?php
    require('./templates/footer.php');
?>