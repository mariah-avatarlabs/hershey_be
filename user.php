<?php

class UserManager {

    function UserManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;
    }

    public function create(){
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $email = $_POST["email"];
        $prizeID = intval($_POST["prizeID"]);
        $data = array(
            'userCreated' => FALSE,
        );
        
        $query = "INSERT INTO Users (firstname, lastname, email, prize_id) VALUES (?, ?, ?, ?) ";

        $sql = $this->conn->prepare($query);
        $sql->bind_param("sssi", $firstname, $lastname, $email, $prizeID);
        $result = $sql->execute();

        if ($result) { 
            $data['userCreated'] = TRUE;
            echo json_encode($data);
    
        } else {
            //QUESTION - double check
            $data['error'] = $sql->error;
        }

        return $data;

    }

}

?>