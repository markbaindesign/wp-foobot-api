<?php
/**
 * Plugin Name: Foobot API plugin
 * Plugin URI: https://bain.design/wp-foobot-api
 * Description: Call your air quality data via the Foobot API.
 * Author: Bain Design
 * Version: 0.0.0
 * Author URI: http://bain.design
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: _bd_foobot_api
 * Plugin Slug: wp-foobot-api
 */

function baindesign_foobot_plugin_init() {

	include( plugin_dir_path( __FILE__ ) . 'admin/admin.php');
	
	if( class_exists( 'SitePress' ) ) {

		/**
		 * Get active languages
		 */
		add_action( 'admin_init', 'baindesign_get_languages' );
		function baindesign_get_languages(){
			$active_languages=icl_get_languages();
			// echo '<pre>';
			// var_dump( $active_languages );
			// echo '</pre>';
			// var_dump( $active_languages[] );

		}


 
		/**
		 * Get post language details
		 */
		function baindesign_foobot_get_post_language($post_id)
		{
			$post_language_details = apply_filters('wpml_post_language_details', NULL, $post_id);
			return $post_language_details;
		}

		/**
		 * Get the moderation email by language
		 * 
		 * This function checks for an ACF field which 
		 * contains the moderation email address for the
		 * specific language.
		 * 
		 * It achieves this by switching language, checking
		 * the value, then switching back to the default 
		 * language. 
		 * 
		 * As an input, this function requires the WPML language code. 
		 * 
		 * This fuction outputs an email address. 
		 * 
		 */
		function baindesign_foobot_get_local_moderation_email($lang_code)
		{
			do_action( 'wpml_switch_language', $lang_code );
			$email = get_field( 'comment_moderation_email_address', 'option' );
			do_action( 'wpml_switch_language', NULL );
			
			return $email;
		}

		/**
		 * Filter the comment moderation email
		 */

		function baindesign_foobot_comment_moderation_recipients($emails, $comment_id)
		{
			$comment = get_comment($comment_id);
			// error_log('Comment ID: ' . $comment->comment_ID);
			// error_log('Post ID: ' . $comment->comment_post_ID);
			$post = get_post( $comment->comment_post_ID );
			if( is_wp_error( $post ) ) {
				return false; // Bail early
			}
			$post_id = $post->ID;
			if( ! is_wp_error( $post_id ) ) {
				// error_log('Post ID: ' . $post_id);
				$lang_details = baindesign_foobot_wpml_get_post_language($post_id);
				if( ! is_wp_error( $post_id ) ) {
					$language_code = $lang_details['language_code'];  
					// error_log('Lang code: ' . $language_code);
					$emails = array ( baindesign_foobot_get_local_moderation_email($language_code) );      
					return $emails;
				}
			}

		}
		add_filter('comment_moderation_recipients', 'baindesign_foobot_comment_moderation_recipients', 11, 2);

	} else {

		/**
		 * Show an admin message to tell the user to install
		 * WPML plugin. 
		 */
		function baindesign_foobot_admin_warning() {
			ob_start(); ?>
			<div class="error">
				<?php _e('<p><strong>Error</strong>: You must activate the WPML plugin for the Language-specific Comment Moderation to work!</p>', '_bd_foobot' ); ?>
			</div>
			<?php
			echo ob_get_clean();
		}
		add_action('admin_notices', 'baindesign_foobot_admin_warning');
	}
}
add_action( 'plugins_loaded', 'baindesign_foobot_plugin_init' );