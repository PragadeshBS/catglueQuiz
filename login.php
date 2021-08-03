<?php
    require('./templates/header.php');

    //redirect logged in users to home
    if (isset($_SESSION['user']))
    {
        header('location: index.php');
    }

    // init vars
    $email = $password = $out_err = "";
    
    // redirect users coming from pages that require authentication
    $flow = $_GET ? ($_GET['flow'] ? $_GET['flow'] : 'index.php') : 'index.php';

    // get from form submitions
    if (isset($_POST['flow']))
    {
        switch ($_POST['flow']) {
            // from create quiz page
            case 'create':
                $flow = 'create.php';
                break;
            // from posts
            case 'create.php':
                $flow = 'create.php';
                break;
            // default redirect to home
            default:
                $flow = 'index.php';
                break;
        }
    }

    // on form submit
    if (isset($_POST['submit']))
    {
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "SELECT username, password, is_admin FROM users WHERE email='$email'";

        $result = mysqli_query($conn, $sql);

        if (!$result->num_rows)
        {
            // no row found for given email
            $out_err = "Oops! We couldn't find your account, enter a registered email or <a href='signup.php'>sign up</a>";
        }
        else
        {            
            $arr = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
            if (password_verify($_POST['password'], $arr['password']))
            {
                // password match success redirect to respective page
                $_SESSION['user'] = $arr['username'];
                $_SESSION['is_admin'] = $arr['is_admin'];
                header("location: $flow");
            }
            else
            {
                // password didn't match
                $out_err = "Invalid password, try again";
            }
        }


    }
?>
<div class="container bg-light py-4 my-3 rounded" ng-controller="loginFormController" ng-init="login.email='<?php echo htmlspecialchars($email); ?>'">
    
    <div class="text-center text-primary">
        <h1 class="display-3 mb-5">Sign In</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 row justify-content-center">

            <!-- errors in form validation -->
            <?php if ($out_err): ?>
                <div class='col-10 rounded-pill alert alert-danger lead text-center'>
                    <?php echo $out_err; ?>
                </div>
            <?php endif; ?>
            
            <!-- ui flow message after login -->
            <?php if ($flow != "index.php" && (!isset($_POST['submit']))): ?>
                <div class ='col-6 rounded-pill alert alert-alt lead text-center'>
                    Sign in to continue...
                </div>
            <?php endif; ?>
    
            <form method="POST" name="loginForm" class="row justify-content-center" action="login.php" novalidate>
                <div class="mb-4 row justify-content-center">
                    
                    <div class="input-group">
                        
                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter the email you signed up with">
                            <i class="bi bi-envelope-fill"></i>
                            </span>
                        </span>

                        <div class="form-floating col-10">

                            <input class="form-control rounded-0 rounded-end" ng-model="login.email" type="text" name="email" id="email" placeholder="name@example.com"
                            ng-required="true">
                            <label for="email" class="form-label">Email</label>

                        </div>

                    </div>

                </div>

                <div class="mb-4 row justify-content-center">
                    
                    <div class="input-group">

                        <!-- tooltip -->
                        <span class="input-group-text col-auto">
                            <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter your password">
                            <i class="bi bi-key-fill"></i>
                            </span>
                        </span>

                        <div class="form-floating col-10">

                            <input class="form-control rounded-0 rounded-end" type="password" name="password" id="password" placeholder="Password"
                            ng-required="true" ng-model="login.password">
                            <label for="password" class="form-label">Password</label>

                        </div>

                    </div>
                </div>

                <!-- attach flow to redirect -->
                <input type="hidden" name="flow" value="<?php echo $flow; ?>">
            
                <div class="mb-4 text-center">            
                    <input class="btn btn-primary" type="submit" value="Login" name="submit" 
                        ng-disabled="loginForm.$invalid">
                </div>
            </form>

            <div class="mt-3 text-muted">
                <small>Don't have an account yet? <span><a href="signup.php">Sign Up</a></span></small>
            </div>

        </div>
    </div>
</div>

<script>

    // loginForm controller
    catglueQuiz.controller('loginFormController', $scope => {});

</script>

<?php
    require('./templates/footer.php');
?>