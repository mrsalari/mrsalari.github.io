<?php

// Include shoutbox class
require 'lib/shoutbox.class.php';

// Create new shoutbox class
$shoutbox = new Shoutbox();

// Check if it is an xmlhttprequest and post is send
if ($_POST && $shoutbox->isAjax()) {
    $message = trim($_POST['message']);
    if (isset($_POST['action']) && $_POST['action'] == 'post' && !empty($message)) {
        // Insert the new shout
        $shoutbox->dbInsert(
            array(
                'name',
                'message',
                'time',
                'ip'
            ),
            array(
                mb_substr($_POST['name'], 0, 64),
                mb_substr($_POST['message'], 0, 255),
                time(),
                $_SERVER['REMOTE_ADDR']
            )
        );
    }
    // Insert a new bot shout
    elseif (isset($_POST['action']) && $_POST['action'] == 'bot') {
        $shoutbox->dbInsert(
            array(
                'name',
                'message',
                'time',
                'ip'
            ),
            array(
                'Bot',
                mb_substr($_POST['name'], 0, 64) . ' enters the shoutbox',
                time(),
                $_SERVER['REMOTE_ADDR']
            )
        );
    }
}
elseif ($_GET && $shoutbox->isAjax()) {
    // Get new messages
    if (isset($_GET['action']) && $_GET['action'] == 'update') {
        $messages = $shoutbox->dbGetMessages($_GET['last']);
        if (!empty($messages)) {
            echo $messages;
        }
    }
    // Load the shoutbox
    elseif (isset($_GET['action']) && $_GET['action'] == 'loader') { ?>
        <div id="shoutbox-box-inner">
          <div id="shoutbox-box"></div>
          <form action="./" method="post" id="shoutbox-form">
            <div>
              <textarea rows="" cols="" name="message" id="shoutbox-message"></textarea>
              <input type="submit" id="shoutbox-submit" value="Shout" />
              <div id="shoutbox-message-counter"></div>
            </div>
          </form>
        </div>
    <?php }
}
