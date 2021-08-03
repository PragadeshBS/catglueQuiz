<?php

// header file
require("./templates/header.php");

$created_quiz_take_modal_show = false;

// post for changing visibility status
if (isset($_POST['submit']))
{
  $id = $_POST['id'];

  // make public
  if ($_POST['set-visibility'] == 1)
  {
    $sql = "UPDATE quizzes SET visibility = 1 WHERE id='$id'";
    if (!mysqli_query($conn, $sql))
    {
      echo "Error: ".mysqli_error($conn);
    }
  }

  // make private
  else if ($_POST['set-visibility'] == 2)
  {
    $sql = "UPDATE quizzes SET visibility = 2 WHERE id='$id'";
    if (!mysqli_query($conn, $sql))
    {
      echo "Error: ".mysqli_error($conn);
    }
  }

}

// verify page validity
if (isset($_GET['id']) || isset($id))
{
    if (isset($_GET['id']))
    {
      $id = $_GET['id'];
      if (isset($_GET['quiz']) && $_GET['quiz'] == "attend")
      {
        $created_quiz_take_modal_show = true;
      }
    }
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

        // only admins and the quiz creator can view
        if (!$_SESSION['is_admin'] && $meta_arr['created_by'] != $_SESSION['user'])
        {
            // access to quiz created by another user
            header("Location: 403.php");
        }
        else
        {
            // success
            $user = $_SESSION['user'];
            $content_sql = "SELECT content FROM quiz_contents WHERE id='$id'";
            $content_result = mysqli_query($conn, $content_sql);

            $user_sql = "SELECT created_quiz_ids FROM users WHERE username='$user'";
            $user_result = mysqli_query($conn, $user_sql);
            $created_quiz_ids = mysqli_fetch_row($user_result)[0];
            $created_quiz_ids_json = json_decode($created_quiz_ids);

            // get quiz contents as json from db
            $content_json = mysqli_fetch_assoc($content_result)['content'];
            $content_assoc = json_decode($content_json);
            $question_nums = $meta_arr['question_nums'];

            $title = htmlspecialchars($meta_arr['title']);
            $description = htmlspecialchars($meta_arr['description']);
            $likes = htmlspecialchars($meta_arr['likes']);
            $timer = $meta_arr['timer'];
            $visibility = $meta_arr['visibility'];

            // show message based on visibility
            switch ($visibility) {
              case 0:
                // private by default after just being created
                $message = "You have successfully created the quiz. You might want to make the quiz public for others to access";

                // append quiz id to users created quizzes list

                // json cast method
                // $sql = "UPDATE users SET created_quiz_ids = JSON_ARRAY_APPEND(created_quiz_ids, '$', CAST('\"$id\"' AS JSON)) WHERE username = '$user'";

                // get update and set method
                array_push($created_quiz_ids_json, $id);
                $to_save = json_encode($created_quiz_ids_json);
                $sql = "UPDATE users SET created_quiz_ids = '$to_save' WHERE username = '$user'";

                // award points for creating a quiz based on number of questions
                $score_sql = "UPDATE users SET points = points + $question_nums WHERE username = '$user'";

                if (!mysqli_query($conn, $sql))
                {
                  echo "Error: ".mysqli_error($conn);
                }

                if (!mysqli_query($conn, $score_sql))
                {
                  echo "Error: ".mysqli_error($conn);
                }
                break;
              
              case 1:
                // public
                $message = "This quiz is public, you can make it private to restrict access to others";
                break;
             
              case 2:
                // private
                $message = "This quiz is not public, only you can see this preview";
                break;

              case 3:
                // reported abuse
                $message = "<div class='alert alert-danger my-3'>This quiz has been reported for abuse, you cannot make this quiz public until an admin verifies the content. Your quiz may also be removed if an admin considers it to contain abusive content.</div>";
                break;
              
              default:
                $message = "Quiz status unknown";
                break;
            }
        }
    }
}
else
{
    // redirect requests without id
    header("location: 404.php");
}

if ($visibility == 0)
{
  $sql = "UPDATE quizzes SET visibility = 2 WHERE id='$id'";
  if (!mysqli_query($conn, $sql))
    {
      echo "Error: ".mysqli_error($conn);
    }
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

<div class="container my-3" ng-controller="quizPreviewController">


  <!-- cannot take your own quiz Modal -->
  <div class="modal fade" id="createdQuizTake" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Note</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div>It wouldn't be fair if you could take your own quiz <i class="bi bi-emoji-wink"></i></div>
          <div>Here's a preview of your quiz...</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="window.location.href = 'index.php'">Go home</button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Show Preview</button>
        </div>
      </div>
    </div>
  </div>

  <h1 class="display-1 text-primary">Quiz Preview</h1>

  <div ng-show="visibility == 0">
    <h6 class="text-muted">Step 3/3</h6>
  </div>

  <!-- quiz title -->
  <h1 class="display-5"><?php echo $title; ?></h1>

  <!-- quiz description -->
  <p class="lead"><?php echo $description; ?></p>
  

  <div class="row justify-content-center my-3 text-center">

    <div class="col-auto bg-light text-primary p-3 rounded-start">
      <!-- questions nums -->
      <h4><?php echo $meta_arr['question_nums']; ?> Qs</h4>
    </div>

    <div class="col-auto bg-secondary text-primary p-3">
      <!-- no of likes & questions-->
      <h4><?php echo $likes; ?> <i class="bi bi-hand-thumbs-up"></i></h4>
    </div>

    <div class="col-auto bg-light text-primary p-3 rounded-end">
      <!-- timer -->
      <div ng-show="timer!=1" class="h4">{{ timer }} Mins</div>
      <div ng-hide="timer!=1" class="h4">{{ timer }} Min</div>
    </div>

  </div>

  <!-- select to jump directly to a specific question -->
  <div class="row my-3">
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

  <div class="mt-5 mb-3 row justify-content-center">
    <!-- only show messages to non admins -->
    <div class="col-auto h5 text-muted">
      <?php echo !$_SESSION['is_admin'] ? $message : ''; ?>
    </div>
  </div>

  <div class="row justify-content-center">
    <div ng-show="visibility == 0 || visibility == 2" class="col-auto">
      <div>
        <form method='POST' action='preview.php'>
          <input type='hidden' value='1' name='set-visibility' />
          <input type='hidden' value="<?php echo $id; ?>" name='id' />
          <button class='btn btn-lg btn-warning' type='submit' value='submit' name='submit'>Make quiz public</button>
        </form>
      </div>
    </div>
    <div ng-show="visibility == 1" class="col-auto">
      <div>
        <form method='POST' action='preview.php'>
          <input type='hidden' value='2' name='set-visibility' />
          <input type='hidden' value="<?php echo $id; ?>" name='id' />
          <button class='btn btn-lg btn-success' type='submit' value='submit' name='submit'>Make quiz private</button>
        </form>
      </div>
    </div>
  </div>

</div>


<script>

  catglueQuiz.controller('quizPreviewController', function($scope){

    $scope.visibility = <?php echo $visibility; ?>;

    $scope.timer = <?php echo $timer; ?>/60;

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

    for(let [key, value] of Object.entries($scope.quizContentObj)){
      
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
    }
  });

</script>

<?php

// footer file
require("./templates/footer.php");

?>