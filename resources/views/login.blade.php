<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>SheridanEsports Login</title>
        <meta name="description" content="">
        <meta name="author" content="Judd A.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
		<link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <style>
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
        <link rel="stylesheet" href="css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/mobile/mobile-large.css">
		<link rel="stylesheet" href="css/mobile/mobile-small.css">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    </head>
    <body>
        <div class="background">
            <img src="images/SheridanBruinsLogo3.png" alt="Bruins Logo">
            <h3 class="logo"><strong>SHERIDAN ESPORTS.</strong></h3>
        </div>
        <div class="mainPageForm">
            <!-- <form action="/action_page.php" method="post",action Specifies
             where to send the form-data when a form is submitted; method
             specifies how to send form. post is for sending sensitive data. -->
            <form class="loginForm" method="post">
                <div style="text-align:center" class="title">SHERIDAN BRUINS</div>
                <input type="text" name="username" placeholder="Summoner Name" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
			    <div style="text-align:center">
                    <button id="signinBtn" class="btn btn-primary">Sign in</button>
                    <button id="registerBtn" class="btn btn-primary">Register</button> <!-- add onclick attribute, open new form -->
			    </div>
            </form>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

        <script src="js/vendor/bootstrap.min.js"></script>

        <script src="js/main.js"></script>

        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            e.src='//www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            ga('create','UA-XXXXX-X','auto');ga('send','pageview');
        </script>
    </body>
</html>
