<?php
header("HTTP/1.1 503 Service Temporarily Unavailable");
header("Status: 503 Service Temporarily Unavailable");
header("Retry-After: 3600");
?>
<!DOCTYPE HTML>
<html lang="nb-no">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=800">
    <meta name="description" content="">
    <meta name="author" content="Petahz.no - Kristoffer A. Iversen">
    <meta property="og:image" content="https://www.mannhullet.no/img/nth_16.9.jpg" >
    <link rel="icon" type="image/jpg" href="/img/favicon-32x32.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png">
    <link rel="manifest" href="/img/site.webmanifest">
    <link rel="mask-icon" href="/img/safari-pinned-tab.svg" color="#404d75">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">
    <title>Mannhullet.no</title>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
    <link href="https://fonts.googleapis.com/css?family=Economica|Source+Sans+Pro" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/css/datepicker3.css">
    <link rel="stylesheet" href="/css/stylesheet.css">
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="col-xs-4 nav"></div>
            <div class="col-xs-4 logo">
                <a href="/"><img src="/img/logo2.png" width="210" height="134" alt="Mannhullets logo" />
                </a>
                <h1>MANNHULLET</h1>
                <small>Linjeforeningen til Marin teknikk ved NTNU</small>
            </div>
            <div class="col-xs-4 user"></div>
        </div>
    </div>
    <div class="wrap">
        <div class="content">
            <div id="container" class="container">
                <ul id="mainnav" class="nav nav-pills mainnav">
                    <li class="logo"><a href="#" onclick="$('html, body').animate({scrollTop:0}, 'slow');return false;"><img src="/img/logo-small-black.png" height="40" alt="Mannhullet logo" /></a></li>
                    <li class="logo"><a href="#" onclick="$('html, body').animate({scrollTop:0}, 'slow');return false;"><img src="/img/logo-small-black.png" height="40" alt="Mannhullet logo" /></a></li>
                </ul>

                <h1>503 - Midlertidig utilgjengelig</h1>
                <h3>Mannhullet.no er under oppgradering</h3>
                <p>Vi er i gang med midlertidig vedlikehold! Burde ikke ta så alt for lang tid. Sjekk tilbake om en stund du.</p>
                <p>Hvis problemet varer lenger enn fire timer, ta kontakt med <s>legen</s> websjef på <a href="mailto:web@mannhullet.no">web@mannhullet.no</a></p>

            </div>
        </div>
        <div id="footer" style="opacity:0;" class="footer" data-bottom-top="opacity:0;" data-0-bottom-center="opacity:1;">
            <div class="container">
                <div style="padding-top:30px;" class="col-md-4">

                </div>
                <div class="col-md-4">
                    <h3>Takk for besøket</h3>
                    <br />
                    <a href="#" onclick="$('html, body').animate({scrollTop:0}, 'slow');return false;">
                        <span class="glyphicon glyphicon-arrow-up"></span><br />
                        Gå til toppen
                    </a>
                </div>
                <div style="padding-top:30px;" class="col-md-4">

                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <script src="/js/bootstrap.js"></script> <!-- Slightly modified bootstrap 3.2.0 that fixes a tooltip-/popover-placement issue (ref. https://github.com/twbs/bootstrap/commit/6b659a8f61cd0ff52e32af5eaa256e29a07e9050) -->
    <script src="/js/skrollr.min.js"></script>
    <script src="/js/bootstrap-datepicker.js"></script>
    <script src="/js/locales/bootstrap-datepicker.nb.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
