<!DOCTYPE html>
<?php
require_once(__DIR__.'/vendor/autoload.php');
$int = new Intuition('wikipedia-username-check');
$int->registerDomain('wikipedia-username-check', __DIR__.'/i18n');
?>
<html>
<head>
	<meta charset="utf-8">
	<title><?=$int->msg('title')?></title>
</head>
<body>
<?php
date_default_timezone_set('UTC');
echo $int->msg('current-time', ['variables' => [date("Y/m/d H:i")]])."<br>";
$api = 'https://zh.wikipedia.org/w/api.php';
$user = (isset($_GET["user"]) ? $_GET["user"] : "");
$user = trim($user);
?>
<form action="./">
	<table>
		<tr>
			<td><?=$int->msg('username')?></td>
			<td>
				<input type="text" name="user" value="<?=htmlspecialchars($user)?>" required autofocus>
			</td>
		</tr>
		<tr>
			<td><?=$int->msg('language')?></td>
			<td>
				<select name="userlang">
					<?php
					$langs = ['en', 'zh-hans', 'zh-hant'];
					$fallback = array_values(array_intersect(
						array_merge(
							[$int->getLang()],
							$int->getLangFallbacks($int->getLang())
						),
						$langs
					))[0];
					foreach ($langs as $lang) {
						?>
						<option value="<?=$lang?>" <?=($lang==$fallback?"selected":"")?>><?=$int->getLangName($lang)?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit"><?=$int->msg('check')?></button></td>
		</tr>
	</table>
</form>
<?php
if ($user === "") {
	exit();
}

$url = 'https://login.wikimedia.org/w/api.php?action=query&format=json&list=users&usprop=cancreate&uselang='.$int->getLang().'&ususers='.urlencode($user);
$res = file_get_contents($url);
if ($res === false) {
	exit("檢查時發生錯誤，請再試一次");
}
$info = json_decode($res, true);
$info = $info["query"]["users"][0];
echo "<h3>".$int->msg('result', ['variables' => [htmlentities($info["name"])]])."</h3>";
?>
<!--
<?php var_dump($info); ?>
-->
<h4><?=$int->msg('technical-check')?></h4>
<div style="margin-left: 30px;">
<?php
if ($user !== $info["name"]) {
	?><p><span style="color: red;"><?=$int->msg('correction', ['variables' => ['<span style="color: black;">'.$info["name"].'</span>']])?></span></p><?php
}
if (isset($info["userid"])) {
	?><p><span style="color: red;"><?=
		$int->msg('cannot-create')." ".$int->msg('userexists', ['variables' => [
		'<a href="https://zh.wikipedia.org/wiki/Special:CentralAuth?target='.urlencode($info["name"]).'" target="_blank">'.$int->msg('central-auth').'</a>',
		'<a href="https://zh.wikipedia.org/wiki/Special:UserRights?user='.urlencode($info["name"]).'" target="_blank">'.$int->msg('grant-rights').'</a>'
		]]);
	?></span></p><?php
}
if (isset($info["invalid"])) {
	?><p><span style="color: red;"><?=$int->msg('cannot-create')." ".$int->msg('invalid-username')?></span></p><?php
}
if (isset($info["cancreateerror"])) {
	$cancreateerror = $info["cancreateerror"][0];
	?><p><span style="color: red;"><?php
		echo $int->msg('cannot-create')." ";
		$message = $cancreateerror["message"];
		if ($message == "userexists") {
			$message = $int->msg('userexists', ['variables' => [
				'<a href="https://zh.wikipedia.org/wiki/Special:CentralAuth?target='.urlencode($info["name"]).'" target="_blank">'.$int->msg('central-auth').'</a>',
				'<a href="https://zh.wikipedia.org/wiki/Special:UserRights?user='.urlencode($info["name"]).'" target="_blank">'.$int->msg('grant-rights').'</a>'
			]]);
		}
		if ($message == "noname") {
			$message = $int->msg('email-username');
		}
		if ($message == "titleblacklist-forbidden-new-account") {
			$message = $int->msg('titleblacklist');
		}
		if ($message == "antispoof-name-illegal") {
			$url = 'https://login.wikimedia.org/w/api.php?action=query&format=json&list=users&usprop=cancreate&uselang=qqx&ususers='.urlencode($user);
			$res = file_get_contents($url);
			$info2 = json_decode($res, true);
			$info2 = $info2["query"]["users"][0];
			$cancreateerror2 = $info2["cancreateerror"][0];
			if ($cancreateerror2["params"][1] == "(antispoof-noletters)") {
				$message = $int->msg('only-number');
			} else if ($cancreateerror2["params"][1] == "(antispoof-mixedscripts)") {
				$message = $int->msg('mixedscripts');
			}
		}
		if (isset($cancreateerror["params"])) {
			for ($i=1; $i <= count($cancreateerror["params"]); $i++) {
				$message = str_replace("$".$i, $cancreateerror["params"][$i-1], $message);
			}
		}
		echo $message;
	?></span> <?php
	if (isset($_GET["admin"])) {
		echo $int->msg('continue-create', ['variables' => [
			'<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName='.$info["name"].'" target="_blank">'.$int->msg('continue-create-text').'</a>',
			'<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName='.$info["name"].'&wpCreateaccountMail=1" target="_blank">'.$int->msg('random-password').'</a>'
		]]);
	}
	?></p><?php
}
if (isset($info["cancreate"])) {
	?>
	<p>
		<span style="color: green;"><?php
		echo $int->msg('can-create', ['variables' => [
			'<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName='.$info["name"].'" target="_blank">'.$int->msg('create-now').'</a>',
			'<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName='.$info["name"].'&wpCreateaccountMail=1" target="_blank">'.$int->msg('random-password').'</a>'
		]]);
		?></span>
	</p>
	<p>
		<?=$int->msg('account-request')?>
	</p>
	<?php
}
?>
</div>
<h4><?=$int->msg('policy-check', ['variables' => [
	'<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名" target="_blank">'.$int->msg('policy-text').'</a>'
	]])?></h4>
<div style="margin-left: 30px;">
<p>
<?php
if (preg_match("/(管理員|行政員|監管員|使用者核查員|使用者查核員|監督員|管理员|行政员|监管员|用户核查员|用户查核员|监督员|admin|sysop|moderator|bureaucrat|steward|checkuser|oversight)/i", $info["name"], $m)) {
	?><span style="color: red;"><?=$int->msg('contain-admin', ['variables' => [$m[1]]])?></span><?php
} else if (preg_match("/bot$/i", $info["name"], $m)) {
	?><span style="color: red;"><?=$int->msg('end-with-bot')?></span><?php
} else if (preg_match("/bot\b/i", $info["name"], $m)) {
	?><span style="color: red;"><?=$int->msg('contain-bot')?></span><?php
} else {
	echo $int->msg('no-problem');
}
?>
<a href="https://www.google.com/search?q=<?= urlencode($info["name"]) ?>" target="_blank"><?= $int->msg('google-search') ?></a>
</p>
<p>
	<?=$int->msg('policy-detail')?>
	<ul>
		<li><?=$int->msg('policy-spam-name', ['variables' => [
			'<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名#公司/團體名稱" target="_blank">'.$int->msg('policy-spam-exception').'</a>'
			]])?></li>
		<li><?=$int->msg('policy-share-name', ['variables' => [
			'<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名#分享帳戶" target="_blank">'.$int->msg('policy-share-account').'</a>'
			]])?></li>
		<li><?=$int->msg('policy-bad-name')?></li>
	</ul>
</p>
<p>
	<?=$int->msg('policy-readmore', ['variables' => [
		'<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名" target="_blank">'.$int->msg('policy-text').'</a>'
		]])?>
</p>
</div>

</body>
</html>
