<?php 

    // header file
    require('./templates/header.php');

    // rediret signed out users 
    if (!isset($_SESSION['user']))
    {
        header("Location: login.php");
    }

    // verify page validity
    if (isset($_GET['id']))
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
            $meta_arr = mysqli_fetch_assoc($result);
            if ($meta_arr['visibility'] != 1)
            {
                // prevent access to a private/reported quiz
                header("Location: 403.php");
            }
            else if ($meta_arr['created_by'] == $_SESSION['user'])
            {
              header("location: preview.php?id=$id&quiz=attend");
            }
            else
            {
                // success
                $content_sql = "SELECT content FROM quiz_contents WHERE id='$id'";
                $content_result = mysqli_query($conn, $content_sql);

                // get quiz contents as json from db
                $content_json = mysqli_fetch_assoc($content_result)['content'];
                $content_assoc = json_decode($content_json);

                $title = htmlspecialchars($meta_arr['title']);
                $description = htmlspecialchars($meta_arr['description']);
                $likes = htmlspecialchars($meta_arr['likes']);

                $user = $_SESSION['user'];
                $user_likes_sql = "SELECT liked_quiz_ids FROM users WHERE username='$user'";
                $user_likes_result = mysqli_query($conn, $user_likes_sql);
                
                $user_likes_json = mysqli_fetch_row($user_likes_result)[0];

                $liked = strpos($user_likes_json, $id);

                $token = $_SESSION['token'];
            }
        }
    }
    else
    {
       // redirect requests without id
       header("location: 404.php");
    }
    
?>

<style>
  input[type=radio]{
    /* hide radio button */
    display: none;
  }
  .list-group-item{
    /* pointer cursor for options */
    cursor: pointer;
  }
</style>

