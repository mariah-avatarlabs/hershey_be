<?php
include_once ('utils.php');

class UserManager {

    function UserManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;

        $this->postData = array(
            "firstname" => NULL,
            "lastname" => NULL,
            "email" => NULL,
            "prizeID" => NULL,            
        );

        if( isset($_POST['firstname']) ){
            $this->postData["firstname"] = encrypt(filter_var($_POST["firstname"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['lastname']) ){
            $this->postData["lastname"] = encrypt(filter_var($_POST["lastname"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['email']) ){
            $this->postData["email"] = encrypt(filter_var($_POST["email"], FILTER_SANITIZE_STRING));
        }
        if( isset($_POST['prizeID']) ){
            $this->postData["prizeID"] = filter_var($_POST["prizeID"], FILTER_VALIDATE_INT);
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
        $validData = TRUE;

	    //* Define expected data structure
        $data = array(
            'userCreated' => FALSE,
            'prizeID' => NULL
        );

        //* Check data is accounted for
        foreach ($this->postData as $key => $value) {
            if(is_null($value)){
                $validData = FALSE;
                $data['error'] = "INVALID ENTRY: " . $key;
            }
        }

        if($validData == TRUE){
            //* Query to create user with post data
            $query = "INSERT INTO Users (firstname, lastname, email, prize_id) VALUES (?, ?, ?, ?) ";
            
            //* Prepare and run query 
            $sql = $this->conn->prepare($query);
            $sql->bind_param("sssi", $this->postData["firstname"], $this->postData["lastname"], $this->postData["email"], $this->postData["prizeID"]);

            $result = $sql->execute();

            //* Filter data            
            if ($result) { 
                $data['userCreated'] = TRUE;
                $data['prizeID'] = $this->postData["prizeID"];
        
            } else {
                $data['error'] = "USER CREATE FAILED";
            }        
        }

        return $data;

    }

    /** 
     * exportToCSV() 
     * dumb DB info to csv for all users
     * * returns[download]: records decrypted for all registered users 
     *
    */
    public function exportToCSV(){
        $delimiter = ",";
        $filename = "users_" . date('Y-m-d') . ".csv";
        $f = fopen('php://memory', 'w');
        $fields = array('ID', 'first', 'last', 'email', 'prizeID');

        //* Query to get User table data
        $query = "SELECT * FROM Users ORDER BY id DESC";
        
        //* Prepare and run query 
        $sql = $this->conn->prepare($query);
        $result = $sql->execute();

        if($result){
            $result = $sql->get_result();

            //* Add page headers
            fputcsv($f, $fields, $delimiter);
            
            //* Add data per row [decrypted]
            while($row = $result->fetch_assoc()){
                $lineData = array($row['id'], decrypt($row['firstname']), decrypt($row['lastname']), decrypt($row['email']), $row['prize_id']);
                fputcsv($f, $lineData, $delimiter);
            }
            
            //* Move to firstline
            fseek($f, 0);
            
            //? set headers to download file rather than displayed
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');
            
            //* Output to file
            fpassthru($f);
        }
        
    }

}

?>