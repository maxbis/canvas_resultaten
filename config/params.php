<?php

$subDomain = "www";

if ( isset($_SERVER['SERVER_NAME'])) {
    $subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];
}

if ($subDomain == 'www' || $subDomain == 'localhost' ) {
    $subDomain = 'c21';
}


return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'subDomain' => $subDomain,
];