<div class="container my-4" ng-controller="quizController">

  <!-- Initial Modal -->
  <div class="modal fade" id="startModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Start Quiz?</h5>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <div class="modal-body text-center">
          <div class="my-3">This quiz is timed for {{<?php echo $meta_arr['timer']; ?>/60}} minutes</div>
          <!-- <div class="my-3">Would you like to start taking the quiz?</div> -->
          <div class="text-muted">Each right answer will fetch you 1 point</div>
          <div class="text-muted">No negative score for wrong answers</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-altlight" data-bs-dismiss="modal" onclick="window.location='index.php'">Go to homepage</button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="startQuiz()">Let's go</button>
        </div>
      </div>
    </div>
  </div>

  <!-- End quiz confirmation modal -->
  <div class="modal fade" id="confirmQuizEndModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Finish Quiz?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          This will end the quiz. Do you want to proceed?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-altlight" data-bs-dismiss="modal">Go back</button>
          <button type="button" class="btn btn-danger" onclick="document.getElementById('finishQuizBtn').click();">End Quiz</button>
        </div>
      </div>
    </div>
  </div>

  <!-- report abuse confirmation modal -->
  <div class="modal fade" id="abuseModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Report abuse?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          If you think this quiz contains abusive content, you can report it anonymously.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-altlight" data-bs-dismiss="modal">Close</button>
          <form action="abuse.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button type="submit" name="submit" class="btn btn-danger">Report quiz</button>
          </form>
        </div>
      </div>
    </div>
  </div>




  <!-- quiz title -->
  <h1 class="display-1 text-primary"><?php echo $title; ?></h1>

  <!-- quiz description -->
  <p class="lead"><?php echo $description; ?></p>
  
  <div class="row justify-content-center my-3 text-center">

    <div class="col-auto text-primary p-3">
      <!-- questions nums -->
      <h4><?php echo $meta_arr['question_nums']; ?> Qs</h4>
    </div>

    <!-- not liked -->
    <div id="like" class="cursor-pointer rounded-circle col-auto bg-light text-primary p-3" ng-hide="liked">
      <!-- no of likes & questions-->
      <h4>{{likes}} <i class="bi bi-hand-thumbs-up"></i></h4>
    </div>
    
    <!-- liked -->
    <div id="dislike" class="cursor-pointer rounded-circle col-auto bg-secondary text-primary p-3" ng-show="liked">
      <!-- no of likes & questions-->
      <h4>{{likes}} <i class="bi bi-hand-thumbs-up-fill"></i></h4>
    </div>

    <div class="col-auto text-primary p-3">
      <!-- timer -->
      <div ng-show="timer!=1" class="h4">{{ <?php echo $meta_arr['timer']; ?> / 60 }} Mins</div>
      <div ng-hide="timer!=1" class="h4">{{ <?php echo $meta_arr['timer']; ?> / 60 }} Min</div>
    </div>

  </div>

  <!-- no of likes & questions-->
  <div class="row justify-content-center">
    <!-- <div class="h1" style="cursor: pointer;">
      <p id="like" ng-hide="liked">{{likes}} <i class="bi bi-hand-thumbs-up"></i></p>
      <p id="dislike" ng-show="liked">{{likes}} <i class="bi bi-hand-thumbs-up-fill"></i></p>
    </div> -->
    <small class="text-muted col-auto mb-3">Find the quiz interesting? Give it a Like and help more people find it</small>
  </div>

  <!-- active timer -->
  <div class="row justify-content-center my-3">
    <div id="time" class="col-auto rounded-pill text-primary h3 p-3 bg-altlight"><?php echo $meta_arr['timer']/60 > 9 ? $meta_arr['timer']/60: '0'.$meta_arr['timer']/60; ?>:00</div>
  </div>

  <!-- select to jump directly to a specific question -->
  <div class="row mt-3 mb-5">
    <label for="jump-select" class="col-auto h3">Jump to a question</label>
    
    <div class="col-auto">
      <select class="form-select" ng-model="jumpSelection" ng-options="n for n in [] | range:1:<?php echo $meta_arr['question_nums']; ?>"
        id="jump-select" ng-change="jump()">
      </select>
    </div>
  </div>

  <form name="quizPreviewForm">

    <!-- bs carousel -->
    <div id="quizCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">

      <div class="carousel-inner">

        <!-- loop through quiz content and set first carousel as active -->
        <div class="carousel-item" ng-repeat="question in questions" ng-class="question == questions[0] ? 'active' : ''">

          <!-- question statement with no -->
          <p class="d-flex justify-content-center h3">{{question[1] + '. ' + question[0]}}</p>

          <!-- options list group -->
          <div class="list-group">

            <label ng-repeat="n in [] | range:1:4" for="option-{{ n }}-question-{{ question[1] }}">
              
              <!-- selected option to have bs success class  -->
              <div class="list-group-item list-group-action me-auto ms-auto py-3" 
              style="width: 60%;" ng-class="answers[question[1]] == options[question[1]][n-1] ? active : null">
                
                <input type="radio" name="question-{{question[1]}}" 
                id="option-{{ n }}-question-{{ question[1] }}" value="{{ options[question[1]][n-1] }}" ng-model="answers[question[1]]">

                <!-- option text -->
                {{ options[question[1]][n-1] }}

              </div>

            </label>

          </div>

        </div>

      </div>

      <!-- carousel control buttons -->
      <button class="carousel-control-prev" type="button" data-bs-target="#quizCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>

      <button class="carousel-control-next" type="button" data-bs-target="#quizCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>

    </div>

  </form>

  <div>
    <div class="text-center my-3">
      <form method='POST' action='score.php'>
        <input type='hidden' value='{{score}}' name='score' />
        <input type='hidden' value="<?php echo $id; ?>" name='id' />
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <!-- the submit button hidden by default -->
        <button class='invisible' id="finishQuizBtn" type='submit' value='submit' name='submit'></button>
        <button class='btn btn-primary mt-5 mb-3' onclick="confirmQuizEndModal.show()" type='button'>Finish quiz</button>
      </form>
    </div>
  </div>

  <div class="text-center">
    <small class="text-muted cursor-pointer" onclick="abuseModal.show()">Report Abuse</small>
  </div>

</div>




