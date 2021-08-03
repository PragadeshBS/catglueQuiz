<?php 

    // header file
    require('./templates/header.php');

    $sql = "SELECT * FROM quizzes WHERE visibility = 1 ORDER BY likes DESC";
    $result = mysqli_query($conn, $sql);
    $quiz_arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $quiz_arr_json = json_encode($quiz_arr);

    $leader_board_sql = "SELECT username, points FROM users ORDER BY points DESC";
    $leader_board_result = mysqli_query($conn, $leader_board_sql);
    $leader_board_json = json_encode(mysqli_fetch_all($leader_board_result, MYSQLI_ASSOC));

?>

<div class="container my-3" ng-controller="indexController">
    
    <div class="row">
        <div class="col-lg-7">
            <h1 class="display-1 my-3 text-primary">Quizzes</h1>
            <!-- show when quizzes are available -->
            <div ng-show="quizzes.length">
                <!-- show quiz cards based on isVisible prop -->
                <div ng-repeat="quiz in quizzes" ng-show="quiz.isVisible" class="card w-100 my-3">
                    <!-- card links to quiz page -->
                    <a href="quiz.php?id={{quiz.id}}" class="text-decoration-none text-reset">
                        <div class="card-body">
                            <!-- quiz title with no of likes -->
                            <div class="card-title h5">
                                <span class="display-6">{{quiz.title}}</span>
                                <!-- no of likes -->
                                <span class="badge bg-primary mx-1" ng-hide="quiz.likes==1">{{quiz.likes}} Likes</span>
                                <span class="badge bg-primary mx-1" ng-show="quiz.likes==1">{{quiz.likes}} Like</span>
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
            <!-- show incase no quizzes are available -->
            <div ng-hide="quizzes.length" class="mb-5">
                <p class="lead">No Quizzes are available currently, why not <a href="create.php">create one</a>?</p>
            </div>
            <!-- show if no quizzes available for a specific search query -->
            <div ng-show="noSearchResults" class="lead">
                <p>Oops! Not many great matches came back for your search</p>
                <p class="text-muted"><i class="bi bi-chevron-right"></i>Try more general keywords</p>
            </div>
        </div>
        <!-- leader board -->
        <div class="col-lg-5">
            <div class="mt-5 py-3 px-3 bg-light border border-2 rounded-3 border-primary">
                <h1 class="display-4 text-center text-dark"><i class="bi bi-stars text-primary"></i> Leader Board</h1>
                <hr>
                <!-- username and points scored -->
                <div ng-repeat="user in leaderBoard" ng-show="user.username != 'admin'">
                    <div>
                        <h3 class="display-6 text-dark">{{$index+1}}. {{user.username}}</h3>
                        <h4 class="text-muted">Score: {{user.points}}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    catglueQuiz.controller('indexController', function($scope){

        // list of quizzes
        $scope.quizzes = <?php echo $quiz_arr_json; ?>;

        // set quiz to be visible initially
        $scope.quizzes.forEach(quiz => quiz.isVisible = true);

        // list of users in leader board
        $scope.leaderBoard = <?php echo $leader_board_json; ?>;

        /* 
            search function
        */

        $scope.noSearchResults = false;

        // toggle visibility property while searching
        $scope.$watch('searchTerm', () => {

            // search only after a char is entered
            if ($scope.searchTerm)
            {
                // split words from search query
                let terms = $scope.searchTerm.split(' ');

                // iterate through each quiz
                for (quiz of $scope.quizzes)
                {
                    let title = quiz.title.toLowerCase(),
                    description = quiz.description.toLowerCase();
    
                    // set quiz found flag to false initially
                    let found = false;

                    // search against each individual word in query
                    for (term of terms)
                    {
                        // do a case insensitive search
                        let searchTerm = term.toLowerCase();

                        // search in title and description
                        if (title.indexOf(searchTerm) != -1 || description.indexOf(searchTerm) != -1)
                        {
                            // set found flag to true, toggle quiz visibility to true and move on to next quiz
                            found = true;
                            quiz.isVisible = true;
                            break;
                        }
                    }
                    if (!found)
                    {
                        // set visibility to false if none of the words match
                        quiz.isVisible = false;
                    }

                }
                let noResults = true;
                $scope.quizzes.forEach(quiz => {
                    if (quiz.isVisible == true)
                    {
                        noResults = false;
                    }
                })
                $scope.noSearchResults = noResults ? true : false;
            }
            else
            {
                // set all quizzes to be visible when no search term is empty
                $scope.quizzes.forEach(quiz => quiz.isVisible = true);
            }
        })

    })
</script>
    
<?php

    // footer file
    require('./templates/footer.php');

?>