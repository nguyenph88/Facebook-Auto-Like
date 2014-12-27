<!--
Simple script to manipulate the facebook graph API to automate the process of liking.
You need to have a list of token in your database first, then connect to the database
and start calling the api as below.
-->

<?php
if($_GET['like']){
	
	$id = $_GET['like'];

	require 'facebook.php';     // or change to your api file name
	
	$facebook = new Facebook(array(
	  'appId'  => $fb_app_id,
	  'secret' => $fb_secret
	));
	
	  $output = '';
	  $result = mysql_query("SELECT * FROM yourdatabase");
	   
	  if($result){
	      while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
						$token = $row['access_token'];
						$facebook->setAccessToken ($token);
						try {
								$facebook->api("/".$id."/likes", 'POST');
			      }	catch (FacebookApiException $e) {
			      		echo $e;
						}
				}
	}

	mysql_close($connection);
	
}
?>