<?php

$subDomain = "www";

if ( isset($_SERVER['SERVER_NAME'])) {
    $subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];
}

if ($subDomain == 'www' || $subDomain == 'localhost' ) {
    $subDomain = 'dev';
}

$klassen=[];

if ($subDomain=='c20'){
    $klassen=['0A','0C','0D'];
}
if ($subDomain=='c21'){
    $klassen=['1A','1B','1C','1D'];
}
if ($subDomain=='c22'){
    $klassen=['2A','2B','2C'];
}
if ($subDomain=='c23'){
    $klassen=['3A','3B','3C'];
}

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'subDomain' => $subDomain,
    'klassen' => $klassen,
];
