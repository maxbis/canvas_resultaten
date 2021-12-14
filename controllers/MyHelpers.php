<?php

// this file contains my own helper that can be called static from any controller.
// e.g. MyHelpers::checkIP();

namespace app\controllers;

class MyHelpers
{
    public function checkIP() {
        if ( $_SERVER['REMOTE_ADDR'] == '::1' ){
            $remoteIP='178.84.73.55';
        } else {
            $remoteIP=$_SERVER['REMOTE_ADDR'];
        }
    
        $file = "../config/ipAllowed.txt";
    
        try { // read file and if not readble raise error and stop
            $lines = file($file);
         } catch (Exception $e) {
            $string = "Cannot acces IP Allowed file ($file) in config";
            writeLog($string);
            echo $string;
            exit;
         }
    
         $ipAllowed=[]; // all lines vlaidated will be put in this array
         for($i=0; $i<count($lines); $i++) {
            $ip = explode(' ',trim($lines[$i]))[0]; // we want teh first word
            if(filter_var(explode('/',$ip)[0], FILTER_VALIDATE_IP)) { // and we want anything beofre the / (note ip = xxx.xxx.xxx.xxx/xx)
                $ipAllowed[] = $ip; // ipnumber validate (note that subnet mask is not validated)
            }
            
         }
         //for($i=0; $i<count($ipAllowed); $i++) {
         //   $a =  self::ipRange($ipAllowed[$i]);
         //   d($a);
         //}

        $weAreOK=false;
        foreach ($ipAllowed as $item) {
            $ipRange = self::ipRange($item);
            if ( ip2long($remoteIP) >= ip2long($ipRange[0]) && ip2long($remoteIP) <= ip2long($ipRange[1]) ) {
                $string = $remoteIP.' - '.$ipRange[0].' - '.$ipRange[1];
                writeLog('IP-check OK: '.$string);
                $weAreOK=true;
            }
        }
        if ( $weAreOK == false ) {
            if ( self::countDown() ) {
                return;
            } else {
                $string = "Permission denied for ". $remoteIP;
                writeLog($string);
                sleep(2);
                echo $string;
                sleep(3);
                exit;
            }
        }
    }

    private function ipRange($cidr)
    {
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
        return $range;
    }

    private function countDown() {
        $file = "../config/allowCount.txt";
        if ( file_exists($file) ) {
            $accesCount = intval(file_get_contents($file, true));
            if ( empty($accesCount) ) {
                $accesCount=0;
            }
            $accesCount=$accesCount-1;
            file_put_contents($file, $accesCount);

            if ( $accesCount > 0 ) {
                return true;
            }  else {
                echo "#$accesCount - ";
                return false;
            }
        } else {
            echo "#NC - ";
            return false;
        }

    }
}
