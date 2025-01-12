<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

        <title><?php echo SITE_TITLE; ?></title>

        <!-- Fonts -->
        <link href='https://fonts.googleapis.com/css?family=Lato:400,700|Oswald:400,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
         
        <!-- alpaca -->
        <link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
        <script type="text/javascript" src="/dist/js/alpaca.js"></script>

        <!-- Local Styles, if Any -->
        <?php if(isset($css) && !empty($css)){ print_styles($css); } ?>

        <link rel="stylesheet" href="/dist/css/main.css">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

<!-- Twitter and Open Graph cards -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@EngnRoom">
            <meta name="twitter:creator" content="@thomwithoutanh">
            <meta name="twitter:title" content="Alidade: an interactive tool that explains what to look for when using technology for social change.">
            <meta name="twitter:description" content="Choosing technology is hard. Alidade can help. Use Alidade to create a plan for finding technology tools that suit your social change project.">
            <meta name="twitter:image" content="https://alidade.tech/assets/images/alidade-social-card.png">

            <meta property="og:url" content="https://alidade.tech" />
            <meta property="og:type" content="website" />
            <meta property="og:title"              content="Alidade: an interactive tool that explains what to look for when using technology for social change." />
            <meta property="og:description"        content="Choosing technology is hard. Alidade can help. Use Alidade to create a plan for finding technology tools that suit your social change project." />
            <meta property="og:image" content="https://alidade.tech/assets/images/alidade-social-card.png" />
<!-- End Twitter and Open Graph cards -->
        
<!-- Matomo -->
<!--
<script type="text/javascript">
  var _paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//www.theengineroom.org/piwik/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '11']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
-->
<!-- End Matomo Code -->

    </head>
    <body class="<?php echo (isset($bodyClass) ? $bodyClass : ''); ?>">

        <?php /*
        <div class="wrap">

            <div class="container-fluid" id="top">
                <div class="row">
                    <div class="steps">
                        <div class="step step-1"></div>
                        <div class="step step-2"></div>
                        <div class="step step-3"></div>
                        <div class="step step-4"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="logo pull-left">TSA</div>
                    <?php if(isset($_SESSION[APPNAME][SESSIONKEY]) && !empty($_SESSION[APPNAME][SESSIONKEY])) { ?>
                        <div class="pull-right user-panel">
                            <span class="user-name">Hello, <strong><?php echo $_SESSION[APPNAME]['USR']; ?></strong>.</span>
                            <ul class="user-actions">
                                <li><a href="/" title="Homepage" class="mininav"><i class="fa fa-home fa-fw"></i><span class="sr-only">Homepage</span></a></li><!--
                                <?php if(isset($userRole) && $userRole == 'root') { ?>
                                --><li><a href="/manage/index" title="Manage Contents" class="mininav"><i class="fa fa-wrench fa-fw"></i><span class="hidden-xs">Manage</span></a></li><!--
                                <?php } ?>
                                --><li><a href="/user/projects" title="All your Projects" class="mininav"><i class="fa fa-tasks fa-fw"></i><span class="hidden-xs">My Projects</span></a></li><!--
                                --><li><a href="/user/logout" title="Logout" class="mininav"><i class="fa fa-sign-out fa-fw"></i><span class="sr-only">Logout</span></a></li><!-- -->
                            </ul>
                        </div>

                    <?php
                    }
                    ?>
                </div>
            </div> */  ?>
