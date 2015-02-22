<?
$root = preg_replace('/\/index.php$/', '', $_SERVER['PHP_SELF']);
$apikeyPrefix = '__' . $goose->util->generateRandomString(15) . '__';
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="UTF-8" />
<title>Install Goose</title>
<link rel="stylesheet" href="<?=$root?>/pages/src/css/install.css" />
</head>
<body>
<main>
	<header>
		<h1>Install Goose</h1>
	</header>
	<form action="./" method="post" id="regsterForm">
		<fieldset>
			<legend>Database infomation</legend>
			<label class="first">
				<strong>DB user id</strong>
				<span><input type="text" name="dbId" id="dbId" size="15" maxlength="20" /></span>
			</label>
			<label>
				<strong>DB password</strong>
				<span><input type="password" name="dbPassword" size="15" id="dbPassword" maxlength="20" /></span>
			</label>
			<label>
				<strong>DB confirm password</strong>
				<span><input type="password" name="dbPassword2" size="15" id="dbPassword2" maxlength="20" /></span>
			</label>
			<label>
				<strong>DB name</strong>
				<span><input type="text" name="dbName" id="dbName" size="15" maxlength="20" /></span>
			</label>
			<hr />
			<label>
				<strong>DB hostname</strong>
				<span><input type="text" name="dbHost" id="dbHost" size="12" maxlength="40" value="localhost" /></span>
			</label>
			<label>
				<strong>Table name prefix</strong>
				<span><input type="text" name="dbPrefix" id="dbPrefix" size="12" maxlength="20" value="GOOSE_" /></span>
			</label>
		</fieldset>

		<fieldset>
			<legend>Admin infomation</legend>
			<label class="first">
				<strong>E-mail</strong>
				<span><input type="email" name="email" id="email" size="28" maxlength="40" /></span>
			</label>
			<label>
				<strong>Nickname</strong>
				<span><input type="text" name="name" id="name" size="18" maxlength="20" /></span>
			</label>
			<label>
				<strong>Password</strong>
				<span><input type="password" name="password" id="password" size="15" maxlength="20" /></span>
			</label>
			<label>
				<strong>Confirm password</strong>
				<span><input type="password" name="password2" id="password2" size="15" maxlength="20" /></span>
			</label>
		</fieldset>

		<fieldset>
			<legend>API</legend>
			<label class="first">
				<strong>API KEY prefix</strong>
				<span><input type="text" name="apiPrefix" id="apiPrefix" size="28" maxlength="20" value="<?=$apikeyPrefix?>" /></span>
			</label>
		</fieldset>

		<fieldset>
			<legend>ETC</legend>
			<label class="first">
				<strong>Timezone</strong>
				<span>
					<input type="text" name="timezone" size="24" maxlength="30" value="Asia/Seoul" required /><br/>
					<span><a href="http://php.net/manual/en/timezones.php" target="_blank">List of Supported Timezones</a> 페이지에 있는 지역을 참고하여 해당지역을 입력해주세요.</span>
				</span>
				
			</label>
		</fieldset>
		<nav>
			<button type="submit">Install</button>
		</nav>
	</form>
</main>


<script src="<?=$root?>/libs/ext/jQuery/jquery-1.11.2.min.js"></script>
<script src="<?=$root?>/libs/ext/validation/jquery.validate.min.js"></script>
<script src="<?=$root?>/libs/ext/validation/localization/messages_ko.js"></script>
<script>
jQuery(function($){
	$('#regsterForm').validate({
		rules : {
			dbId : { required: true, minlength: 3 }
			,dbPassword : { required: true }
			,dbPassword2 : { equalTo: '#dbPassword' }
			,dbName : { required: true }
			,dbHost : { required: true }
			,dbPrefix : { required: true }
			,email : { required: true }
			,name : { required: true }
			,password : { required: true }
			,password2 : { equalTo: '#password' }
			,apiPrefix : { required: true }
		}
	});
});
</script>
</body>
</html>