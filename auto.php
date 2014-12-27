<!--
Simple script to manipulate the facebook graph API to automate the process of liking.
You need to have a list of token in your database first, then connect to the database
and start calling the api as below.

Another way not using the api file but instead calling graph api directly
-->
<?php
if(isset($_POST['like'])){
$id = $_POST['id'];
$limit = $_POST['limit'];

$query= mysql_query('select * from Likers ORDER BY RAND() DESC LIMIT '.$limit);
while($result = mysql_fetch_array($edwin)){
$me = json_decode(file_get_contents('https://graph.facebook.com/me?&access_token='.$result['access_token' ]),true);

$com = 'https://graph.facebook.com/'.$id.'/likes?method=post&access_token='.$result['access_token'];
}
}

?>  
