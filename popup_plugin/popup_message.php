<?php
/*
  Plugin Name: Popup plugin
  Plugin URI: https://nikunjb.sgedu.site/prachi_Artsy/
  Description:This plugin allows admin to add pop pu messages to any page of website
  Version:1
  Author: Prachi Kamble
  License: GPLv2
 */

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];

$file='';

$url = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/js/popup.js';

register_activation_hook(__FILE__, 'message_popup_activate');
register_uninstall_hook(__FILE__, 'message_popup_uninstall');




/*
 *  plugin is activated.
 */
function message_popup_activate() {
	global $plugin_version;
	
	$default_value = array(
			'version' => $plugin_version,
			'title' => 'Title',
			'content' => 'This is the default content.',
			'first_visit' => '1',
			'time' => '0',
			'display_never' => '1',
			'exclude_fields' => '',
			'show_preference' => '',
			'exclude_post_fields' => ''
	);

	add_option('message_popup_settings',$default_value);
}


/**
 *  plugin is uninstalled.
 */
function message_popup_uninstall()
{
	delete_option('message_popup_settings',$default_value);
}


function message_popup_load_preview(){
	global $file;
	$option_css = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/css/options.css';
	wp_register_style( 'option_css', $option_css );
	wp_enqueue_style( 'option_css' );
}
add_action('admin_enqueue_scripts','message_popup_load_preview');


function message_popup_jquery_enqueuescripts() {
	global $url,$file,$plugin_version;
	$popup_time = get_message_popup_setting( 'time' );

	if( !wp_script_is( 'jquery' ) )
	{
		wp_enqueue_script('jquery');
	}

	wp_enqueue_script('jquery_message_model', $url, array('jquery'), $plugin_version);
	$close_on_esc = get_message_popup_setting( 'close_on_esc' );
	$translation_array = array( 'popup_time' =>  $popup_time, 'close_on_esc' => $close_on_esc );

	wp_localize_script( 'jquery_message_model', 'messagePopup', $translation_array );
	

}
add_action('wp_enqueue_scripts', 'message_popup_jquery_enqueuescripts');


