<?php

    require 'lib/shoutbox.class.php';
    
    $shoutbox = new Shoutbox();
    $shoutbox->sendContentTypeHeader('text/html');
  
?><!doctype html>
<html lang="en">
    <head>
        <title>Shoutbox</title>
        <meta charset="<?php echo $shoutbox->getCharset(); ?>" />
        <?php if ($shoutbox->isMobile()) : ?>
        <meta name="viewport" content="width=device-width" />
        <?php endif; ?>
        
        <link rel="stylesheet" type="text/css" media="screen" href="media/css/main.css" />
        <link rel="shortcut icon" type="image/png" href="media/images/favicon.png" />
        <?php if ($shoutbox->isMobile()) : ?>
        <link rel="stylesheet" type="text/css" media="screen" href="media/css/mobile.css" />
		<link rel="canonical" href="http://www.20script.ir" />
        <?php endif; ?>
        
        <script src="media/js/jquery.js"></script>
        <script src="media/js/jquery.placeholder.js"></script>
        <script src="media/js/jquery.charcounter.js"></script>
        <script src="media/js/shoutbox.js"></script>
        <script>
            Shoutbox.refreshRate = <?php echo $shoutbox->getRefreshRate() * 1000; ?>;
            Shoutbox.spamProtectionRate = <?php echo $shoutbox->getSpamProtectionRate(); ?>;
        </script>
        <base href="<?php echo $shoutbox->getBaseURL(); ?>" />
    </head>
    <body>
        <div id="wrapper">
            <div id="shoutbox">
                <h1><img src="media/images/icon-shoutbox.png" alt="Shoutbox" width="32" height="32" /> <?php echo $shoutbox->getHeading(); ?></h1>
                <div id="shoutbox-box-wrapper">
                    <div id="shoutbox-box-login-wrapper">
                        <div id="shoutbox-enter-name">
                            <p>Welcome, please enter your name:</p>
                            <p><input type="text" name="name" id="shoutbox-name" value="" data-prepopulate="<?php echo $shoutbox->getPrepopulateName(); ?>" /></p>
                            <p><input type="submit" id="shoutbox-name-submit" value="Login" /></p>
                        </div>
                        <div id="shoutbox-loader"><img src="media/images/loader.gif" alt="Loading" width="32" height="25" /></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>