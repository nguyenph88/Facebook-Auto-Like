<?php
session_start();
// JSONURL //
function get_html($url) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FAILONERROR, 0);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
function get_json($url) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FAILONERROR, 0);
	$data = curl_exec($ch);
	curl_close($ch);
	return json_decode($data);
}
if($_SESSION['token']){
	$token = $_SESSION['token'];
	$graph_url ="https://graph.fb.me/me?access_token=" . $token;
	$user = get_json($graph_url);
	if ($user->error) {
		if ($user->error->type== "OAuthException") {
			session_destroy();
			error_reporting(0);
			$cut = explode('&',$token);
			unlink('pantek/'.$cut[0]);
			if(!is_dir('expired')){
				mkdir('expired');
			}
			$v=fopen('expired/'.$cut[0],'w');
			fwrite($v,1);
			fclose($v);
			header('Location: index.php?i=Token expired please re-enter token');
		}
	}
}
?>



</body>
</html>
