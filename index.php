<?php
// SPDX-License-Identifier: MIT

// Loca Template
require_once __DIR__ . \DIRECTORY_SEPARATOR . 'utils' . \DIRECTORY_SEPARATOR . 'template.php';

// Set UA
ini_set('user_agent', 'Qiuwen/1.1 (Username-Check/1.0)');
define(CURLOPT_USERAGENT, "Qiuwen/1.1 (Username-Check/1.0)");

// Set variables
$api = 'https://www.qiuwenbaike.cn/api.php';
$user = (isset($_GET["user"]) ? $_GET["user"] : "");
$user = trim($user);
$encodedUser = htmlspecialchars($user);
date_default_timezone_set('Asia/Shanghai');
$date = date("Y/m/d H:i");

$pageContent = <<<EOF
<form action="./">
	<p>
		<em>当前时间：$date</em>
	</p>
	<p>
		<label for="user">用户名：</label>
		<input type="text" name="user" id="user" title="user" value="$encodedUser" required autofocus />
	</p>
	<p>
		<input type="submit" name="submit" id="submit" value="检查" />
	</p>
</form>
EOF;

if ($user === "") {
	pageTemplate($pageContent);
	exit();
}

$url = 'https://login.qiuwenbaike.cn/api.php?action=query&format=json&list=users&usprop=cancreate&ususers=' . urlencode($user);
$res = file_get_contents($url);
if ($res === false) {
	$pageContent = $pageContent . <<<EOF
	<p>检查时发生错误，请再试一次</p>
	EOF;
	pageTemplate($pageContent);
	exit();
}

$info = json_decode($res, true);
$info = $info["query"]["users"][0];
$dump = var_dump($info);
$userName = htmlentities($info["name"]);

$pageContent = $pageContent . <<<EOF

<h2>检查结果</h2>

<h3>技术性检查</h3>
EOF;

if ($user !== $info["name"]) {
	$correctedName = $info["name"];
	$pageContent = $pageContent . <<<EOF
<p>
	<span style="color: red;">
		因为技术原因，您的用户名会自动变更为<span style="color: black;">“$correctedName ”</span>。若您不能接受，请另择一个。
	</span>
</p>
EOF;
}

if (isset($info["userid"])) {
	$existName = $info["name"];
	$encodedExistName = urlencode($existName);
	$pageContent = $pageContent . <<<EOF
<p>
	<span style="color: red;">
		您的用户名不可建立，原因为：已被他人使用。<br />
		参见：<a href="https://www.qiuwenbaike.cn/wiki/Special:CentralAuth?target=$encodedExistName" target="_blank">全域账号信息</a>、<a href="https://www.qiuwenbaike.cn/wiki/Special:UserRights?user=$encodedExistName" target="_blank">权限授予信息</a>。
	</span>
</p>
EOF;
}

if (isset($info["invalid"])) {
	$existName = $info["name"];
	$encodedExistName = urlencode($existName);
	$pageContent = $pageContent . <<<EOF
<p>
	<span style="color: red;">
		您的用户名不可建立。
	</span>
</p>
<p>
	原因：包含不允许的字符。
</p>
EOF;
}

