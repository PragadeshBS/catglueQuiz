<?php
    require('./templates/header.php');

    // store any errors to display
    $out_err = "";

    // initialise vars
    $email = $username = $password = "";

    //redirect logged in users to home
    if (isset($_SESSION['user']))
    {
        header('location: index.php');
    }
    
    // on form submit
    if (isset($_POST['submit']))
    {
        $id = uniqid();
        $email = mysqli_escape_string($conn, $_POST['email']);
        $username = mysqli_escape_string($conn, $_POST['username']);
        $password = password_hash(mysqli_escape_string($conn, $_POST['password']), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(id, email, username, password, created_at, points, created_quiz_ids, liked_quiz_ids) 
        VALUES('$id', '$email', '$username', '$password', CURRENT_TIMESTAMP(), 0, '[]', '[]');";

        if (substr_count($username, 'admin') > 0)
        {
            $out_err = "Username cannot contain the term 'admin'";
        }
        else if (mysqli_query($conn, $sql))
        {
            // success redirect to home
            $_SESSION['user'] = $username;
            header('location: index.php');
        } 
        else
        {
            if (mysqli_errno($conn) == 1062) {
                $err = mysqli_error($conn);
                if (preg_match("/Duplicate entry(.)*username'$/", $err))
                {
                    $out_err = 'Oops! Username already exists, try another one';
                }
                else if (preg_match("/Duplicate entry(.)*email'$/", $err))
                {
                    $out_err = 'Oops! Email already exists, choose another one or login';
                }
            }
        }
    }
?>
<div class="container bg-light py-4 my-3 rounded" ng-controller="signUpFormController"
 ng-init="signUp.username='<?php echo htmlspecialchars($username); ?>'; 
    signUp.email='<?php echo htmlspecialchars($email); ?>';
">
    
    <div class="text-center text-primary">
        <h1 class="display-3 mb-5">Sign Up</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 row justify-content-center">
        
                <form method="POST" action="signup.php" name="signUpForm" novalidate>
        
                    <!-- output any errors from php validation -->
                    <?php if ($out_err): ?>
                        <div class='alert alert-altdark mb-3 text-center' role='alert'>
                            <?php echo $out_err; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Email field -->
                    <div class="mb-4 row justify-content-center">
        
                        <div class="input-group">
                            <!-- tooltip -->
                            <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Enter an email that you can use to login">
                                <i class="bi bi-envelope-fill"></i>
                                </span>
                            </span>
                            <div class="form-floating col-10">
                                <input class="form-control rounded-0 rounded-end" ng-model="signUp.email" type="email" name="email" id="email"
                                ng-required="true" placeholder="Email">
                                <!-- label -->
                                <label for="email">E-mail</label>
                            </div>
        
                        </div>
                        <!-- invalid email warning -->
                        <div class="text-center rounded-pill bg-danger text-light col-7 py-2 border-1 mt-1"
                            ng-show="signUpForm.email.$touched && signUpForm.email.$invalid">
                            <small>Enter a valid email</small>
                        </div>
                    </div>
                    <!-- Username field-->
                    <div class="mb-4 row justify-content-center">
        
                        <div class="input-group">
                            <!-- tooltip -->
                            <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Pick a unique username that's displayed to others">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                            </span>
        
                            <div class="form-floating col-10">
                                <input class="form-control rounded-0 rounded-end" type="text" ng-model="signUp.username"
                                ng-pattern="usernameReg" name="username" id="username" placeholder="Edward Snowden"
                                ng-required="true" value="hello">
        
                                <!-- label -->
                                <label for="email" class="form-label">Username</label>
                            </div>
                        </div>
                        <!-- invalid username warning -->
                        <div class="text-center rounded-pill bg-danger text-light col-7 py-2 border-1 mt-1"
                        ng-show="signUpForm.username.$touched && signUpForm.username.$invalid" class="text-center alert alert-altdark col-10 mt-3">
                            <small>Username must be 3-20 alphanumeric chars</small>
                        </div>
                    </div>
                    <!-- Password field -->
                    <div class="mb-4 row justify-content-center">
                        <div class="input-group">
                            <!-- tooltip -->
                            <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Pick a strong password">
                                    <i class="bi bi-key-fill"></i>
                                </span>
                            </span>
        
                            <div class="form-floating col-10">
                                <input class="form-control rounded-0 rounded-end" ng-model="signUp.password" type="password" name="password" id="password"
                                ng-required="true" placeholder="Password" ng-pattern="passwordReg">
        
                                <!-- label -->
                                <label for="password" class="form-label">Password </label>
                            </div>
                        </div>
                        <!-- invalid password warning -->
                        <div class="text-center rounded-pill bg-danger text-light col-10 py-2 border-1 mt-1"
                        ng-show="signUpForm.password.$touched && signUpForm.password.$invalid">
                            <small>Password must be at least 8 chars long and cannot contain quotes</small>
                        </div>
                    </div>
        
                    <!-- Re-enter password field -->
                    <div class="mb-4 row justify-content-center">
        
                        <div class="input-group">
                            <!-- tooltip -->
                            <span class="input-group-text col-auto">
                                <span class="tt" data-bs-toggle="tooltip" data-bs-placement="left" title="Re-enter your password">
                                <i class="bi bi-key-fill"></i>
                                </span>
                            </span>
        
                            <div class="form-floating col-10">
                                <input class="form-control rounded-0 rounded-end" type="password"
                                ng-model="signUp.rePassword" name="re_password" id="re-password"  ng-required="true"
                                placeholder="Re-enter Password">
                                <!-- label -->
                                <label for="re-password" class="form-label">Re-enter Password</label>
                            </div>
                        </div>
                        <!-- password match warning -->
                        <div class="text-center rounded-pill bg-danger text-light col-7 py-2 border-1 mt-1"
                        ng-show="signUpForm.password.$touched && signUpForm.re_password.$touched && signUp.password !== signUp.rePassword">
                            <small>Passwords do not match</small>
                        </div>
                    </div>
        
        
                    <!-- Submit button -->
                    <div class="mb-4 text-center">
                        <input class="btn btn-primary" ng-disabled="signUpForm.$invalid || signUp.password !== signUp.rePassword"
                        type="submit" value="Sign Up" name="submit" id="submit-btn">
                    </div>
                </form>
                <div class="mt-3 text-muted">
                    <small>Already have an account? <span><a href="login.php">Login</a></span></small>
                </div>
        </div>
    </div>
</div>

<script>

    // signupForm controller for form validation
    catglueQuiz.controller('signUpFormController', function($scope){

        $scope.usernameReg = /^([a-zA-Z_0-9- ]{3,20})$/;
        $scope.passwordReg = /^([^"']{8,200})$/;

    })

</script>

<?php
    require('./templates/footer.php');
?>