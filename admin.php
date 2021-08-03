<?php

    // header file
    require('./templates/header.php');

    if (!isset($_SESSION['user']))
    {
        header("location: 403.php");
    }
    else
    {
        // check if user is admin
        $user = $_SESSION['user'];
        $token = $_SESSION['token'];
        $is_admin_sql = "SELECT is_admin FROM users WHERE username='$user'";
        $is_admin_result = mysqli_query($conn, $is_admin_sql);
        $is_admin_val = mysqli_fetch_row($is_admin_result)[0];
        if ($is_admin_val == 0)
        {
            header("location: 403.php");
        }
    }
    // select reported quizzes
    $quiz_sql = "SELECT * FROM quizzes WHERE visibility = 3";
    $quiz_result = mysqli_query($conn, $quiz_sql);
    $quizzes = json_encode(mysqli_fetch_all($quiz_result, MYSQLI_ASSOC));

    // select reported problems that are not yet solved
    $problems_sql = "SELECT * FROM reported_problems WHERE status = 1";
    $problems_result = mysqli_query($conn, $problems_sql);
    $problems = json_encode(mysqli_fetch_all($problems_result, MYSQLI_ASSOC));
   
    // select feedback
    $feedback_sql = "SELECT * FROM feedback";
    $feedback_result = mysqli_query($conn, $feedback_sql);
    $feedback = json_encode(mysqli_fetch_all($feedback_result, MYSQLI_ASSOC));

    // select analytics
    $analytics_sql = "SELECT * FROM site_logs";
    $analytics_res = mysqli_query($conn, $analytics_sql);
    $analytics = json_encode(mysqli_fetch_all($analytics_res, MYSQLI_ASSOC));

?>

<div class="container py-3 my-3" ng-controller="adminController">
    <div>
        <h1 class="display-4 text-primary my-3">Reported quizzes</h1>
        <div ng-hide="reportedQuizzes.length" class="my-3 row">
            <p class="lead alert alert-success col-auto">All good! No quizzes have been reported for abuse</p>
        </div>
        <div ng-show="reportedQuizzes.length">
            <div ng-repeat="quiz in reportedQuizzes" class="card w-50 my-3">
                <div class="card-body">
                    <div class="card-title h5">
                        <span class="display-6"><a class="text-reset text-decoration-none" href="preview.php?id={{quiz.id}}">{{quiz.title}}</span>
                        <span ng-show="quiz.visibility==1" class="badge bg-success mx-1">Public</span>
                        <span ng-show="quiz.visibility==2" class="badge bg-warning mx-1">Private</span></a>
                    </div>
                    <blockquote class="blockquote">
                        <p><span class="h4">Category</span>: {{quiz.category}}</p>
                        <p>{{ quiz.description }}</p>
                        <footer class="blockquote-footer mt-3"> {{quiz.question_nums}} questions, {{quiz.timer/60}} mins</footer>
                    </blockquote>
                    <button ng-click="approve($index)" class="btn btn-success mx-3 my-3">Approve and make quiz public</button>
                    <button ng-click="remove($index)" class="btn btn-sm btn btn-outline-danger">Remove quiz permanently</button>
                </div>
                <div class="card-footer text-muted">
                    Created by {{ quiz.created_by }}
                </div>
            </div>
        </div>
    </div>

    <!-- reported problems -->
    <div>
        <h1 class="display-4 text-primary mt-5 mb-3">Reported Problems</h1>
        <div ng-hide="problems.length" class="my-3 row">
            <p class="lead alert alert-success col-auto">No problems have been reported in the site</p>
        </div>
        <div ng-show="problems.length">
            <div ng-repeat="problem in problems" class="card w-50 my-3">
                <div class="card-body">
                    <div class="card-title h5">
                        <span class="display-6">{{ problem.prob_title }}</span>
                    </div>
                    <p class="mt-3">{{ problem.prob_description }}</p>
                    <div ng-show="problem.status == 1">
                        <button ng-click="solved($index)" class="btn btn-outline-success my-3">Mark as solved</button>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    Posted {{ problem.created_at }}
                </div>
            </div>
        </div>
    </div>

    <!-- feedback -->
    <div>
        <h1 class="display-4 text-primary mt-5 mb-3">Feedback</h1>
        <div ng-show="feedbacks.length">
            <div>
                <input type="checkbox" id="show-only-unread" ng-model="showOnlyUnreadFeedback">
                <label for="show-only-unread" class="text-muted">Show only unread feedback</label>
            </div>
            <div ng-repeat="feedback in feedbacks">
                <div ng-hide="feedback.feedback_read == 1 && showOnlyUnreadFeedback" class="card w-50 my-3">
                    <div class="card-body">
                        <div class="card-title h5">
                            <span class="display-6">{{ feedback.title }}</span>
                            <span ng-show="feedback.feedback_read == 0" class="badge bg-success mx-1">New</span>
                        </div>
                        <blockquote class="blockquote">
                            <p>{{ feedback.content }}</p>
                        </blockquote>
                        <div ng-show="feedback.feedback_read == 0">
                            <button ng-click="read($index)" class="btn btn-outline-primary my-3">Mark as read</button>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        Posted {{ feedback.created_at }}
                    </div>
                </div>
            </div>
        </div>
        <div ng-show="feedbacks.length==0 || (showOnlyUnreadFeedback && unreadFeedbacks.length==0)">
            <p class="lead my-3">No feedback content to show</p>
        </div>
    </div>

    <!-- analytics -->
    <div>
        <h1 class="display-4 text-primary mt-5 mb-3">Site analytics</h1>
        <div class="my-3 row">
            <p class="lead col-auto">Total site visits: {{analytics.length}}</p>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <!-- <th scope="col">Id</th> -->
                    <th scope="col">User agent</th>
                    <th scope="col">Country</th>
                    <th scope="col">Time</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="data in analytics | startFrom:currentPage*pageSize | limitTo:pageSize">
                    <th scope="col">{{data.index}}</th>
                    <td>{{data.uag}}</td>
                    <td>{{data.loc}}</td>
                    <td>{{data.logged_at}}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- pagination -->
        <div class="row justify-content-center align-items-center" ng-hide="analytics.length/pageSize <= 1">
            <div class="col-auto">
                <button class="btn btn-primary" ng-disabled="currentPage == 0" ng-click="currentPage=currentPage-1">Previous</button>
            </div>
            <div class="col-auto">
                <div class="lead">Page {{currentPage+1}} of {{numberOfPages()}}</div>
            </div>
            <div class="col-auto">
                <button ng-disabled="currentPage >= analytics.length/pageSize - 1" ng-click="currentPage=currentPage+1" class="btn btn-primary">Next</button>
            </div>
        </div>
    </div>

