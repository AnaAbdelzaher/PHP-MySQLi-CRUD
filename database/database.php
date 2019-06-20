<?php

include '/../config.php';
class DB
{
    private $con = false; // Check to see if the connection is on or off
    private $myconn = ""; // This will be our mysqli object
    private $result = array(); // Any results from a query will be stored here
    private $myQuery =""; 

    // Function to make connection to database
    public function connect(){
		if(!$this->con){
			$this->myconn = new mysqli(DATABASE_HOST_NAME, DATABASE_USER_NAME, DATABASE_PASSWORD, DATABASE_DB_NAME);  // mysql_connect() with variables defined at the start of Database class
            $this->myconn->set_charset("utf8");
            if($this->myconn->connect_errno > 0){
                //array_push($this->result,$this->myconn->connect_error);
                return false; // Problem selecting database return FALSE
            }else{
                $this->con = true;
                return true; // Connection has been made return TRUE
            } 
        }else{  
            return true; // Connection has already been activated return TRUE 
        }  	
	}
	
 	// Function to disconnect from the database
    public function disconnect(){
    	// If there is a connection to the database
    	if($this->con){
    		// We have found a connection, try to close it
    		if($this->myconn->close()){
                // echo "closed";
                // We have successfully closed the connection, set the connection variable to false
    			$this->con = false;
				// Return true tjat we have closed the connection
				return true;
			}else{
				// We could not close the connection, return false
				return false;
			}
		}
    }

    // function to delete row or drop table
    public function deleteAll($table){
        if($this->tableExist($table)){
            $deleteall='DELETE FROM '.$table;
            $this->ExecuteQuery($deleteall);

        }else{
            return false;  //table doesnot exist
        }
        
    }
    public function dropTable($table){
        if($this->tableExist($table)){
            $drop='DROP TABLE '.$table;
            $this->ExecuteQuery($drop);


        }else{
            return false;  //table doesnot exist
        }
        
    }
    public function deleteRow($table,$where){
        if($this->tableExist($table)){
            $deleterow='DELETE FROM '.$table.' WHERE  '.$where;
            $this->ExecuteQuery($deleterow);
        }else{
            return false;  //table doesnot exist
        }
        
    }

    //update function to update row in database
    public function update($table,$params=array(),$where){   
        if ($this->tableExist($table)) {
            $items=array();//array for all column which will be updated
            foreach ($params as $key=>$value) {
                $items[]=$key.'="'.$value.'"';
            }
            //update Query
            $sqlUpdate='UPDATE ' . $table . ' SET ' . implode(',', $items) . ' WHERE ' . $where;
            $this->ExecuteQuery($sqlUpdate);

        }else{
            return false;
        }
    }
    //insert function to certain table
    public function insert($table,$params=array()){
    	// Check to see if the table exists
    	 if($this->tableExist($table)){
    	 	$sqlInsert='INSERT INTO '.$table.' (' . implode(',',array_keys($params)) . ') VALUES ("' . implode('", "', $params) . '")';
            
            //InsertRow to the database
            $this->ExecuteQuery($sqlInsert);

        }else{
        	return false; // Table does not exist
        }
    }
    // select what you want from any table 
    public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null){
		// Create query from the variables passed to the function
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null){
			$q .= ' JOIN '.$join;
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        if($limit != null){
            $q .= ' LIMIT '.$limit;
        }
        
        $this->myQuery = $q; // Pass back the SQL
		// Check to see if the table exists
        if($this->tableExist($table)){
        	// The table exists, run the query
        	$query = $this->myconn->query($q);    
            $this->FetchResult($query);
            
      	}else{
      		return false; // Table does not exist
    	}
    }
    //select All rows from specific table 
    public function selectALl($table){
        if($this->tableExist($table)){
            // check The table exists 
            $selectAll='SELECT * FROM '.$table;
            $this->myQuery = $selectAll; // Pass back the SQL Query
            $query = $this->myconn->query($selectAll);   
            $this->FetchResult($query);
			
      	}else{
      		return false; // Table does not exist
    	}
        


    }





    private function ExecuteQuery($sqlQuery){
        if ($sql = $this->myconn->query($sqlQuery)) {
            array_push($this->result, $this->myconn->affected_rows);
            $this->myQuery = $sqlQuery; // Pass back the SQL
            return true; // The query exectued correctly
        } else {
            array_push($this->result, $this->myconn->error);
            return false; // The query did not execute correctly
        }   


    }
    private function FetchResult($query){
        if($query){
            // If the query returns results  >= 1 assign the number of rows to numResults
            $this->numResults = $query->num_rows;
            // Loop through the query results by the number of rows returned
            for($row = 0; $row < $this->numResults; $row++){
                $resultReturned = $query->fetch_array(); //fetch result as associative array
                $Coloumnkey = array_keys($resultReturned);// collect all name of coloumns at array 
                for($indexColoum = 0; $indexColoum < count($Coloumnkey); $indexColoum++){
                    
                    if(is_string($Coloumnkey[$indexColoum])){
                        if($query->num_rows >= 1){
                            $this->result[$row][$Coloumnkey[$indexColoum]] = $resultReturned[$Coloumnkey[$indexColoum]];
                        }else{
                            $this->result[$row][$Coloumnkey[$indexColoum]] = null;
                        }
                    }
                }
            }
            return true; // Query was successful
        }else{
            array_push($this->result,$this->myconn->error);
            return false; // No rows returned
        }

    }

    public function tableExist($table){
		$tablesInDB = $this->myconn->query('SHOW TABLES FROM '.DATABASE_DB_NAME.' LIKE "'.$table.'"');
        if($tablesInDB){
        	if($tablesInDB->num_rows == 1){
                return true; // The table exists
            }else{
            	array_push($this->result,$table." does not exist in this database");
                return false; // The table does not exist
            }
        }
        else{
            array_push($this->result," An Error occurs during process ");
        }
    }
    // Public function to return the data to the user
    public function getResult(){
        return $this->result;
    }
    //Pass the SQL back for debugging
    public function getSql(){
        return $this->myQuery;
    }








    

}
