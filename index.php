<?php
//مقادیری که باید ارسال شوند
$data = http_build_query(
    array(
    'prompt' => 'ایا تو رباتی',
    'options' => ' '
    )
);
//تنظیم سربرگ های http
$http = array('http' =>
    array(
    'method'  => 'POST',
    'header'  => 'Content-type: application/x-www-form-urlencoded',
    'content' => $data
    )
);
//معتبر سازی با stream_context_create
$context = stream_context_create($http);
//ارسال درخواست و دریافت نتیجه
$result = file_get_contents('https://chatbot.theb.ai/api/chat-process', FALSE, $context);
?>
