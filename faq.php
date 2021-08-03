<?php
    require('./templates/header.php');
?>

<div class="container" ng-controller = "faqController">
    <h1 class="display-1 text-primary my-3">FAQ</h1>

    <div ng-repeat = "faq in faqs" class="card w-100 my-5 px-3">
        <div class="card-body">
            <h6 class="display-6 card-title">{{ $index+1 }}. {{ faq.qs }}</h6>
            <p class="card-text text-muted ps-3 ms-3">{{ faq.ans }}</p>
        </div>
    </div>

</div>

<script>
    catglueQuiz.controller('faqController', $scope => {
    $scope.faqs = [
        {
            qs: "What is catglue Quiz?",
            ans: "catglue Quiz is an online quiz portal designed to bring the fun of quizzing online."
        },
        {
            qs: "How can I attend a quiz?",
            ans: "Once logged in, you can click on any quiz that you would like to attend from the homepage."
        },
        {
            qs: "Can I create my own quiz?",
            ans: "Yes! Everyone has the ability to create their own quiz, you can then make the \
            quiz public to let others try out your quiz. You are also awarded points when you create a quiz."
        },
        {
            qs: "Are the quizzes based on any specific topic/category?",
            ans: "Quizzes that you create or attend can be from a diverse range of topics, not confined to any specific category."
        },
        {
            qs: "Are the quizzes only text based?",
            ans: "For now, yes, but quizzes with other features like images or audio are coming soon."
        },
        {
            qs: "Are the quizzes timed?",
            ans: "Yes, each quiz has a timer that starts once you begin taking the quiz. The duration is decided by the quiz creator."
        },
        {
            qs: "What if I could not finish a quiz within the given time?",
            ans: "Your existing score will be taken into account. You can retake the quiz at anytime."
        },
        {
            qs: "How can I place myself on the leader board?",
            ans: "Leader board position is determined by your score, users with the most score will be listed on the leader board."
        },
        {
            qs: "How can I increase my points?",
            ans: "Attending quizzes and creating quizzes are the ways in which you can score points. Answering a quiz \
            question rightly will give you one point, while creating a quiz will give you as many points as the number of \
            questions in your quiz."
        },
        {
            qs: "Can I modify a quiz once it's created?",
            ans: "Yes, you can modify all the details of your quiz and the quiz contents. You \
            also have the ability to make your quiz private or delete your quiz, just in case you want to." 
        },
        {
            qs: "I found a quiz with improper content, is there anything I can do?",
            ans: "All users have the ability to report a quiz if they find something wrong about the contents of the quiz. \
            Reports are anonymous and cannot be traced back to you. We encourage you to report improper quizzes so that we can \
            then take action on them."
        },
        {
            qs: "My quiz has been reported by someone for abuse. What should I do?",
            ans: "There's nothing you need to do. After we verify the quiz content and find it to be safe, we will make your quiz \
            public again. But if we feel that there's something wrong with your quiz, we might permanently remove it from the site."
        },
        {
            qs: "Why do quizzes have 'likes'?",
            ans: "The 'like' feature helps us to bring out the most popular quizzes among many. It acts like an up vote feature. \
            You can like a quiz if you find it to be interesting. Others cannot see the quizzes that you have liked."
        },
        {
            qs: "Is it safe to enter my email on the site?",
            ans: "We do not reveal your email to others, your username is the only public info that you share with others. \
            Your password is securely hashed before storing in the database, so only you can access your account, nobody else can, not even us."
        },
        {
            qs: "Do you log any personal information?",
            ans: "We keep track of the email address that you use to register with us. \
            Apart from this, we log your browser's user agent string and the country code from your \
            IP address (though we do NOT store your IP address itself), both of which cannot be used to identify you uniquely. We do NOT store \
            any other personally identifiable data apart from the before mentioned email address."
        },
        {
            qs: "Can I give feedback or make feature requests?",
            ans: "Definitely! You can do so from the feedback page. It really helps us a lot."
        },
        {
            qs: "I found a problem in the site. How can I report it?",
            ans: "If you find a problem anywhere within the site, you can report it anonymously from the problem reporting page."
        },
    ];
    })
</script>

<?php
    require('./templates/footer.php');
?>