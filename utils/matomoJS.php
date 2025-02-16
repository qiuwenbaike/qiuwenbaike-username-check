<?php
function matomoJS()
{
	return <<<EOF
	<script>(function(b,c,a){var d='&action_name='+b.title;c='&url='+a(c.href);var e=a(b.referrer)?'&urlref='+a(b.referrer):'';a=new XMLHttpRequest;a.open('post','https://www.qiuwenbaike.cn/rest.php/audit?idsite=13&rec=1&send_image=1'+c+d+e+'&rand=1');a.send()})(document,location,encodeURIComponent);</script>
	<noscript><img src='https://www.qiuwenbaike.cn/rest.php/audit?idsite=13&rec=1' width='1' height='1' alt='' /></noscript>
	EOF;
}
