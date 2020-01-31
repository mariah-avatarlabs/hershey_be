<?php

class UserManager {

    function UserManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;

        $this->firstname = filter_var($_POST["firstname"], FILTER_SANITIZE_STRING);
        $this->lastname = filter_var($_POST["lastname"], FILTER_SANITIZE_STRING);
        $this->email = filter_var($_POST["email"], FILTER_SANITIZE_STRING);
        $this->prizeID = filter_var($_POST["prizeID"], FILTER_SANITIZE_STRING);

    }

    public function create(){

        $query = "INSERT INTO Users (firstname, lastname, email, prize_id) VALUES (?, ?, ?, ?) ";

        $sql = $this->conn->prepare($query);
        $sql->bind_param("sssi", $this->firstname, $this->lastname, $this->email, intval($this->prizeID));
        $result = $sql->execute();

        if ($result) { 
            $data['userCreated'] = TRUE;
            $data['prizeID'] = $this->prizeID;
    
        } else {
            //QUESTION - double check
            $data['error'] = $sql->error;
        }

        return $data;

    }

}

?>