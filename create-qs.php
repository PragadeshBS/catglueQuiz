<?php
    require('./templates/header.php');

    // question form post
    if (isset($_POST['submit']))
    {
        // update no of questions in quiz meta table
        $id = $_POST['id'];

        // parse num of questions from angular expression
        preg_match("/\d+$/", $_POST['question_nums'], $match);
        $question_nums = $match[0];
        
        $meta_update_sql = "UPDATE quizzes SET question_nums = '$question_nums' WHERE id='$id'";

        // not updated
        if (!mysqli_query($conn, $meta_update_sql))
        {
            echo "Error ". mysqli_error($conn);
        }
        else
        {
            // add quiz content as JSON to db
            $content = mysqli_real_escape_string($conn, json_encode($_POST));

            $content_sql = "INSERT INTO quiz_contents(id, content) VALUES('$id', '$content')";

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
        $sql = "SELECT * FROM quizzes WHERE id='$id'";
        $result = mysqli_query($conn, $sql);
        if (!mysqli_num_rows($result))
        {
            // redirect requests with invalid id
            header("Location: 404.php");
        }
        else
        {
            $arr = mysqli_fetch_assoc($result);
            if ($arr['created_by'] != $_SESSION['user'])
            {
                // access to quiz created by another user
                header("Location: 403.php");
            }
            else if ($arr['visibility'] != 0)
            {
                // accessing page after quiz is created
                header("location: 404.php");
            }
            else
            {
                // success
                $title = htmlspecialchars($arr['title']);
                $description = htmlspecialchars($arr['description']);
                $category = htmlspecialchars($arr['category']);
            }
        }
    }
    else
    {
        // redirect requests without id
        header("location: 404.php");
    }
    
?>
<div class="container bg-light rounded py-4 my-3" ng-controller="createQuestionsController">

    <div class="row justify-content-center">
        <div class="col-lg-10 row justify-content-center">
            <h1 class="text-center text-primary display-3">Create Quiz</h1>
            <h6 class="text-muted text-center">Step 2/3</h6>
            <!-- <hr style="width: 60%; margin: 10px auto"> -->
            <!-- title -->
            <div>
                <h2><span class="text-primary">Quiz title:</span> <?php echo $title; ?></h2>
            </div>
            <!-- description -->
            <div>
                <p><span class="text-primary">Description: </span><?php echo $description; ?></p>
            </div>
            <!-- category -->
            <div>
                <p><span class="text-primary">Category: </span><?php echo $category; ?></p>
            </div>

            <form action="create-qs.php" method="POST" name="questionsCreateForm" novalidate>
                <!-- pass id in POST -->
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            
                <div class="mb-4 col-12 row justify-content-center">
                    <div class="input-group">
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Select the number of questions in your quiz">
                            <i class="bi bi-list-ol"></i>
                            </span>
                        </span>
                        <div class="col-auto">

                            <!-- select with ng-options -->
                            <!-- <select class="form-select rounded-0 rounded-end" ng-model="questionNo" ng-options="option for option in options" name="question_nums"></select> -->

                            <select class="form-select rounded-0 rounded-end" ng-model="questionNo" name="question_nums">
                                <option value="" disabled selected>Number of questions</option>
                                <option ng-repeat = "option in options" ng-value="option">{{option}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div ng-repeat="n in [] | range:1:questionNo" class="card col-12 row justify-content-center my-3 rounded">
                    <div class="mb-4 card-body col-12 row justify-content-center">
                        <div class="display-6 my-3 col-12 card-title">Question {{n}}</div>
                        <div class="mb-4 form-floating col-12">
                            <input type="text" id="question_statement_{{n}}"
                            placeholder="Question statement" name="question_{{n}}" 
                            ng-model="question" ng-required="true" class="form-control"
                            ng-pattern="questionStatementReg">
                            <label class="ps-4" for="question_statement_{{n}}">Question statement</label>
                        </div>

                        <!-- invalid qs statement warning -->
                        <div ng-show="questionsCreateForm.question_{{n}}.$invalid && questionsCreateForm.question_{{n}}.$touched" class="text-center ms-5 col-auto rounded-pill bg-danger text-light py-2 mb-3" >
                            Question statement must be 3-220 characters long without double quotes
                        </div>

                        <div class="card-text col-12 row justify-content-center">
                            <div class="col-12 row justify-content-center">
                                <div class="col-lg-6 mb-3 form-floating">
                                    <input type="text" id="option_1_question_{{n}}" class="form-control" placeholder="Option 1" 
                                    ng-required="true" name="option_1_question_{{n}}" ng-model="option1" ng-pattern="optionReg">
                                    <label class="ps-4" for="option_1_question_{{n}}">Option 1</label>
                                </div>
                                <div class="col-lg-6 mb-3 form-floating">
                                    <input ng-pattern="optionReg" type="text" id="option_2_question_{{n}}" class="form-control" ng-required="true" placeholder="Option 2" name="option_2_question_{{n}}" ng-model="option2">
                                    <label for="option_2_question_{{n}}" class="ps-4">Option 2</label>
                                </div>
                            </div>
                            <div class="mb-4 col-12 row justify-content-center">
                                <div class="col-lg-6 mb-3 form-floating">
                                    <input ng-pattern="optionReg" type="text" id="option_3_question_{{n}}" class="form-control" placeholder="Option 3" ng-required="true" name="option_3_question_{{n}}" ng-model="option3">
                                    <label class="ps-4" for="option_3_question_{{n}}">Option 3</label>
                                </div>
                                <div class="col-lg-6 mb-3 form-floating">
                                    <input ng-pattern="optionReg" type="text" id="option_4_question_{{n}}" class="form-control" placeholder="Option 4" ng-required="true" name="option_4_question_{{n}}" ng-model="option4">
                                    <label for="option_4_question_{{n}}" class="ps-4">Option 4</label>
                                </div>
                            </div>

                            <!-- invalid option warning -->

                            <div ng-show="(questionsCreateForm.option_1_question_{{n}}.$invalid && questionsCreateForm.option_1_question_{{n}}.$touched) 
                                || (questionsCreateForm.option_2_question_{{n}}.$invalid && questionsCreateForm.option_2_question_{{n}}.$touched)
                                || (questionsCreateForm.option_3_question_{{n}}.$invalid && questionsCreateForm.option_3_question_{{n}}.$touched)
                                || (questionsCreateForm.option_4_question_{{n}}.$invalid && questionsCreateForm.option_4_question_{{n}}.$touched)
                            " class="col-auto text-center ms-5 rounded-pill bg-danger text-light py-2 mb-5">
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
                                        <select class="form-select rounded-0 rounded-end" name="right_option_{{n}}" id="answerSelect{{n}}"
                                        ng-required="true" ng-model="rightOptionValues[n]">
                                        <option value="" disabled selected>Right answer</option>
                                        <option ng-repeat="option in answerOptions" ng-value="option">{{option}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" name="submit" value="submit" ng-disabled="questionsCreateForm.$invalid || !questionNo" class="btn btn-lg btn-primary">Next</button>
                </div>

                <!-- disable button disable -->
                <!-- <button type="submit" name="submit" value="submit" class="btn btn-primary">Next</button> -->
            </form>
        </div>
    </div>

</div>

<script>
    catglueQuiz.controller('createQuestionsController', function($scope){

        var questionNo = 0;

        // question and option patterns
        $scope.questionStatementReg = /^[^"]{3,220}$/;
        $scope.optionReg = /^[^"]{1,220}$/;

        // default no of qs
        $scope.questionNo = 3;

        // number of questions 
        $scope.options = [3,4,5,6,7,8,9,10];

        $scope.rightOptionValues = {};

        // options
        $scope.answerOptions = ['Option 1', 'Option 2', 'Option 3', 'Option 4'];
    })
</script>

<?php
    require('./templates/footer.php');
?>