<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>zhwiki username check</title>
</head>
<body>
<?php
date_default_timezone_set('UTC');
echo "現在時間：".date("Y/m/d H:i")."<br>";
$api = 'https://zh.wikipedia.org/w/api.php';
$user = (isset($_GET["user"]) ? $_GET["user"] : "");
$uselang = (isset($_GET["uselang"]) ? $_GET["uselang"] : "zh-hant");
$user = trim($user);
?>
<form action="./">
	<table>
		<tr>
			<td>用戶名：</td>
			<td>
				<input type="text" name="user" value="<?=$user?>" required autofocus>
			</td>
		</tr>
		<!-- <tr>
			<td>顯示語言:</td>
			<td>
				<select name="uselang">
					<option value="zh-hant" <?=($uselang=="zh-hant"?"selected":"")?>>繁體</option>
					<option value="zh-hans" <?=($uselang=="zh-hans"?"selected":"")?>>简体</option>
				</select>
			</td>
		</tr> -->
		<tr>
			<td></td>
			<td><button type="submit">檢查</button></td>
		</tr>
	</table>
</form>
<?php
if ($user === "") {
	exit();
}

$url = 'https://login.wikimedia.org/w/api.php?action=query&format=json&list=users&usprop=cancreate&uselang='.$uselang.'&ususers='.urlencode($user);
$res = file_get_contents($url);
if ($res === false) {
	exit("檢查時發生錯誤，請再試一次");
}
$info = json_decode($res, true);
$info = $info["query"]["users"][0];
echo "<h3>檢查用戶名 \"".$info["name"]."\" 的結果如下</h3>";
?>
<!--
<?php var_dump($info); ?>
-->
<h4>技術性檢查：</h4>
<div style="margin-left: 30px;">
<?php
if ($user !== $info["name"]) {
	?><p><span style="color: red;">因為技術原因，您的用戶名會自動變更為「</span><?=$info["name"]?><span style="color: red;">」，若您不能接受，請另擇一個。</span></p><?php
}
if (isset($info["userid"])) {
	?><p><span style="color: red;">您的用戶名不可建立，原因為：已被他人使用，<a href="https://zh.wikipedia.org/wiki/Special:CentralAuth?target=<?=urlencode($info["name"])?>" target="_blank">全域帳號資訊</a></span></p><?php
}
if (isset($info["invalid"])) {
	?><p><span style="color: red;">您的用戶名不可建立，原因為：包含不允許的字元</span></p><?php
}
if (isset($info["cancreateerror"])) {
	$cancreateerror = $info["cancreateerror"][0];
	?><p><span style="color: red;">您的用戶名不可建立，原因為：<?php
		$message = $cancreateerror["message"];
		if ($message == "userexists") {
			$message = '已被他人使用，<a href="https://zh.wikipedia.org/wiki/Special:CentralAuth?target='.urlencode($info["name"]).'" target="_blank">全域帳號資訊</a>';
		}
		if ($message == "noname") {
			$message = "不可使用電子郵件地址作為用戶名";
		}
		if ($message == "titleblacklist-forbidden-new-account") {
			$message = "用戶名被黑名單禁止";
		}
		if ($message == "antispoof-name-illegal") {
			if ($cancreateerror["params"][1] == "未含有任何字母") {
				$message = "用戶名僅包含數字";
			} else if ($cancreateerror["params"][1] == "包含不兼容的混合文字") {
				$message = "包含不兼容的混合文字，例如不能中文和英文混用";
			}
		}
		if (isset($cancreateerror["params"])) {
			for ($i=1; $i <= count($cancreateerror["params"]); $i++) { 
				$message = str_replace("$".$i, $cancreateerror["params"][$i-1], $message);
			}
		}
		echo $message;
	?></span><?php
	if (isset($_GET["admin"])) {
		?><a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName=<?=$info["name"]?>" target="_blank">仍要建立</a>（<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName=<?=$info["name"]?>&wpCreateaccountMail=1" target="_blank">隨機密碼</a>）<?php
	}
	?></p><?php
}
if (isset($info["cancreate"])) {
	?>
	<p>
		<span style="color: green;">此用戶名可以建立，<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName=<?=$info["name"]?>" target="_blank">立即建立</a>（<a href="https://zh.wikipedia.org/wiki/Special:CreateAccount?wpName=<?=$info["name"]?>&wpCreateaccountMail=1" target="_blank">隨機密碼</a>）</span>
	</p>
	<p>
		如果您向管理員請求註冊帳戶而被導引來這裡，請直接告知那位管理員您測試通過的用戶名即可，不要複製貼上本頁內容。
	</p>
	<?php
}
?>
</div>
<h4>中文維基百科<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名" target="_blank">用戶名方針</a>檢查：</h4>
<div style="margin-left: 30px;">
<p>
<?php
if (preg_match("/(管理員|行政員|監管員|使用者核查員|使用者查核員|監督員|管理员|行政员|监管员|用户核查员|用户查核员|监督员|admin|sysop|moderator|bureaucrat|steward|checkuser|oversight)/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名包含了字眼"<?=$m[1]?>"，可能誤導他人您的帳戶擁有特定權限。</span><?php
} else if (preg_match("/bot$/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名以"bot"結尾，這被保留給機器人使用，除非您要建立一個機器人帳戶。</span><?php
} else if (preg_match("/bot\b/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名包含了字眼"bot"，可能誤導他人您的帳戶是機器人帳戶，除非您要建立一個機器人帳戶。</span><?php
} else {
	?>自動檢查未發現任何問題。<?php
}
?>
</p>
<p>
	維基百科不允許一些用戶名：
	<ul>
		<li>公司/團體名稱（<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名#公司/團體名稱" target="_blank">查看例外</a>）</li>
		<li>暗示多人共有的用戶名（<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名#分享帳戶" target="_blank">分享帳戶亦被禁止</a>）</li>
		<li>誤導性、侮辱性、冒犯性、破壞性用戶名</li>
	</ul>
</p>
<p>
	您可以閱讀<a href="https://zh.wikipedia.org/wiki/Wikipedia:用户名" target="_blank">用戶名方針</a>了解詳細的規定。
</p>
</div>

</body>
</html>
