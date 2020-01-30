<?php

function hasError($data){
    if(array_key_exists('error', $data)){
        return TRUE;

    } else {
        return FALSE;

    }

}

?>