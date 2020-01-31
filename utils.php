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

/** 
 * addSalt() 
 * adds MD5 hash to data
 * @param data:string = data to be encrypted for DB
 * * returns:string salted data to be encrypted
 * 
*/
function addSalt($data){
    $chars = '5ecc04a77b57809c69f09721b8cf76ac';
    return $data .= $chars;
}

/** 
 * removeSalt() 
 * removes MD5 hash from data
 * @param data:string = data to be returned decrypted
 * * returns:string data decrypted and de-salted
 * 
*/
function removeSalt($data){
    $chars = '5ecc04a77b57809c69f09721b8cf76ac';
    return substr($data, 0, -strlen($chars));
}

/** 
 * decryptData() 
 * decrypt data
 * @param data:string = encrypted data with salt
 * * returns:string data decrypted without salt
 * 
*/
function decryptData($dataString){
    $key = "testing"; 
    $ciphering = "AES-128-CTR"; 
    $iv_length = openssl_cipher_iv_length($ciphering); 
    $decryption_iv = '1234567891011121'; 

    $decryption=openssl_decrypt ($dataString, $ciphering,  
        $key, 0, $decryption_iv); 

    return removeSalt($decryption);

}

/** 
 * encryptData() 
 * encrypt data
 * @param data:string = data to be encrypted
 * * returns:string data encrypted with salt
 * 
*/
function encryptData($dataString){
    $key = "testing"; 
    $ciphering = "AES-128-CTR"; 
    $iv_length = openssl_cipher_iv_length($ciphering); 
    $encryption_iv = '1234567891011121'; 

    $encryption = openssl_encrypt(addSalt($dataString), $ciphering, 
    $key, 0, $encryption_iv); 
    return $encryption;
}


?>