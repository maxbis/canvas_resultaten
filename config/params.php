<?php

$subDomain = "www";

if ( isset($_SERVER['SERVER_NAME'])) {
    $subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];
}

if ($subDomain == 'www' || $subDomain == 'localhost' ) {
    $subDomain = 'c21';
}

$klassen=[];

if ($subDomain=='c21'){
    $klassen=['1A','1B','1C','1D'];
}
if ($subDomain=='c22'){
    $klassen=['2B','2C','2D'];
}


return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'subDomain' => $subDomain,
    'klassen' => $klassen,
];
