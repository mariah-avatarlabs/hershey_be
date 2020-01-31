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

        // if( isset($_POST['firstname']) ){
        //     $this->firstname = encrypt(filter_var($_POST["firstname"], FILTER_SANITIZE_STRING));
        // }
        // if( isset($_POST['lastname']) ){
        //     $this->lastname = encrypt(filter_var($_POST["lastname"], FILTER_SANITIZE_STRING));
        // }
        // if( isset($_POST['email']) ){
        //     $this->email = encrypt(filter_var($_POST["email"], FILTER_SANITIZE_STRING));
        // }
        // if( isset($_POST['prizeID']) ){
        //     $this->prizeID = filter_var($_POST["prizeID"], FILTER_SANITIZE_STRING);
        // }                        

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