<?php
/* Code for sniffing out a reasonable IE browser implementation */

$browser_is_IE = false !== strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
$css_supports_fixed = !$browser_is_IE;
if ($browser_is_IE) {
	/* IE 6 seems to handle fixed positioning OK */
	preg_match('/MSIE (\d+)\.(\d+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
	if ((int)$matches[1] >= 6) {
		$css_supports_fixed = true;
	}
}
?>
