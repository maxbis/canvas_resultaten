<?php

function subDomain() {
    $subDomain = "www";

    if ( isset($_SERVER['SERVER_NAME'])) {
        $subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];
    }

    if ( $subDomain == 'c23') {
        $DB='canvas-c23';
    } elseif ( $subDomain == 'c22') {
        $DB='canvas-c22';
    } elseif ( $subDomain == 'c21') {
        $DB='canvas-c21';
    } elseif ( $subDomain == 'c20') {
        $DB='canvas-c20';
    } else {
        $DB='canvas-c23';
    }

    # $DB='canvas';

    return $DB;
}

?>