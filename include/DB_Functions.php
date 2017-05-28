<?php
 
 
class DB_Functions {
 
    private $conn;
 
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    // destructor
    function __destruct() {
         
    }
 
    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $email, $password) {
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
 
        $stmt = $this->conn->prepare("INSERT INTO users(name, email, encrypted_password, salt, created_at) VALUES(?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $encrypted_password, $salt);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
			$stmt->bind_result($id,$name, $email,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$sub10,$sub11,$score,$encrypted_password,$salt, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
 
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $stmt->bind_result($id,$name, $email,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$sub10,$sub11,$score,$encrypted_password,$salt, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
            $user["created_at"] = $created_at;
			$user["salt"]=$salt;
			$user["encrypted_password"]=$encrypted_password;
            $stmt->close();
 
            // verifying user password
            $salt = $user["salt"];
            $encrypted_password = $user["encrypted_password"];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }
 
    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT email from users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }
 
 
	public function loadQuestionsBySubject($subject,$id){
		$sql="SELECT * FROM `$subject` WHERE id=$id";
		$result = mysqli_query($this->conn, $sql);
		mysqli_close($this->conn);
		return $result;
	}
	
	public function uploadScore($email,$score,$subject){
		$stmt = $this->conn->prepare("UPDATE users SET `$subject` = ? WHERE email = ?");
        $stmt->bind_param("ss",$score, $email);
        $stmt->execute();
		$stmt->close();
		
		$stmt = $this->conn->prepare("UPDATE users SET score = `Algorithms`+`Compiler Design`+`Computer Networks`+`Computer Organization`+`Data Structures`+
		`DBMS`+`Discrete Structures`+`FLAT`+`Microprocessor`+`Operating Systems`+`Programming Languages` WHERE email = ?");
		$stmt->bind_param("s", $email);
		$result=$stmt->execute();
		$stmt->close();
		return result;
	}
	
	public function loadScoresByEmail($email){
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $stmt->bind_result($id,$name, $email,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$sub10,$sub11,$score,$encrypted_password,$salt, $created_at);
            $stmt->fetch();
            $user = array();
            $user["algo"] = $sub1;
			$user["cd"] = $sub2;
            $user["cn"] = $sub3;
			$user["co"] = $sub4;
			$user["datas"] = $sub5;
			$user["dbms"] = $sub6;
			$user["diss"] = $sub7;
			$user["flat"] = $sub8;
            $user["micro"] = $sub9;
			$user["os"] = $sub10;
			$user["pl"] = $sub11;
			$user["score"] = $score;
			$stmt->close();
            return $user;
        } else {
            return NULL;
        }
	}
	
	public function getRankByEmail($email){
		/*$sql = "SELECT score, FIND_IN_SET( score, ( SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM users )) AS rank FROM `users` WHERE email=$email";
        $result = mysqli_query($this->conn, $sql);
		mysqli_close($this->conn);
		return $result;*/
		
		//$stmt = $this->conn->prepare("SELECT score, FIND_IN_SET( score, ( SELECT GROUP_CONCAT( score ORDER BY score DESC) FROM users ) ) AS rank FROM `users` WHERE email=?");
	$stmt = $this->conn->prepare("SELECT s.score,count(*)  AS rank 
FROM users p CROSS JOIN
     (SELECT id,score FROM users WHERE email=?) s
WHERE p.score > s.score or
      (p.score = s.score and p.id <= s.id)");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $stmt->bind_result($score,$rank);
            $stmt->fetch();
            $user = array();
            $user["score"] = $score;
			$user["rank"] = $rank;
			$stmt->close();
            return $user;
        } else {
            return NULL;
        }
	}
	public function showLeaderboard(){
		$sql="SELECT name,email,score FROM `users` ORDER BY score DESC LIMIT 10";
		$result = mysqli_query($this->conn, $sql);
		mysqli_close($this->conn);
		return $result;
	}
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
 
}
 
?>