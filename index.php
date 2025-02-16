<?php
require_once(__DIR__ . \DIRECTORY_SEPARATOR . '/vendor/autoload.php');
$int = new Intuition('qiuwenbaike-username-check');
$int->registerDomain('qiuwenbaike-username-check', __DIR__ . \DIRECTORY_SEPARATOR . '/i18n');
ini_set('user_agent', 'Qiuwen/1.1 (Username-Check/1.0)');
define(CURLOPT_USERAGENT, "Qiuwen/1.1 (Username-Check/1.0)");
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>
		<?php echo $int->msg('title') ?>
	</title>
</head>

<body>
	<?php
	date_default_timezone_set('UTC');
	echo $int->msg('current-time', ['variables' => [date("Y/m/d H:i")]]) . "<br>";
	$api = 'https://www.qiuwenbaike.cn/api.php';
	$user = (isset($_GET["user"]) ? $_GET["user"] : "");
	$user = trim($user);
	?>
	<form action="./">
		<table>
			<tr>
				<td>
					<?php echo $int->msg('username') ?>
				</td>
				<td>
					<input type="text" name="user" value="<?php echo htmlspecialchars($user) ?>" required autofocus>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $int->msg('language') ?>
				</td>
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
						foreach ($langs as $lang) { ?>
							<option value="<?php echo $lang ?>"
								<?php echo ($lang == $fallback ? "selected" : "") ?>>
								<?php echo $int->getLangName($lang) ?>
							</option>
						<?php
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<button type="submit">
						<?php echo $int->msg('check') ?>
					</button>
				</td>
			</tr>
		</table>
	</form>
	<?php
	if ($user === "") {
		exit();
	}

	$url = 'https://login.qiuwenbaike.cn/api.php?action=query&format=json&list=users&usprop=cancreate&uselang=' . $int->getLang() . '&ususers=' . urlencode($user);
	$res = file_get_contents($url);
	if ($res === false) {
		exit("檢查時發生錯誤，請再試一次");
	}
	$info = json_decode($res, true);
	$info = $info["query"]["users"][0];
	echo "<h3>" . $int->msg('result', ['variables' => [htmlentities($info["name"])]]) . "</h3>"; ?>
	<!-- <?php var_dump($info); ?> -->
	<h4>
		<?php echo $int->msg('technical-check') ?>
	</h4>
	<div style="margin-left: 30px;">
		<?php
		if ($user !== $info["name"]) { ?>
			<p>
				<span style="color: red;">
					<?php
					echo $int->msg('correction', ['variables' => ['<span style="color: black;">' . $info["name"] . '</span>']])
					?>
				</span>
			</p>
		<?php
		}
		if (isset($info["userid"])) { ?>
			<p>
				<span style="color: red;">
					<?php
					echo $int->msg('cannot-create') . " " . $int->msg('userexists', ['variables' => [
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:CentralAuth?target=' . urlencode($info["name"]) . '" target="_blank">' . $int->msg('central-auth') . '</a>',
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:UserRights?user=' . urlencode($info["name"]) . '" target="_blank">' . $int->msg('grant-rights') . '</a>'
					]]);
					?>
				</span>
			</p>
		<?php
		}
		if (isset($info["invalid"])) { ?>
			<p>
				<span style="color: red;">
					<?php echo $int->msg('cannot-create') . " " . $int->msg('invalid-username') ?>
				</span>
			</p>
		<?php
		}
		if (isset($info["cancreateerror"])) {
			$cancreateerror = $info["cancreateerror"][0]; ?>
			<p>
			<div style="color: red;">
				<?php echo $int->msg('cannot-create') . " ";
				$message = $cancreateerror["message"];
				if ($message == "userexists") {
					$message = $int->msg('userexists', ['variables' => [
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:CentralAuth?target=' . urlencode($info["name"]) . '" target="_blank">' . $int->msg('central-auth') . '</a>',
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:UserRights?user=' . urlencode($info["name"]) . '" target="_blank">' . $int->msg('grant-rights') . '</a>'
					]]);
				}
				if ($message == "noname") {
					$message = $int->msg('email-username');
				}
				if ($message == "titleblacklist-forbidden-new-account") {
					$message = $int->msg('titleblacklist');
				}
				if ($message == "antispoof-name-illegal") {
					$url = 'https://login.qiuwenbaike.cn/api.php?action=query&format=json&list=users&usprop=cancreate&uselang=qqx&ususers=' . urlencode($user);
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
				if ($message == "antispoof-conflict") {
					$message = $int->msg('conflict-username');
				}
				if (isset($cancreateerror["params"])) {
					for ($i = 1; $i <= count($cancreateerror["params"]); $i++) {
						$param = $cancreateerror["params"][$i - 1];
						if (is_array($param) && isset($param['num'])) {
							$param = $param['num'];
						}
						$message = str_replace("$" . $i, $param, $message);
					}
				}
				echo $message; ?>
			</div>
			<?php
			if (isset($_GET["admin"])) {
				echo $int->msg('continue-create', ['variables' => [
					'<a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=' . $info["name"] . '" target="_blank">' . $int->msg('continue-create-text') . '</a>',
					'<a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=' . $info["name"] . '&wpCreateaccountMail=1" target="_blank">' . $int->msg('random-password') . '</a>'
				]]);
			} ?>
			</p>
		<?php
		}
		if (isset($info["cancreate"])) { ?>
			<p>

				<?php echo $int->msg('account-request')
				?>

			</p>
			<p>
				<span style="color: green;">
					<?php echo $int->msg('can-create', ['variables' => [
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=' . $info["name"] . '" target="_blank">' . $int->msg('create-now') . '</a>',
						'<a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=' . $info["name"] . '&wpCreateaccountMail=1" target="_blank">' . $int->msg('random-password') . '</a>'
					]]); ?>
				</span>
			</p>
		<?php
		} ?>
	</div>
	<h4>
		<?php
		echo $int->msg('policy-check', ['variables' => [
			'<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:用户名" target="_blank">' . $int->msg('policy-text') . '</a>'
		]])
		?>
	</h4>
	<div style="margin-left: 30px;">
		<p>
			<?php
			if (preg_match("/(管理員|行政員|監管員|裁決委員|使用者核查員|使用者查核員|監督員|裁决委员|管理员|行政员|监管员|用户核查员|用户查核员|监督员|admin|sysop|moderator|bureaucrat|steward|checkuser|oversight)/i", $info["name"], $m)) { ?>
				<span style="color: red;">
					<?php echo $int->msg('contain-admin', ['variables' => [$m[1]]]) ?>
				</span>
			<?php
			} else if (preg_match("/bot$/i", $info["name"], $m)) { ?>
				<span style="color: red;">
					<?php echo $int->msg('end-with-bot') ?>
				</span>
			<?php
			} else if (preg_match("/bot\b/i", $info["name"], $m)) { ?>
				<span style="color: red;">
					<?php echo $int->msg('contain-bot') ?>
				</span>
			<?php
			} else {
				echo $int->msg('no-problem');
			} ?>
		</p>
		<p>
			<?php echo $int->msg('policy-detail') ?>
		<ul>
			<li>
				<?php echo $int->msg('policy-spam-name', ['variables' => [
					'<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:用户名方针#SPAMNAME" target="_blank">' . $int->msg('policy-spam-exception') . '</a>'
				]]) ?>
			</li>
			<li>
				<?php echo $int->msg('policy-share-name', ['variables' => [
					'<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:一人一号#BADSOCK" target="_blank">' . $int->msg('policy-share-account') . '</a>'
				]]) ?>
			</li>
			<li>
				<?php echo $int->msg('policy-bad-name') ?>
			</li>
		</ul>
		</p>
		<p>

			<?php echo $int->msg('policy-readmore', ['variables' => [
				'<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:用户名" target="_blank">' . $int->msg('policy-text') . '</a>'
			]]) ?>
		</p>
	</div>

</body>

</html>