</div>

<script>

    // session token
    var token = "<?php echo $token; ?>",
    id = "";

    catglueQuiz.controller('adminController', $scope => {

        $scope.showOnlyUnreadFeedback = true;

        $scope.reportedQuizzes = <?php echo $quizzes; ?>;

        $scope.problems = <?php echo $problems; ?>;

        $scope.feedbacks = <?php echo $feedback; ?>;

        $scope.analytics = <?php echo $analytics; ?>;

        // add index
        for (let j = 0; j < $scope.analytics.length; j++) {
            $scope.analytics[j].index = j + 1;            
        }

        $scope.$watch('feedbacks', () => {
            $scope.unreadFeedbacks = $scope.feedbacks.filter(e => e.feedback_read == 0);
        }, true)


        // approve a reported quiz as safe
        $scope.approve = n => {
            id = $scope.reportedQuizzes[n].id;
            $.ajax({
                type: "POST",
                url: "data-modify.php",
                data: {id, action: "approve", token}
            }).done(() => {
                $scope.$apply(() => {
                    $scope.reportedQuizzes.splice(n, 1);
                })
            })
        }

        // remove a reported quiz from db
        $scope.remove = n => {
            id = $scope.reportedQuizzes[n].id;
            $.ajax({
                type: "POST",
                url: "data-modify.php",
                data: {id, action: "remove", token}
            }).done(() => {
                $scope.$apply(() => {
                    $scope.reportedQuizzes.splice(n, 1);
                })
            })
        }

        // mark a feedback as read
        $scope.read = n => {
            id = $scope.feedbacks[n].id;
            $.ajax({
                type: "POST",
                url: "data-modify.php",
                data: {id, action: "read_feedback", token}
            }).done(() => {
                $scope.$apply(() => {
                    $scope.feedbacks[n].feedback_read = 1;
                })
            })
        }

        // mark a problem as solved
        $scope.solved = n => {
            id = $scope.problems[n].id;
            $.ajax({
                type: "POST",
                url: "data-modify.php",
                data: {id, action: "solved_problem", token}
            }).done(() => {
                $scope.$apply(() => {
                    $scope.problems.splice(n, 1);
                })
            })
        }

        // pagination for analytics
        $scope.currentPage = 0;
        $scope.pageSize = 5;
        $scope.numberOfPages=function(){
            return Math.ceil($scope.analytics.length/$scope.pageSize);                
        }

    })

    // startFrom filter
    catglueQuiz.filter('startFrom', function() {
            return function(input, start) {
                start = +start; //parse to int
                return input.slice(start);
            }
    });

</script>

<?php

    //footer file
    require('./templates/footer.php');

?>