if (isset($info["cancreateerror"])) {
	$nameCannotCreated = $info["name"];
	$encodedNameCannotCreated = urlencode($nameCannotCreated);
	$pageContent = $pageContent . <<<EOF
<p>
	<span style="color: red;">
		您的用户名不可建立。
	</span>
<p>
EOF;

	$cancreateerror = $info["cancreateerror"][0];
	$message = $cancreateerror["message"];

	if ($message == "userexists") {
		$pageContent = $pageContent . <<<EOF
		<p>原因：已被他人使用。</p>
		<p>参见：</p>
		<ul>
			<li><a href="https://www.qiuwenbaike.cn/wiki/Special:CentralAuth?target=$encodedNameCannotCreated" target="_blank">全域账号信息</a></li>
			<li><a href="https://www.qiuwenbaike.cn/wiki/Special:UserRights?user=$encodedNameCannotCreated" target="_blank">权限授予信息</a></li>
		</ul>
		EOF;
	} else if ($message == "noname") {
		$pageContent = $pageContent . "<p>原因：不可使用电子邮件地址作为用户名。</p>";
	} else if ($message == "titleblacklist-forbidden-new-account") {
		$pageContent = $pageContent . "<p>原因：用户名包含列入黑名单的关键词。</p>";
	} else if ($message == "antispoof-conflict") {
		$pageContent = $pageContent . "<p>原因：用户名与其他用户名过于相似，请选择其它用户名。</p>";
	} else {
		$pageContent = $pageContent . "<p>原因：用户名因其他技术原因无法创建，请选择其它用户名。</p>" . <<<EOF
		<p>
		技术信息（请报告管理员）：
		<code>$message</code>
		</p>
		EOF;
	}


	if (isset($_GET["admin"])) {
		$pageContent = $pageContent . <<<EOF
		<p>仍要创建？</p>
		<ul>
			<li><a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=$encodedNameCannotCreated" target="_blank">继续创建</a></li>
			<li><a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=$encodedNameCannotCreated&wpCreateaccountMail=1"" target="_blank">继续创建（<small>随机密码</small>）</a></li>
		</ul>
		EOF;
	}
}

if (isset($info["cancreate"])) {
	$nameCanCreate = $info["name"];
	$encodedNameCanCreate = urlencode($nameCanCreate);
	$pageContent = $pageContent . <<<EOF
<h3>账户请求</h3>
<p>如果您向管理员请求注册账户而被导引来这里，请直接告知那位管理员您测试通过的用户名即可，不要复制粘贴本页内容或截图。</p>
<p>
	<span style="color: green;">
		此用户名可以建立。
	</span>
</p>
	<ul>
		<li><a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=$nameCannotCreated" target="_blank">立即创建</a></li>
		<li><a href="https://www.qiuwenbaike.cn/wiki/Special:CreateAccount?wpName=$nameCannotCreated&wpCreateaccountMail=1"" target="_blank">立即创建（<small>随机密码</small>）</a></li>
	</ul>
EOF;
}


$pageContent = $pageContent . <<<EOF
<h3>合规性检查</h3>
<p>以下检查旨在确认您的用户名是否存在违反<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:用户名" target="_blank">用户名方针</a>之处。</p>
EOF;


if (preg_match("/(管理員|行政員|監管員|裁決委員|使用者核查員|使用者查核員|監督員|裁决委员|管理员|行政员|监管员|用户核查员|用户查核员|监督员|admin|sysop|moderator|bureaucrat|steward|checkuser|oversight)/i", $info["name"], $m)) {
	$match = $m[1];
	$pageContent = $pageContent . <<<EOF
	<p>
		<span style="color: red;">
			您的用户名包含了特定字词“$match ”，可能误导他人您的账户拥有特定权限。
		</span>
	</p>
	EOF;
} else if (preg_match("/(机器人|机械人|機器人|機械人|bot$)/i", $info["name"], $m)) {
	$pageContent = $pageContent . <<<EOF
	$match = $m[1];
	<p>
		<span style="color: red;">
			您的用户名包含了特定字词“$match ”，可能误导他人您的账户是机器人账户，除非您要创建一个机器人账户。
		</span>
	</p>
	EOF;
} else {
	$pageContent = $pageContent . <<<EOF
	<p>
		<span style="color: green;">
			自动检查未发现任何问题。
		</span>
	</p>
	EOF;
}

$pageContent = $pageContent . <<<EOF
<h3>参见</h3>
<ul>
	<li>
		<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:用户名方针" target="_blank">用户名方针</a>
	</li>
	<li>
		<a href="https://www.qiuwenbaike.cn/wiki/Qiuwen:一人一号" target="_blank">一人一号</a>
	</li>
</ul>
EOF;

pageTemplate($pageContent);
