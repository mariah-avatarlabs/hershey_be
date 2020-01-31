<?php


class PrizeManager {

    function PrizeManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;
    }

    public function canWin(){
        $dailyLimit = 5;
        $timeStart = "00:00:00";
        $timeEnd = "23:59:59";
        $baseDate = substr($this->dateStamp, 0, -8);
        
        $startDate = $baseDate . $timeStart;
        $endDate = $baseDate . $timeEnd;
        
        $data = array(
            'hasWon' => FALSE,
        );
        
        $query = "SELECT COUNT(*) FROM Prizes WHERE time_claimed BETWEEN (?) AND (?)";    
        
        $sql = $this->conn->prepare($query);
    	$sql -> bind_param("ss", $startDate, $endDate);
        $result = $sql->execute();
        $result = $sql->get_result();
        
        if ($result) { 	
            $count = $result -> fetch_row();
            $count = $count[0];

            if($count < $dailyLimit){
                $data['hasWon'] = TRUE;
            } else {
                $data['hasWon'] = FALSE;
            }

        } else {
            $data['error'] = $sql->error;
        }

        return $data;

    }

    public function update($type, $ID){
        $data = array(
            'updated' => FALSE,
        );
            
        switch ($type) {
            case 'won':
                $query = "UPDATE `Prizes` 
                    SET `time_won` = (?) 
                    WHERE `id` = (?)";
                break;

            case 'claimed':
                $query = "UPDATE `Prizes` 
                            SET `time_claimed` = (?) 
                            WHERE `id` = (?)";
                break;

           default:
                $query = "UPDATE `Prizes` 
                            SET `time_won` = (?) 
                            WHERE `id` = (?)";
                break;
        }


        
        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $this->dateStamp, $ID);

        if ($sql -> execute()) { 
            $data['updated'] = TRUE;

        } else {
            $data['error'] = $sql->error;
        }

        return $data;

    }

    public function retrieveAvailablePrize(){
        // REFACTOR - pull into util??
        $baseTime = Datetime::createFromFormat('Y-m-d H:i:s', $this->dateStamp);
        $baseTime = $baseTime->modify('-10 minutes');
        $endTime = $baseTime->format('Y-m-d H:i:s');
            
        $data = array(
            'prize' => null,
        );
    
        $query = "SELECT * FROM `Prizes` 
                    WHERE `time_claimed` IS NULL 
                    AND `time_won` BETWEEN (?) AND (?) 
                    OR `time_won` IS NULL 
                    LIMIT 1";
        

        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $endTime, $this->dateStamp);
    
        $result = $sql -> execute();

            
        if ($result) { 	
            // REFACTOR - CHECK ONE RECORD??
            $result = $sql->get_result();

            while ($row = $result->fetch_array(MYSQLI_ASSOC)){
                $data['prize'] = $row;
                // echo json_encode($row);
           }
    
        } else {
            $data['error'] = $sql->error;
            // echo "int err";

        }
    
        return $data;
    
    
    }

    
}






?>