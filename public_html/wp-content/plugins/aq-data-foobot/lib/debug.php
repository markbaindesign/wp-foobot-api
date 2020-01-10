<?php

// A little debug function
function bd_pretty_debug( $var, $name = NULL ){
  // Debug
  echo '<h5>Debug</h5>';
  echo '<p>Variable: "$'.$name.'"</p>';
  echo '<pre><code>';
  var_dump( $var );
  echo '</code></pre>';
  // That's all!
}