<script>

    catglueQuiz.controller('quizController', $scope => {

    // score
    $scope.score = 0;
        
    // add bs class to current option selected by user
    $scope.active = 'list-group-item-success';
    
    // jump to a specific question
    $scope.jumpSelection = 1;

    $scope.jump = function(){
      // bs carousel array uses 0 indexing
      carousel.to($scope.jumpSelection-1);
    }

    // nested questions array with first element as the question and second as the index starting from 1
    $scope.questions = [];

    // options obj with qs no as key and options an an array value
    $scope.options = {};

    // get json from php
    $scope.quizContentObj = <?php echo $content_json; ?>;

    // store answers chose by user in obj with question index as key and option content as value
    $scope.answers = {}

    // right answers
    $scope.rightAnswers = {};

    for(let [key, value] of Object.entries($scope.quizContentObj))
    {
      
      // add questions
      if(key.startsWith('question') && !key.endsWith('nums'))
      {
        // add question with index
        $scope.questions.push([value, $scope.questions.length + 1])
      }

      // add options
      else if (key.startsWith('option'))
      {
        let questionNo = parseInt(key.slice(18));

        // create new key as qs no if not exists
        if (!$scope.options[questionNo])
        {
          $scope.options[questionNo] = [];
          $scope.answers[questionNo] = null;
          $scope.options[questionNo].push(value);
        }

        // add options array to existing question no key
        else
        {
          $scope.options[questionNo].push(value);
        }
      }

      // add right answers to rightAnswers obj
      else if (key.startsWith('right_option'))
      {
        let questionNo = parseInt(key.slice(13));
        // create new key with qs no as key and right option no as value
        // parsing option no from angular expression
        $scope.rightAnswers[questionNo] = value.slice(14);
      }
    }

    // map right options to their respective values
    for (let j = 0; j < $scope.questions.length; j++)
    {
      let rightOptionValue = $scope.rightAnswers[j+1];
      $scope.rightAnswers[j+1] = $scope.options[j+1][rightOptionValue-1];
    }

    // update score while answer obj changes
    $scope.$watch('answers', function(){
      $scope.score = 0;
      // loop through each qs and compare with answer
      for (let j = 0; j < $scope.questions.length; j++)
      {
        if ($scope.answers[j+1] == $scope.rightAnswers[j+1])
        {
          $scope.score += 1;
        }
      }
    }, true);

    // likes
    $scope.liked = <?php echo $liked ? 'true' : 'false'; ?>;
    $scope.likes = <?php echo $likes; ?>;
    $scope.likedQuizIds = <?php echo $user_likes_json; ?>;
  
    $('#like').click(function() {
      var newUserLikedIds = [...$scope.likedQuizIds, "<?php echo $id; ?>"];
      $.ajax({
        type: "POST",
        url: "likes.php",
        data: { id: "<?php echo $id; ?>", action: "like", liked_ids_json: JSON.stringify(newUserLikedIds), user: "<?php echo $user; ?>"}
      }).done(function( msg ) {
        $scope.$apply(function(){
          $scope.liked = true;
          $scope.likes++;
          $scope.likedQuizIds = newUserLikedIds;
        });
      });
    });
    
    $('#dislike').click(function() {
      var newUserLikedIds = [...$scope.likedQuizIds.filter(quizId => quizId != "<?php echo $id; ?>")];
      $.ajax({
        type: "POST",
        url: "likes.php",
        data: { id: "<?php echo $id; ?>", action: "dislike", liked_ids_json: JSON.stringify(newUserLikedIds), user: "<?php echo $user; ?>"}
      }).done(function( msg ) {
        $scope.$apply(function(){
          $scope.liked = false;
          $scope.likes--;
          $scope.likedQuizIds = newUserLikedIds;
        });
      });
    });

  });

    function startTimer(duration, display) {
      var timer = duration, minutes, seconds;
      var countDownTimer = setInterval(timerFunc, 1000)
      function timerFunc() {
          minutes = parseInt(timer / 60, 10);
          seconds = parseInt(timer % 60, 10);

          minutes = minutes < 10 ? "0" + minutes : minutes;
          seconds = seconds < 10 ? "0" + seconds : seconds;

          display.textContent = minutes + ":" + seconds;

          if (--timer < 0) {
            clearInterval(countDownTimer);
            // end the quiz after time runs out
            document.getElementById('finishQuizBtn').click();
          }
      }
  }

  var startQuiz = function () {
    // time in seconds
    var time = <?php echo $meta_arr['timer']; ?>,
    // var time = 10,
    display = document.querySelector('#time');
    startTimer(time, display);
  };

  window.onload = function(){
    startModal.show();
  }

</script>


<?php
    // footer file
    require('./templates/footer.php');
?>