<?php
require_once __DIR__ . \DIRECTORY_SEPARATOR . 'matomoJS.php';

function pageTemplate($content)
{
	$matomo = matomoJS();
	echo <<< EOF
	<!DOCTYPE html>
	<html lang="zh-hans" xml:lang="zh-hans">
		<head>
			<meta charset="utf-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
			<title>求闻百科用户名检查</title>
			<link rel="stylesheet" href="https://unc.qiuwen.net.cn/assets/styles.css?ver=20250216" />
		</head>
		<body>
			<header>
				<a href="https://www.qiuwenbaike.cn" title="求闻百科">
					<img src="https://assets.zhongwen.wiki/commons/wordmark/qiuwenbaike-with-favicon-and-slogan.png" width="238" height="35" alt="求闻百科，共笔求闻" />
				</a>
			</header>
			<main>
				<h1>求闻百科用户名检查</h1>
				$content
			</main>
			<footer>
				<div class="footer-copyright">
					<p>
						© 2019 Xiplus；© 2025 求闻百科贡献者
						<em>
						（<a href="./LICENSE">MIT许可证</a>）
						</em>
					</p>
					<p>
						求闻®、求闻百科®、共笔全书®、“求”字商标、“绿竹”图标等文字、图形、图文组合，均是本网站运营者——<a
							href="https://www.gongbiquanshu.cn"
							title="无锡共笔全书网络有限责任公司"
							target="_blank"
							rel="noopener noreferrer"
							>无锡共笔全书网络有限责任公司</a
						>或其关联实体的商标或注册商标。
					</p>
				</div>
				<div class="footer-links">
					<a
						class="qwlink qwlink-about"
						href="https://www.qiuwenbaike.cn/wiki/Qiuwen:%E5%85%B3%E4%BA%8E%E6%B1%82%E9%97%BB%E7%99%BE%E7%A7%91"
						title="关于“求闻”"
						rel="noreferrer noopener"
						target="_blank"
						>关于“求闻”</a
					>
					<a class="qwlink qwlink-tos" href="https://www.qiuwenbaike.cn/wiki/Qiuwen:%E7%94%A8%E6%88%B7%E5%8D%8F%E8%AE%AE" title="用户协议" rel="noreferrer noopener" target="_blank">用户协议</a>
					<a
						class="qwlink qwlink-pipp"
						href="https://www.qiuwenbaike.cn/wiki/Qiuwen:%E4%B8%AA%E4%BA%BA%E4%BF%A1%E6%81%AF%E4%BF%9D%E6%8A%A4%E6%96%B9%E9%92%88"
						title="个人信息保护方针"
						rel="noreferrer noopener"
						target="_blank"
						>个人信息保护方针</a
					>
					<a class="govlink govlink-icp" href="https://beian.miit.gov.cn/" title="中华人民共和国工业和信息化部ICP/IP地址/域名信息备案管理系统" rel="noreferrer noopener" target="_blank"
						>苏ICP备2022013164号</a
					>
					<a class="govlink govlink-ga" href="http://beian.mps.gov.cn/" title="中华人民共和国公安部全国互联网安全管理平台" rel="noreferrer noopener" target="_blank"
						>苏公网安备32021302000963号</a
					>
				</div>
			<div class="footer-buttons">
				<a href="https://www.qiuwenbaike.cn" title="“求闻”计划网站" target="_blank" rel="noopener noreferrer"
					><img alt="“求闻”计划网站" width="88" height="31" src="https://assets.zhongwen.wiki/commons/button/qiuwen.png"
				/></a>
				<a href="https://www.gongbiquanshu.cn" title="共笔全书旗下网站" target="_blank" rel="noopener noreferrer"
					><img alt="共笔全书旗下网站" class="img" width="88" height="31" src="https://assets.zhongwen.wiki/commons/button/gbqs.png"
				/></a>
			</div>
			</footer>
			$matomo
		</body>
	</html>
	EOF;
}
