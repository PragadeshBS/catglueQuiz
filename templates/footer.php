<div class="mt-5 py-3 px-5 bg-altlight text-primary">
    <div class="container mt-3 row">
        <div class="col-8">
            <h6 class="display-6"><a class="text-reset text-decoration-none" href="index.php">catglue Quiz</a></h6>
            <p class="lead">Fun of quizzing, brought online</p>
        </div>
        <!-- <div class="col-3"></div> -->
        <div class="col-4 text-muted">
            <ul style="list-style-type: none;" class="float-end">
                <li class="pb-3 mb-3"><a href="faq.php" class="text-reset text-decoration-none">FAQ</a></li>
                <li class="pb-3 mb-3"><a href="report-problem.php" class="text-reset text-decoration-none">Report a problem</a></li>
                <li class="pb-3"><a href="feedback.php" class="text-reset text-decoration-none">Feedback/ Suggestions</a></li>
            </ul>
        </div>
    </div>
</div>


<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

<script>

    
    // range filter
    catglueQuiz.filter('range', function() {
        return function(input, min, max) {
            min = parseInt(min); //Make string input int
            max = parseInt(max);
            for (var i=min; i<=max; i++)
            input.push(i);
            return input;
        };
    });
    
    // Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // quiz carousel
    <?php if (substr_count($_SERVER['PHP_SELF'], 'quiz.php') > 0 || substr_count($_SERVER['PHP_SELF'], 'preview.php') > 0): ?>
        var quizCarousel = document.querySelector('#quizCarousel');
        var carousel = new bootstrap.Carousel(quizCarousel, {
            interval: false
        });
    <?php endif; ?>

    <?php if (substr_count($_SERVER['PHP_SELF'], 'preview.php') > 0): ?>
        var createdQuizTakeModal = new bootstrap.Modal(document.getElementById('createdQuizTake'))
        <?php if($created_quiz_take_modal_show): ?>
            createdQuizTakeModal.show();
        <?php endif; ?>
    <?php endif; ?>
    
    // bootstrap modal for quiz start, initiate only on quiz page
    var url = "<?php echo $_SERVER['REQUEST_URI']; ?>";
    if (url.indexOf('quiz.php') != -1)
    {
        var startModal = new bootstrap.Modal(document.getElementById('startModal'), {keyboard: false, backdrop: 'static'});
        var confirmQuizEndModal = new bootstrap.Modal(document.getElementById('confirmQuizEndModal'));
        var abuseModal = new bootstrap.Modal(document.getElementById('abuseModal'));
    }
    // bootstrap modal for delete quiz confirmation on profile page
    if (url.indexOf('profile.php') != -1)
    {
        var deleteQuizConfirmationModal = new bootstrap.Modal(document.getElementById('deleteQuizConfirmationModal'));
    }


    // logs
    <?php if (!isset($_SESSION['logged'])): ?>

        $.get('https://www.cloudflare.com/cdn-cgi/trace', function(data) {
            // Convert key-value pairs to JSON
            data = data.trim().split('\n').reduce(function(obj, pair) {
                pair = pair.split('=');
                return obj[pair[0]] = pair[1], obj;
            }, {});
            var logData = data;
            // detect brave browser
            $.get('https://api.duckduckgo.com/?q=useragent&format=json', function(data){
                var jsonData = JSON.parse(data)
                var isBrave = jsonData['Answer'].includes('Brave');
                if (isBrave)
                {
                    logData.uag += " Brave";
                }
                $.ajax({
                    type: "POST",
                    url: "log.php",
                    data: {loc: logData.loc, uag: logData.uag}
                }).done(function( msg ) {});
            })
        });

        <?php $_SESSION['logged'] = true; ?>
    
    <?php endif; ?>

</script>

</body>
</html>