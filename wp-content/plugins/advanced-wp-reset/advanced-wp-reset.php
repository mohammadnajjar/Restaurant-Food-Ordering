<?php
if (!defined('ABSPATH') || !is_main_site()) return;

/*
Plugin Name: Advanced WordPress Reset
Plugin URI: http://sigmaplugin.com/downloads/advanced-wordpress-reset
Description: Reset your WordPress database to its first original status, just like if you make a fresh installation.
Version: 1.5
Author: Younes JFR.
Author URI: http://www.sigmaplugin.com
Contributors: symptote
Text Domain: advanced-wp-reset
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class DBR_Advanced_DB_Reset {

	public function __construct(){

		// Define common constants that should be modified in each version
		if(!defined("DBR_PLUGIN_VERSION")) 	define("DBR_PLUGIN_VERSION", "1.5");

		// Load text-domain, ajax functions...
		add_action('plugins_loaded', array($this, 'plugins_loaded'));

		// Load CSS and JS
		add_action('admin_enqueue_scripts', array($this, 'DBR_load_styles_and_scripts'));		

		// Register activation, deactivation and uninstall hooks of the plugin
		register_activation_hook	(__FILE__, array($this, 'DBR_activate_plugin'));
		register_deactivation_hook	(__FILE__, array($this, 'DBR_deactivate_plugin'));
		register_uninstall_hook		(__FILE__, array('DBR_Advanced_DB_Reset', 'DBR_uninstall'));

		// Add admin notice to rate plugin
		add_action('admin_notices', array($this, 'DBR_rate_notice'));
		add_action('admin_init', array($this, 'DBR_ignore_notice'));

	}

	// Do this on plugins loded : prevent conflict between free and pro, load text-domain and check if we should update settings in DB
	public function plugins_loaded(){

		// Include functions
		include_once 'includes/functions.php';

		// Add actions for Ajax
		add_action('wp_ajax_DBR_wp_reset', 							'DBR_wp_reset');
		add_action('wp_ajax_DBR_calculate_number_items_to_reset', 	'DBR_calculate_number_items_to_reset');
		add_action('wp_ajax_DBR_execute_called_tool', 				'DBR_execute_called_tool');

		// Define other variables
		if(!defined("DBR_PLUGIN_DIR_PATH")) 				define("DBR_PLUGIN_DIR_PATH", plugins_url('' , __FILE__));
		if(!defined("DBR_PLUGIN_BASENAME")) 				define("DBR_PLUGIN_BASENAME", plugin_basename(__FILE__));
		if(!defined("WP_CONTENT_DIR")) 						define("WP_CONTENT_DIR", ABSPATH . 'wp-content');
		if(!defined("WPMU_PLUGIN_DIR")) 					define("WPMU_PLUGIN_DIR", WP_CONTENT_DIR . 'mu-plugins');

		// Add submenu under tools
		add_action('admin_menu', array($this, 'DBR_add_admin_menu'));

		load_plugin_textdomain('advanced-wp-reset', false, dirname(plugin_basename(__FILE__)) . '/languages');

	}

	/// Function to add submenu under tools
	function DBR_add_admin_menu(){

		global $DBR_tool_submenu;
		$DBR_tool_submenu = add_submenu_page('tools.php', 'Advanced WP Reset', 'Advanced WP Reset', 'manage_options', 'advanced_wp_reset', array($this, 'DBR_main_page_callback'));

	}

	// Load CSS and JS
	function DBR_load_styles_and_scripts($hook){

		// Enqueue our js and css in the plugin pages only
		global $DBR_tool_submenu;
		if($hook != $DBR_tool_submenu){
			return;
		}

		wp_enqueue_style('DBR_css', DBR_PLUGIN_DIR_PATH . '/css/admin.css');
		wp_enqueue_script('DBR_js', DBR_PLUGIN_DIR_PATH . '/js/admin.js');

		wp_enqueue_style('sweet2_css', DBR_PLUGIN_DIR_PATH . '/css/sweetalert2.min.css');
		wp_enqueue_script('sweet2_js', DBR_PLUGIN_DIR_PATH . '/js/sweetalert2.min.js');

		// The wp_localize_script allows us to output the ajax_url path for our script to use.
		wp_localize_script('DBR_js', 'DBR_ajax_obj', array(

			'ajaxurl' 		 	=> admin_url('admin-ajax.php'),
			'images_path'	 	=> DBR_PLUGIN_DIR_PATH . "/images/",
			'are_you_sure'   	=> __('Are you sure to continue?', 'advanced-wp-reset'),
			'warning_msg' 	 	=> __('You are about to reset your database. Any content will be lost!', 'advanced-wp-reset'),
			'irreversible_msg' 	=> __('This operation is irreversible!', 'advanced-wp-reset'),
			'custom_warning' 	=> __('You are about to perform the following action:', 'advanced-wp-reset'),			
			'type_reset'  	 	=> sprintf(__('Please type the word "<b>%s</b>" correctly in the text box','advanced-wp-reset'), "reset"),
			'processing' 	 	=> __('Processing...', 'advanced-wp-reset'),
			'done' 			 	=> __('Done!', 'advanced-wp-reset'),
			'cancel' 		 	=> __('Cancel', 'advanced-wp-reset'),
			'Continue' 		 	=> __('Continue', 'advanced-wp-reset'),
			'keep_active_theme' => __('Keep active theme', 'advanced-wp-reset'),
			'keep_this_plugin' 	=> __('Keep current plugin', 'advanced-wp-reset'),
			'unknown_error' 	=> __('Unknown error!', 'advanced-wp-reset'),
			'ajax_nonce'	 	=> wp_create_nonce('DBR_nonce'),

		));

		//wp_enqueue_script('jquery');
		//wp_enqueue_script('jquery-ui-dialog');
		//wp_enqueue_style('wp-jquery-ui-dialog');
		wp_enqueue_script( 'jquery-ui-sortable' );	

	}

	// Register activation of the plugin
	function DBR_activate_plugin(){
		// Anything to do on deactivation? Maybe later...
	}

	// Register deactivation hook
	function DBR_deactivate_plugin($network_wide){
		// Anything to do on deactivation? Maybe later...
	}

	// Register UNINSTALL hook
	public static function DBR_uninstall(){
		// Anything to do on uninstall? Maybe later...
	}

	// Add admin notice to rate plugin
	function DBR_rate_notice(){

		$DBR_upload_dir = wp_upload_dir();
		$DBR_file_path = str_replace('\\' ,'/', $DBR_upload_dir['basedir']) . "/DBR.txt";

		if(file_exists($DBR_file_path)){
			$content = file_get_contents($DBR_file_path);
			// Return in case the file contains 0
			if($content != "1")
				return;
		}else{
			// Return in case the file does not exist
			return;
		}

		$DBR_new_URI = $_SERVER['REQUEST_URI'];
		$DBR_new_URI = add_query_arg('DBR_rate', "0", $DBR_new_URI);
		// Style should be done here because it is not loaded outside the plugin admin panel
		$style_botton = "background:#f0f5fa;padding:5px;text-decoration:none;margin-right:10px;border:1px solid #999;border-radius:4px"; ?>

		<div style="padding:15px !important;" class="updated DBR-top-main-msg">
			<span style="font-size:16px;color:green;font-weight:bold;"><?php _e('Awesome!', 'advanced-wp-reset'); ?></span>
			<p style="font-size:14px;line-height:30px">
				<?php _e('The plugin "Advanced DB Reset" just helped you reset your database to a fresh installation with success!', 'advanced-wp-reset'); ?>
				<br/>
				<?php _e('Could you please kindly help the plugin in your turn by giving it 5 stars rating? (Thank you in advance)', 'advanced-wp-reset'); ?>
				<div style="font-size:14px;margin-top:10px">
				<a style="<?php echo $style_botton ?>" target="_blank" href="https://wordpress.org/support/plugin/advanced-wp-reset/reviews/?filter=5">
				<?php _e('Ok, you deserved it', 'advanced-wp-reset'); ?></a>
				<form method="post" action="" style="display:inline">
				<input type="hidden" name="dont_show_rate" value=""/>
				<a style="<?php echo $style_botton ?>" href="<?php echo $DBR_new_URI; ?>"><?php _e('I already did', 'advanced-wp-reset'); ?></a>
				<a style="<?php echo $style_botton ?>" href="<?php echo $DBR_new_URI; ?>"><?php _e('Please don\'t show this again', 'advanced-wp-reset'); ?></a>
				</form>
				</div>
			</p>
		</div>	
	<?php
	}

	// Hide rating msg box if the user clicked on the button to hide it
	function DBR_ignore_notice(){

		if(isset($_GET['DBR_rate']) && $_GET['DBR_rate'] == "0"){

			$DBR_upload_dir = wp_upload_dir();
			$DBR_file_path 	= str_replace('\\' ,'/', $DBR_upload_dir['basedir']) . "/DBR.txt";

			$handle = fopen($DBR_file_path, "w");
			if($handle){
				fwrite($handle, "0");
			}
		}
	}

	// The admin page of the plugin
	function DBR_main_page_callback(){ ?>
		<div class="wrap">

			<div>
				<table width="100%" cellspacing="0">
					<tr style="background:#fff;border:0px solid #eee;">

						<td style="padding:10px 10px 10px 20px">
							<img style="width:50px" src="<?php echo DBR_PLUGIN_DIR_PATH; ?>/images/icon-128x128.png"/>
						</td>

						<td width="100%">
							<div style="background:#fff;padding:10px;margin-bottom:10px;">
								<?php
								$DBR_plugin_title = "Advanced WP Reset '" . DBR_PLUGIN_VERSION . "'";
								?>
								<div style="font-size: 20px;font-weight: 400;margin-bottom:10px"><?php echo $DBR_plugin_title; ?></div>
								<div style="border-top:1px dashed #eee;padding-top:4px">
									<span class="DBR-row-text"><?php _e('By', 'advanced-wp-reset'); ?></span>
									<a class="DBR-sidebar-link" href="https://profiles.wordpress.org/symptote/" target="_blank">Younes JFR.</a>
									&nbsp;|&nbsp;
									<span class="DBR-row-text"><?php _e('Need help?', 'advanced-wp-reset'); ?></span>
									<a class="DBR-sidebar-link" href="http://sigmaplugin.com/contact" target="_blank"><?php _e('Contact me', 'advanced-wp-reset'); ?></a>							
								</div>
							</div>
						</td>

						<td style="text-align:center">
							<div style="background:#fff;padding:10px;margin-bottom:10px;">
								<a class="DBR-sidebar-link" href="http://sigmaplugin.com/contact" target="_blank">
									<img style="width:50px" src="<?php echo DBR_PLUGIN_DIR_PATH; ?>/images/help.svg"/>
									<br/>
									<span><?php _e('Support', 'advanced-wp-reset'); ?></span>
								</a>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<h1 style="font-size:10px"></h1>

			<div class="DBR-margin-r-300" style="margin-top:30px">
				<div class="DBR-tab-box">

					<a id="reset_all_tab" class="DBR-tablinks"><?php _e('Reset all', 'advanced-wp-reset') ?></a>
					<a id="custom_reset_tab" class="DBR-tablinks"><?php _e('Custom reset', 'advanced-wp-reset') ?></a>
					<!--<a id="reset_tables_tab" class="DBR-tablinks"><?php //_e('Reset tables', 'advanced-wp-reset') ?></a>
					<a id="profiles_tab" class="DBR-tablinks"><?php //_e('Profiles', 'advanced-wp-reset') ?></a>
					<a id="collections_tab" class="DBR-tablinks"><?php //_e('Collections', 'advanced-wp-reset') ?></a>
					<a id="settings_tab" class="DBR-tablinks"><?php //_e('Settings', 'advanced-wp-reset') ?></a>-->

					<div class="DBR-tab-box-div"> <?php
						include_once 'includes/reset.php';
						include_once 'includes/custom_reset.php';
						include_once 'includes/settings.php'; ?>
					</div>

				</div>

				<div class="DBR-sidebar"><?php include_once 'includes/sidebar.php'; ?></div>

			</div>
		</div>
	<?php 
	}
}

// Get instance
new DBR_Advanced_DB_Reset();
