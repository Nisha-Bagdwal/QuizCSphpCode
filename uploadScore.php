<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['email'])&& isset($_POST['score'])&& isset($_POST['subject'])) {
 
    // receiving the post params
    $email = $_POST['email'];
	$score = $_POST['score'];
	$subject = $_POST['subject'];
 
    // get the user by email and password
    $result = $db->uploadScore($email,$score,$subject);
	
	if($result!=false){
		$response["error"] = FALSE;
		echo json_encode($response);
	}else{
		$response["error"] = TRUE;
        $response["error_msg"] = "Cannot update score on server!";
        echo json_encode($response);
	}
 
} else {
    // required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter is missing!";
    echo json_encode($response);
}
?>