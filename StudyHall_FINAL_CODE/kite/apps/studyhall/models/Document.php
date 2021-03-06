<?php
/**
*	@author Kristen Massey
**/

        // put your code here
include_once "db_functions.php";

class Document{
    //variable declaration
    private $doc_id;
    private $username;
    private $class_name;
    private $subject;
    private $doc_name;
    private $doc_type;
    private $path_to_doc;
    private $upvotes;
    private $downvotes;
    private $blocked;
    
    private $new_doc;
    
    //constructor
    function __construct($doc_id = 0){
        //check if doc_id is 0
        if($doc_id == 0){ 
            $this->new_doc = true;
            return;
        }
        //get doc values from database and set
        else {
            $this->new_doc = false;
            //conn to database
            $db_vals = db_get("Document", "*", "doc_id", sprintf("'%d'", $doc_id));
            $this->doc_id = $doc_id;
            $this->username = $db_vals['username'];
            $this->class_name = $db_vals['class_name'];
            $this->subject = $db_vals['subject'];
            $this->doc_name = $db_vals['doc_name'];
            $this->doc_type = $db_vals['doc_type'];
            $this->path_to_doc = $db_vals['path_to_doc'];
            $this->upvotes = $db_vals['upvotes'];
            $this->downvotes = $db_vals['downvotes'];
            $this->blocked = $db_vals['blocked'];
            return;
        }
    }
    function __destruct(){
        
    }
    
    public function createDocument($username, 
                        $class_name,
                        $subject,
                        $doc_name,
                        $doc_type,
                        $path_to_doc){


        //get random doc_id
        $doc_id = get_rand_num();
        while(value_exists("Document", "doc_id", $doc_id)){
            $doc_id = get_rand_num();
        }

        
        $int = 1;
        //get all documents where doc_name similar to $doc_name AND path_to_doc==$path_to_doc
        $query_string = sprintf("SELECT * FROM `Document` WHERE doc_name LIKE '%s%%' AND path_to_doc='%s';", mysql_escape_string($doc_name), mysql_escape_string($path_to_doc));
        $data = get_query($query_string);
            if(isset($data)){
                //put results into an array
                
                $doc_list = array();
                while( $row = $data->fetch_assoc()){
                    array_push($doc_list, $row['doc_name']);
                }
                $int = 0;
                $new_doc_name = $doc_name;
                while($int < $data->num_rows){
                    if(strcmp($doc_list[$int], $new_doc_name) == 0){
                        $new_doc_name = sprintf("%s%d", $doc_name, $int);
                        $int = 0;
                    }
                    else{
                        $int++;
                    }
                }
            }else{
                $new_doc_name = $doc_name;
            }

            
        db_add("Document", sprintf("'%d', '%s', '%s', '%s', '%s', '%s', '%s', '0', '0', 'false'", $doc_id, $username, mysql_escape_string($class_name), $subject, mysql_escape_string($new_doc_name), $doc_type, mysql_escape_string($path_to_doc)));
        
        $this->doc_name = $new_doc_name;
        $this->username = $username;
        $this->class_name = $class_name;
        $this->subject = $subject;
        $this->doc_type = $doc_type;
        $this->path_to_doc = $path_to_doc;
        $this->doc_id = $doc_id;
        $this->blocked = false;
        $this->upvotes = 0;
        $this->downvotes = 0;
    }
    
