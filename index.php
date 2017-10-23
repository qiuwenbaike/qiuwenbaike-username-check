<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>zhwiki username check</title>
</head>
<body>
<?php
date_default_timezone_set('UTC');
echo "現在時間: ".date("Y/m/d H:i")."<br>";
$api = 'https://zh.wikipedia.org/w/api.php';
$user = (isset($_GET["user"]) ? $_GET["user"] : "");
$uselang = (isset($_GET["uselang"]) ? $_GET["uselang"] : "zh-hant");
$user = trim($user);
?>
<form>
	<table>
		<tr>
			<td>用戶名:</td>
			<td>
				<input type="text" name="user" value="<?=$user?>" required>
			</td>
		</tr>
		<!-- <tr>
			<td>顯示語言:</td>
			<td>
				<select name="uselang">
					<option value="zh-hant" <?=($type=="zh-hant"?"selected":"")?>>繁體</option>
					<option value="zh-hans" <?=($type=="zh-hans"?"selected":"")?>>简体</option>
				</select>
			</td>
		</tr> -->
		<tr>
			<td></td>
			<td><button type="submit">check</button></td>
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
echo "檢查用戶名 \"".$info["name"]."\" 的結果如下";
?>
<!--
<?php var_dump($info); ?>
-->
<br>
<br>
技術性檢查：<br>
<?php
if (preg_match("/^[a-z]/", $user)) {
	?><span style="color: red;">提醒：用戶名第一個字會被自動替換成大寫</span>，目前自動變更為"<?=$info["name"]?>"<br><?php
}
if (isset($info["userid"])) {
	?><span style="color: red;">您的用戶名不可建立，原因為：已被他人使用<?php
		
	?></span><br><?php
}
if (isset($info["invalid"])) {
	?><span style="color: red;">您的用戶名不可建立，原因為：包含不允許的字元<?php
		
	?></span><br><?php
}
if (isset($info["cancreateerror"])) {
	$cancreateerror = $info["cancreateerror"][0];
	?><span style="color: red;">您的用戶名不可建立，原因為：<?php
		$message = $cancreateerror["message"];
		if ($message == "userexists") {
			$message = "已被他人使用";
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
	?></span><br><?php
}
if (isset($info["cancreate"])) {
	?><span style="color: green;">此用戶名可以建立</span><br><?php
}
?>
<br>
中文維基百科<a href="https://zh.wikipedia.org/wiki/Wikipedia:%E7%94%A8%E6%88%B7%E5%90%8D" target="_blank">用戶名方針</a>檢查：<br>
<?php
if (preg_match("/(管理員|行政員|監管員|使用者核查員|監督員|管理员|行政员|监管员|用户核查员|监督员|admin|sysop|moderator|bureaucrat|steward|checkuser|oversight)/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名包含了字眼"<?=$m[1]?>"，可能誤導他人您的帳戶擁有特定權限</span><br><?php
} else if (preg_match("/bot$/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名以"bot"結尾，這被保留給機器人使用，除非您要建立一個機器人帳戶</span><br><?php
} else if (preg_match("/bot\b/i", $info["name"], $m)) {
	?><span style="color: red;">您的用戶名包含了字眼"bot"，可能誤導他人您的帳戶是機器人帳戶</span><br><?php
} else {
	?>自動檢查未發現任何問題，您可以閱讀<a href="https://zh.wikipedia.org/wiki/Wikipedia:%E7%94%A8%E6%88%B7%E5%90%8D#.E9.81.B8.E6.93.87.E4.B8.80.E5.80.8B.E7.94.A8.E6.88.B6.E5.90.8D" target="_blank">用戶名方針</a>了解哪些用戶名不被允許。<?php
}
?>

</body>
</html>