function message_popup_init() {
	load_plugin_textdomain( 'message_popup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'message_popup_init');



function get_message_popup_setting($key= '')
{
	if($key == '')
		return '';
	else
	{
		$current_option = get_option('message_popup_settings');
		if(isset($current_option[$key])) {
			return $current_option[$key];
		}
		else
			return '';
	}
}


function update_all_settings($key= '', $value = '')
{
	$msg = 0;
	if($key == '')
		return true;
	else
	{
		update_option('message_popup_settings',$value);
		$msg = 1;
	}
	return $msg;
}


function message_popup_admin_add_page() {
	add_options_page( __('Message Popup Settings Page', 'message_popup' ), __( 'Message Popup', 'message_popup' ), 'manage_options', 'message_popup_page', 'message_popup_options_page');
}
add_action('admin_menu', 'message_popup_admin_add_page');



function message_popup_options_page() {
	global $msg_box;
	if(isset($_POST['message_popup_submit'])) {

		if(isset($_POST['display_never'])){
			$dis_never = $_POST['display_never'];
		}
		else {
			$dis_never = 0;
		}

		if(isset($_POST['close_on_esc'])){
			$close_on_esc = $_POST['close_on_esc'];
		}
		else {
			$close_on_esc = 0;
		}

		if(isset($_POST['exclude_fields'])){
			$exclude_page = $_POST['exclude_fields'];
		}
		else{
			$exclude_page = '';
		}

		$show_preference = !empty( $_POST['show_preference'] ) ? $_POST['show_preference'] : '';

		$changed_value = array(
				'title' => $_POST['title'],
				'content' => $_POST['content'],
				'close_on_esc' => $close_on_esc,
				'time' => $_POST['time'],
				'display_never' => $dis_never,
				'show_preference' => $show_preference,
				'exclude_fields' => $exclude_page,
		);
		
		$msg_box = update_all_settings('message_popup_settings', $changed_value);
	}
	global $file;
?>
<div class="wrap">
	<h2>
		<?php _e('Message Popup Options','message_popup');?>
	</h2>
	<?php if($msg_box) {?>
	<div class="updated">
		<p>
			<strong><?php _e('Settings Saved','message_popup');?></strong>
		</p>
	</div>
	<?php }?>
	<form method="post">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e('Title:','message_popup');?></th>
					<td><input name="title" type="text" id="title" class="regular-text" value="<?php echo esc_attr(get_message_popup_setting( 'title' ));?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Content:','message_popup');?></th>
					<td>
					<?php 
					wp_editor(
						stripslashes(get_message_popup_setting( 'content' )), 
						'popup_content', 
						array(
							'media_buttons' => false,
							'quicktags'     => array("buttons"=>"strong,em,link,b-quote,del,ins,img,ul,ol,li,code,close"),
							'textarea_name' => 'content',
							'textarea_rows' => 4,
							'tinymce'	=> false,
						) 
					);
					?>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><?php _e('Close On ESC:','message_popup');?></th>
					<td><input name="close_on_esc" type="checkbox" value="1" <?php  checked( '1', get_message_popup_setting( 'close_on_esc' ) ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Popup delay (in seconds):','message_popup');?></th>
					<td><input name="time" type="text" id="time" class="regular-text" value="<?php echo esc_attr(get_message_popup_setting( 'time' ));?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Show Display never link:','message_popup');?></th>
					<td><input type="checkbox" name="display_never" value="1" <?php checked( '1', get_message_popup_setting( 'display_never' ) ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Show Preference:','message_popup');?></th>
					<?php $show_preference = get_message_popup_setting('show_preference');  ?>
					<td>
						<select name="show_preference" tabindex="1" id="show_preference">
							<option value="" <?php selected($show_preference,'');?>><?php _e( 'All Users', 'message_popup' );?></option>
							<option value="login" <?php selected($show_preference,'login');?>><?php _e( 'Logged In User Only', 'message_popup' );?></option>
							<option value="guest" <?php selected($show_preference,'guest');?>><?php _e( 'Guest User Only', 'message_popup' );?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Exclude Pages:','message_popup');?></th>
					<?php  $exclude_fields = get_message_popup_setting('exclude_fields');  ?>
					<td>
						<select name="exclude_fields[]" size="3" multiple="multiple" tabindex="1" id="exclude_pages">
						<?php 
						$pages = get_pages();
						foreach ( $pages as $page ) { ?>
						<option
						<?php if (is_array($exclude_fields) && in_array($page->ID,$exclude_fields)) {echo "selected=selected";}?>
							value="<?php echo esc_attr($page->ID); ?>">
							<?php echo __("$page->post_title",'message_popup'); ?>
						</option>
						<?php 
						echo $option;
						}
						?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"></th>
					<td>
						<p class="submit">
							<input type="submit" name="message_popup_submit" id="message_popup_submit" class="button button-primary" value="<?php _e('Save Changes','message_popup');?>">
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<?php 
}



function message_popup_set_cookie() {
	global $post,$file, $url,$flag;
	$show_preference = get_message_popup_setting('show_preference');
	if( $show_preference == 'login' && ! is_user_logged_in() ) {
		return false;
	} elseif( $show_preference == 'guest' && is_user_logged_in() ) {
		return false;
	}
    
	if( empty( $post->ID ) ) return;

	$flag = 0;
	$post->ID;
	$exclude_fields = get_message_popup_setting('exclude_fields');
	$exclude_post_fields = get_message_popup_setting('exclude_post_fields');
	$url_css = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/css/style.css';
	wp_register_style( 'plugin_css', $url_css );
	wp_enqueue_style( 'plugin_css' );
	
	if(!empty($exclude_fields) && !empty($exclude_post_fields))
	{
		$both_combine_array = array_merge($exclude_fields,$exclude_post_fields);
	}
	if(!empty($both_combine_array))
	{
		if(!in_array($post->ID, $both_combine_array)){
			$flag = 1;
			if (!isset($_COOKIE['visit'])) {
				ob_start();
				setcookie('visit', 'set', time()+60,COOKIEPATH, COOKIE_DOMAIN, false);
				ob_flush();
			}
		}	
	}
	
	else if(!empty($exclude_fields))
	{
		if(!in_array($post->ID, $exclude_fields)){
			$flag = 1;
			if (!isset($_COOKIE['visit'])) {
				ob_start();
				setcookie('visit', 'set', time()+60,COOKIEPATH, COOKIE_DOMAIN, false);
				ob_flush();
			}
		}
	}
	else {
		$exclude_post_fields = array();
		if(!in_array($post->ID, $exclude_post_fields)){
			$flag = 1;
			if (!isset($_COOKIE['visit'])) {
				ob_start();
				setcookie('visit', 'set', time()+60,COOKIEPATH, COOKIE_DOMAIN, false);
				ob_flush();
			}
		}
	}
}
add_action( 'wp', 'message_popup_set_cookie' );


function get_message_popup() {
	global $flag;
	$show_preference = get_message_popup_setting('show_preference');
	if( $show_preference == 'login' && ! is_user_logged_in() ) {
		return false;
	} elseif( $show_preference == 'guest' && is_user_logged_in() ) {
		return false;
	}

	$show_hide = get_message_popup_setting('display_never');
	$fir_visit = get_message_popup_setting( 'first_visit' );
	if($fir_visit == '1')
	{
		if (isset($_COOKIE['visit']))
		{
		}
		else
		{
			if(!isset($_COOKIE['popup'])){

				$popup_title = get_message_popup_setting( 'title' );
				$content = stripslashes(get_message_popup_setting( 'content' ));
				$popup_content = apply_filters('the_content', $content);
				$popup_time = get_message_popup_setting( 'time' );

				if($popup_title == '') {
					$output = '<div class="popup_bg" style="display: none;">
					<div class="popup_block">
					<div class="inner">
					<a href="javascript:void(0);" class="btn_close" title="'.__("Close","message_popup").'">'.__("Close","message_popup").'</a>
					<div class="content_box blank">
					<div class="content_desc">
					<p>'.$popup_content.'</p></div>';
				}
				
				else {
					$output = '<div class="popup_bg" style="display: none;">
					<div class="popup_block">
					<div class="inner">
					<a href="javascript:void(0);" class="btn_close" title="'.__("Close","message_popup").'">'.__("Close","message_popup").'</a>
					<div class="heading_block">
					<span class="sprite icon01"></span>
					<div class="heading01">'.$popup_title.'</div>
					</div>
					<div class="content_box">
 					<div class="content_desc">
					<p>'.$popup_content.'</p></div>';
					
				}

				if($show_hide == 1) {
					$output = $output.'<p class="display"><a href="javascript:void(0);">'.__("Don't Show Again","message_popup").'</a></p>';
				}

				$output = $output.'</div></div></div></div><div id="overlay" style="display: none;"></div>';

				if($flag == 1) {
					echo $output;
					$flag=0;
				}
			}
			else {
				
			}
		}
	}
	else {
		if(!isset($_COOKIE['popup'])){
			$exclude_fields = get_message_popup_setting('exclude_fields');
			$exclude_post_fields = get_message_popup_setting('exclude_post_fields');
			$show_hide = get_message_popup_setting('display_never');
			$popup_title = get_message_popup_setting( 'title' );
			$content = stripslashes(get_message_popup_setting( 'content' ));
			$popup_content = apply_filters('the_content', $content);
			$popup_time = get_message_popup_setting( 'time' );

			if($popup_title == '') {
				$output = '<div class="popup_bg" style="display: none;">
				<div class="popup_block">
				<div class="inner">
				<a href="javascript:void(0);" class="btn_close" title="'.__("Close","message_popup").'">'.__("Close","message_popup").'</a>
				<div class="content_box blank">
				<div class="content_desc">
				<p>'.$popup_content.'</p></div>';
			}
			else {
				$output = '<div class="popup_bg" style="display: none;">
				<div class="popup_block">
				<div class="inner">
				<a href="javascript:void(0);" class="btn_close" title="'.__("Close","message_popup").'">'.__("Close","message_popup").'</a>
				<div class="heading_block">
				<span class="sprite icon01"></span>
				<div class="heading01">'.$popup_title.'</div>
				</div>
				<div class="content_box">
				<div class="content_desc">
				<p>'.$popup_content.'</p></div>';
			}
			
			if($show_hide == 1) {
				$output = $output.'<p class="display"><a href="javascript:void(0);">'.__("Don't Show Again","message_popup").'</a></p>';
			}

			$output = $output.'</div></div></div></div><div id="overlay" style="display: none;"></div>';

			if($flag == 1) {
				echo $output;
				$flag=0;
			}
		}
		else {
			
		}
	}
}

add_action('wp_head', 'get_message_popup');?>
