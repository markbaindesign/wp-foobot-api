<?php

/**
 * API tests
 */

function bd_foobot_show_api_key()
{
   
   $key = bd_foobot_get_api_key();
   ob_start();
	echo '<pre><code>'. $key . '</code></pre>';
	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
add_shortcode('foobot_show_key', 'bd_foobot_show_api_key');