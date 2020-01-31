<?php

/** 
 * generateTimestamp() 
 * returns formatted datestamp based off of greenwhich timezone and offset by 'America/Vancouver' timezone
 * * returns: date:string = formatted string representing current time
 * 
 * ? need to confirm actual timezone for contest
 * TODO: confirm get_result() call
*/
function generateTimestamp(){
    $date = new DateTime('now');
    $date->setTimezone(new DateTimeZone('America/Vancouver'));
    return $date->format('Y-m-d H:i:s');
    
}

/** 
 * determineProbability() 
 * updates prize `time_won` or `time_claimed` property based off of ID and update type.
 * @param prizesWon:int = number of prizes already won
 * @param totalPrizes:int = total prizes allocated for the day
 * * returns: boolean determining if player won(TRUE) or not(FALSE)
 * 
 * TODO: modify odds based on time of day
 * TODO: modify total prizes based off of remaining prizes?
*/
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

/** 
 * hasError() 
 * returns boolean representing of data has record of an error; marker to proceed or not;
 * @param data:array = data object created based off query
 * * returns: boolean determining if previous call has error or not
 * 
*/
function hasError($data){
    if(array_key_exists('error', $data)){
        return TRUE;
    } else {
        return FALSE;
    }

}


function decryptData($dataString){

}

function encryptData($dataString){
    //https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
    $ciphering = "AES-128-CTR"; 
    $encryption_iv = '1234567891011121'; 

    $iv_length = openssl_cipher_iv_length($ciphering); 

}


?>