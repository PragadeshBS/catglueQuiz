<?php

    // header file
    require('./templates/header.php');

    if (!isset($_SESSION['user']))
    {
        header("location: login.php");
    }
    $user = $_SESSION['user'];

    function get_created_quizzes()
    {
        global $user, $conn;

        $sql = "SELECT * FROM quizzes WHERE created_by = '$user'";
        $result = mysqli_query($conn, $sql);
        return json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
    }


    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = mysqli_query($conn, $sql);
    $arr = mysqli_fetch_assoc($result);
    $created_quiz_ids = json_decode($arr['created_quiz_ids']);
    if (!empty($created_quiz_ids))
    {
        $created_quizzes_json = get_created_quizzes();
    }
    else
    {
        $created_quizzes_json = "[]";
    }
    $username = $arr['username'];
    $email = $arr['email'];
    $points = $arr['points'];

?>

<div class="container" ng-controller="profileController">

    <!-- delete quiz confirmation Modal -->
    <div class="modal fade" id="deleteQuizConfirmationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Delete quiz?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            This will permanently remove the quiz from the site. Do you want to proceed?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go back</button>
            <button ng-click="deleteQuiz()" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Delete</button>
        </div>
        </div>
    </div>
    </div>

    <div>
        <!-- username and email -->
        <h1 class="display-1 text-primary my-3"><?php echo htmlspecialchars($username); ?></h1>
        <h3 class="my-2"><?php echo htmlspecialchars($email); ?></h3>
        <h5 class="my-2 alert alert-altlight w-25">Points: <?php echo $points; ?></h5>
        <p class="text-muted small my-3">Attend / create more quizzes to increase your score</p>
    </div>
    <div>
        <h1 class="display-4 mt-5">Your quizzes</h1>
        <div ng-hide="createdQuizzes.length">
            <p class="lead my-3">Quizzes that you create will appear here</p>
        </div>
        <div ng-show="createdQuizzes.length">
            <div ng-repeat="quiz in createdQuizzes" class="card w-75 my-3">
                <div class="card-body">
                    <div class="card-title h5">
                        <span class="display-6"><a class="text-reset text-decoration-none" href="preview.php?id={{quiz.id}}">{{quiz.title}}</span>
                        <span ng-show="quiz.visibility==1" class="badge bg-success mx-1">Public</span>
                        <span ng-show="quiz.visibility==2" class="badge bg-warning mx-1">Private</span></a>
                        <span ng-show="quiz.visibility==3" class="badge bg-danger mx-1">Reported</span></a>
                        <span class="badge bg-primary mx-1" ng-hide="quiz.likes==1">{{quiz.likes}} Likes</span>
                        <span class="badge bg-primary mx-1" ng-show="quiz.likes==1">{{quiz.likes}} Like</span>
                    </div>
                    <blockquote class="blockquote">
                        <p><span class="h4">Category</span>: {{quiz.category}}</p>
                        <p>{{ quiz.description }}</p>
                        <footer class="blockquote-footer mt-3"> {{quiz.question_nums}} questions, {{quiz.timer/60}} mins</footer>
                    </blockquote>
                    <a href="modify.php?id={{quiz.id}}" class="btn btn-altlight">Modify Quiz</a>
                    <button ng-click="showDeleteQuizConfirmationModal(quiz.id);" class="mx-3 btn btn-sm btn-outline-danger">Delete Quiz</button>
                </div>
                <div class="card-footer text-muted">
                    {{ quiz.time }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // id of quiz to be deleted
    let deleteId = "";

    catglueQuiz.controller('profileController', $scope => {
        $scope.createdQuizzes = <?php echo $created_quizzes_json; ?>;
        for (quiz of $scope.createdQuizzes)
        {
            // Split mysql timestamp into [ Y, M, D, h, m, s ]
            const t = quiz.created_at.split(/[- :]/);

            // Apply each element to the Date function
            const d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

            let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
            let mo = new Intl.DateTimeFormat('en', { month: 'short' }).format(d);
            let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);
            quiz.time = `${da} ${mo}, ${ye}`;
        }

        $scope.showDeleteQuizConfirmationModal = (id) => {
            // set id to be deleted
            deleteId = id;
            deleteQuizConfirmationModal.show();
        }

        // delete quiz fn
        $scope.deleteQuiz = () => {
            $.ajax({
                type: "POST",
                url: "data-modify.php",
                data: { id: deleteId, action: "remove", token: "<?php echo $_SESSION['token']; ?>"}
            }).done(function( msg ) {
                $scope.$apply(function(){
                    $scope.createdQuizzes = $scope.createdQuizzes.filter(quiz => quiz.id != deleteId);
                });
            });
        }
    })
</script>

<?php

    // footer file
    require('./templates/footer.php');

?>