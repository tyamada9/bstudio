<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link href="css/common.css" type="text/css" rel="stylesheet" media="all" />
<link href="css/login.css" type="text/css" rel="stylesheet" media="all" />
<script src="js/bframe.js" type="text/javascript"></script>
<script src="js/bstudio.js" type="text/javascript"></script>
<title><?php echo $this->title ?></title></head>
<body onload="bstudio.focusLoginId()">
	<script type="text/javascript">if(window != top) top.location.href='.';</script>
	<div id="title-header"><h1><?php echo $this->site_title; ?></h1></div>

	<div class="login">
		<form method="post" action="." autocomplete="off">
			<div class="account">
				<table>
					<tbody>
						<tr>
							<th class="user">ログインID</th>
							<td><input id="user_id" name="user_id" type="text" class="textbox ime_off" size="30" maxlength="20" /></td>
						</tr>
						<tr>
							<th class="key">パスワード</th>
							<td><input id="password" name="password" type="password" class="textbox" size="30" maxlength="20" /></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="icon">
				<img src="images/login/lock.png" alt="lock" />
			</div>

			<ul class="submit">
				<li><input class="login-button" name="login" type="submit" id="login" value="login"  /></li>
			</ul>
		</form>
	</div>

</body>