    public function deleteDocument(){
        //check for error, return value to user based on if error or not
        db_delete("Document", "doc_id", $this->doc_id);
        return;
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function displayDocComments($doc_id){
    	$commentsList=NULL;
	$db_conn = db_conn();
        $query_string = "SELECT * from Comment where document_id=".$doc_id;
        $results = mysqli_query($db_conn, $query_string);
        //$result = $result->fetch_assoc();
        $commentsList = array();
        while($result = $results->fetch_assoc()) {
        	if(($result['blocked']==NULL) || ($result['blocked']==0)){ //if not blocked
        		array_push($commentsList, $result);
        		//echo $doc['doc_id'].'<br>';
        	}
        	else{
        		// do nothing
        	}
        	
        }        
        
        mysqli_close($db_conn);
        //var_export($docList);
        return $commentsList;
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function deleteDocumentByDocId($doc_id){
    	//echo 'function called';
        $db_conn = db_conn();
        
        // sql to delete a record
	$sql = "DELETE FROM Document WHERE doc_id=".$doc_id;
	
	if (mysqli_query($db_conn, $sql)) {
	    echo "Record deleted successfully";
	} else {
	    echo "Error deleting record: " . mysqli_error($db_conn);
	}
	
	mysqli_close($db_conn);
        
        return;
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function likeDocByPath($path, $name){
    	session_start();
    	$db_conn = db_conn();
    	
    	$sql = "UPDATE Document SET upvotes = (upvotes + 1) WHERE path_to_doc='".$path."' AND doc_name = '".$name."' AND doc_id= ".$_SESSION['doc_id'];

	if (mysqli_query($db_conn, $sql)) {
	    
	} else {
	    echo "Error updating record: " . mysqli_error($db_conn);
	}
	
	mysqli_close($db_conn);
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function getDislikesById($doc_id){
	session_start();
	$db_conn = db_conn();
        $query_string = "SELECT * from Document where doc_id=".$doc_id;
        $results = mysqli_query($db_conn, $query_string);
        //$result = $result->fetch_assoc();
        
        while($result = $results->fetch_assoc()) {
        	if(($result['blocked']==NULL) || ($result['blocked']==0)){ //if not blocked
        		$_SESSION['downvotes'] = $result['downvotes'];
        	}
        	else{
        		// do nothing
        	}
        	
        }        
        
        mysqli_close($db_conn);
    
    }
    
    public function getlikesById($doc_id){
	session_start();
	$db_conn = db_conn();
        $query_string = "SELECT * FROM Document WHERE doc_id=".$doc_id;
        $results = mysqli_query($db_conn, $query_string);
        //$result = $result->fetch_assoc();
        
        while($result = $results->fetch_assoc()) {
        	if(($result['blocked']==NULL) || ($result['blocked']==0)){ //if not blocked
        	//echo found;
        		$_SESSION['upvotes'] = $result['upvotes'];
        		//echo ' upvotes '.$_SESSION['upvotes'] ;
        	}
        	else{
        		// do nothing
        	}
        	
        }        
        
        mysqli_close($db_conn);
    
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function dislikeDocByPath($path, $name){
    	session_start();
    	$db_conn = db_conn();
    	
    	$sql = "UPDATE Document SET downvotes = (downvotes + 1) WHERE path_to_doc='".$path."' AND doc_name = '".$name."' AND doc_id= ".$_SESSION['doc_id'];

	if (mysqli_query($db_conn, $sql)) {
	    
	} else {
	    echo "Error updating record: " . mysqli_error($db_conn);
	}
	
	mysqli_close($db_conn);
    }
    
    
    /**
    *@author Armand Nokbak
    **/
    public function blockDocbyId($doc_id){
    	$db_conn = db_conn();
    	
    	$sql = "UPDATE Document SET blocked=now() WHERE doc_id=".$doc_id;

	if (mysqli_query($db_conn, $sql)) {
	    
	} else {
	    echo "Error updating record: " . mysqli_error($db_conn);
	}
	
	mysqli_close($db_conn);
    }
    
    /**
    *@author Armand Nokbak
    **/
    public function unblockDocbyId($doc_id){
    	$db_conn = db_conn();
    	
    	$sql = "UPDATE Document SET blocked=NULL WHERE doc_id=".$doc_id;

	if (mysqli_query($db_conn, $sql)) {
	    
	} else {
	    echo "Error updating record: " . mysqli_error($db_conn);
	}
	
	mysqli_close($db_conn);
    }
    
    public function rate($updown){
        //downvotes
        if($updown == 0){
            $new_val = $this->downvotes + 1;
            db_set("Document", sprintf("downvotes='%d'", $new_val), "doc_id", $this->doc_id);
            $this->downvotes = $new_val;    
        }
        //upvotes
        if($updown == 1){
            $new_val = $this->upvotes + 1;
            db_set("Document", sprintf("upvotes='%d'", $new_val), "doc_id", $this->doc_id);
            $this->upvotes = $new_val;
        }
    }
    
    public function block(){
        db_set("Document", "blocked='true'", "doc_id", $this->doc_id);
        $this->blocked = true;
    }
    
    public function unblock(){
        db_set("Document", "blocked='false'", "doc_id", $this->doc_id);
        $this->blocked = false;
    }
    
    public function isBlocked(){
        $db_val = db_get("Document", "blocked", "doc_id", $this->doc_id);
        $blocked_bool = $db_val["blocked"];
        return $blocked_bool;
    }
    
    public function getDocID(){
        return $this->doc_id;
    }
    
    public function getUsername(){
        return $this->username;
    }
    public function getClassName(){
        return $this->class_name;
    }
    public function getSubject(){
        return $this->subject;
    }
    public function getDocName(){
        return $this->doc_name;
    }
    public function getDocType(){
        return $this->doc_type;
    }
    public function getPathToDoc(){
        return $this->path_to_doc;
    }
    public function getUpvotes(){
        return $this->upvotes;
    }
    public function getDownvotes(){
        return $this->downvotes;
    }
}
?>