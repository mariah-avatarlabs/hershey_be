<?php
include_once ('utils.php');

class UserManager {

    function UserManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;

        $this->firstname = "";
        $this->lastname = "";
        $this->email = "";
        $this->prizeID = "";

        if( isset($_POST['firstname']) ){
            $this->firstname = encrypt(filter_var($_POST["firstname"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['lastname']) ){
            $this->lastname = encrypt(filter_var($_POST["lastname"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['email']) ){
            $this->email = encrypt(filter_var($_POST["email"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['prizeID']) ){
            $this->prizeID = filter_var($_POST["prizeID"], FILTER_SANITIZE_STRING);
        }                        

    }

    /** 
     * create() 
     * creates new user record in DB
     * * returns: 
        * * $data:array ["userCreated":boolean, "prizeID":int ]
        * * $data:array ["error":string]
     *
    */
    public function create(){
	    //* Define expected data structure
        $data = array(
            'userCreated' => FALSE,
            'prizeID' => NULL
        );
        
        //* Query to create user with post data
        $query = "INSERT INTO Users (firstname, lastname, email, prize_id) VALUES (?, ?, ?, ?) ";
        
        //* Prepare and run query 
        $sql = $this->conn->prepare($query);
        $sql->bind_param("sssi", $this->firstname, $this->lastname, $this->email, intval($this->prizeID));

        $result = $sql->execute();

        //* Filter data            
        if ($result) { 
            $data['userCreated'] = TRUE;
            $data['prizeID'] = $this->prizeID;
    
        } else {
            $data['error'] = "USER CREATE FAILED";
        }        

        return $data;

    }

}

?>