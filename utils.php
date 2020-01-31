<?php

function generateTimestamp(){
    $date = new DateTime('now');
    $date->setTimezone(new DateTimeZone('America/Vancouver'));
    return $date->format('Y-m-d H:i:s');
    
}

function encryptData($dataString){
    //https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
    $ciphering = "AES-128-CTR"; 
    $encryption_iv = '1234567891011121'; 

    $iv_length = openssl_cipher_iv_length($ciphering); 

}

function determineProbability($prizesWon, $totalPrizes)
{
    $odds = $totalPrizes - $prizesWon;
    $odds = $odds / $totalPrizes;
    $odds = $odds * 100;

    //https://stackoverflow.com/questions/9252671/how-do-i-execute-one-event-in-php-based-on-a-probability-for-the-event-to-happen
    if (rand(1,100) <= $odds){
        // echo 1;
        return TRUE;
    } else {
        // echo 0;
        return FALSE;

    }

}

function decryptData($dataString){

}

function hasError($data){
    if(array_key_exists('error', $data)){
        return TRUE;
    } else {
        return FALSE;
    }

}

?>