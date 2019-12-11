<?php

/**
 * Database tests
 */

function bd_f_test_transient()
{
  // [foobot_transient]
  if (1 == get_transient('foobot-test')) {
    // Debug
    error_log("foobot-test is unexpired", 0);
    return;
  }
  set_transient('foobot-test', 1, (60 * 5));
  // Debug
  error_log("foobot-test has expired. Setting foobot-test", 0);
  return;
}
add_shortcode('foobot_transient', 'bd_f_test_transient');