<?php

/**
* Execute the called tool. This function is called via ajax
*
* @return null
*/
function DBR_execute_called_tool(){

	// Security and role check
	check_ajax_referer('DBR_nonce', 'security');

	if(!current_user_can('administrator')){
		wp_send_json_error(__('Not sufficient permissions!', 'advanced-wp-reset'));
	}

	// Sanitize $_REQUEST['DBR_tool']
	$item_to_reset = sanitize_html_class($_REQUEST['DBR_item_to_reset']);

	switch($item_to_reset){

		case 'uploads-files':
			DBR_reset_uploads_dir();
			break;

		case 'themes-files' :
			$keep_active_theme = $_REQUEST['DBR_keep_active_theme'] == 0 ? false : true;
			DBR_delete_all_themes($keep_active_theme);
			break;

		case 'plugins-files' :
			DBR_delete_all_plugins(true);
			break;

		case 'wp-content-files' :

			DBR_reset_wp_content_dir();
			break;

		case 'mu-plugins-files' :
			DBR_reset_mu_plugins_dir();
			break;

		case 'htaccess-files' :
			DBR_delete_htaccess_file();
			break;

		case 'nav-menus' :
			// TODO
			break;

		case 'widgets' :
			// TODO
			break;

		case 'transients' :
			// TODO
			break;

		case 'themes-options' :
			// TODO
			break;

		case 'posts' :
			// TODO
			break;

		case 'pages' :
			// TODO
			break;

		case 'media' :
			// TODO
			break;

		case 'revisions' :
			// TODO
			break;

		case 'drafts' :
			// TODO
			break;

		case 'auto-drafts' :
			// TODO
			break;

		case 'trash-posts' :
			// TODO
			break;

		case 'categories' :
			// TODO
			break;

		case 'tags' :
			// TODO
			break;

		case 'all-comments' :
			DBR_delete_comments("all-comments");
			break;

		case 'pending-comments' :
			DBR_delete_comments("pending-comments");
			break;

		case 'spam-comments' :
			DBR_delete_comments("spam-comments");
			break;

		case 'trashed-comments' :
			DBR_delete_comments("trashed-comments");
			break;

		case 'pingbacks' :
			DBR_delete_comments("pingbacks");
			break;

		case 'trackbacks' :
			DBR_delete_comments("trackbacks");
			break;

		case 'users' :
			// TODO
			break;

		case 'user-roles' :
			// TODO
			break;

		default:
			wp_send_json_error(__('Cannot find this tool!', 'advanced-wp-reset'));
	}

	// If no error reported before, success and die
	wp_send_json_success();
}

/**
* Resets the database back to its initial status just like a fresh installation
*
* @return null
*/
function DBR_wp_reset(){

	// Verify ajax nonce before doing anything
	check_ajax_referer('DBR_nonce', 'security');

	require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
	global $current_user, $wpdb;

	$blogname 		= get_option('blogname');
	$admin_email 	= get_option('admin_email');
	$blog_public 	= get_option('blog_public');

	if($current_user->user_login != 'admin')
		$user = get_user_by('login', 'admin');

	if(empty($user->user_level ) || $user->user_level < 10)
		$user = $current_user;

	$prefix = str_replace('_', '\_', $wpdb->prefix );
	$tables = $wpdb->get_col("SHOW TABLES LIKE '{$prefix}%'" );

	foreach($tables as $table){
		$wpdb->query("DROP TABLE $table");
	}

	// Install wordpress
	$result  = wp_install($blogname, $user->user_login, $user->user_email, $blog_public);
	$user_id = $result['user_id'];

	// Set user password
	$query = $wpdb->prepare("UPDATE $wpdb->users SET user_pass = %s, user_activation_key = '' WHERE ID = %d", $user->user_pass, $user_id);
	$wpdb->query($query);

	// Say to wordpress that we will not use generated password
	if(get_user_meta($user_id, 'default_password_nag'))
		update_user_meta($user_id, 'default_password_nag', false);

	if(get_user_meta($user_id, $wpdb->prefix . 'default_password_nag'))
		update_user_meta($user_id, $wpdb->prefix . 'default_password_nag', false);

	// Add a small file to invite users rate the plugin
	$aDBc_upload_dir 	= wp_upload_dir();
	$aDBc_file_path 	= str_replace('\\' ,'/', $aDBc_upload_dir['basedir']) . "/DBR.txt";
	if(!file_exists($aDBc_file_path)){
		$handle = fopen($aDBc_file_path, "w");
		if($handle){
			fwrite($handle, "1");
		}
	}

	// Reactivate the current plugin
	@activate_plugin(DBR_PLUGIN_BASENAME);

	wp_die(); // Always die after ajax call
}

/**
* Prepares an array with all elements to reset in "Custom reset" tab
*
* @return array Array of elements to reset
*/
function DBR_prepare_custom_reset_items(){

	$all_items_array = array();

	$deals_with_db 			 = __('This tool modifies only the database. Files are not modified', 'advanced-wp-reset');
	$deals_with_files 		 = __('This tool modifies only files. Database is not modified', 'advanced-wp-reset');
	$deals_with_db_and_files = __('This tool modifies both the database and files', 'advanced-wp-reset');
	$deals_with_nothing 	 = __('This tool does not modify either the files or the database', 'advanced-wp-reset');

	// Add items related to files
	$all_items_array['files'] = array(

		'table_title' 	=> __('Reset Files', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'uploads-files', 	'title' => __("Clean 'uploads' folder", "advanced-wp-reset"), 	'deals_with' => DBR_generate_tooltip($deals_with_files)),
							array('type' => 'themes-files', 	'title' => __("Delete all themes", "advanced-wp-reset"), 		'deals_with' => DBR_generate_tooltip($deals_with_db_and_files)),
							array('type' => 'plugins-files', 	'title' => __("Delete all plugins", "advanced-wp-reset"), 		'deals_with' => DBR_generate_tooltip($deals_with_db_and_files)),
							array('type' => 'wp-content-files', 'title' => __("Clean 'wp-content' folder", "advanced-wp-reset"),'deals_with' => DBR_generate_tooltip($deals_with_files)),
							array('type' => 'mu-plugins-files', 'title' => __("Delete MU plugins", "advanced-wp-reset"), 		'deals_with' => DBR_generate_tooltip($deals_with_files)),
							array('type' => 'htaccess-files', 	'title' => __("Delete '.htaccess' file", "advanced-wp-reset"), 	'deals_with' => DBR_generate_tooltip($deals_with_files))
						));

	// Add items related to options
	/*$all_items_array['options'] = array(

		'table_title' 	=> __('Reset DB Options', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'nav-menus', 	 'title' => __('Delete navigation menus', 'advanced-wp-reset')),
							array('type' => 'widgets', 		 'title' => __('Delete widgets', 'advanced-wp-reset')),
							array('type' => 'transients',	 'title' => __('Delete transients', 'advanced-wp-reset')),
							array('type' => 'themes-options','title' => __('Delete theme options', 'advanced-wp-reset')),
						));*/

	// Add items related to posts
	/*$all_items_array['posts'] = array(

		'table_title' 	=> __('Reset Posts', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'posts',		'title' => __('Delete posts', 'advanced-wp-reset')),
							array('type' => 'pages',		'title' => __('Delete pages', 'advanced-wp-reset')),		
							array('type' => 'media',		'title' => __('Delete media', 'advanced-wp-reset')),	
							array('type' => 'revisions',	'title' => __('Delete revisions', 'advanced-wp-reset')),
							array('type' => 'drafts',		'title' => __('Delete drafts', 'advanced-wp-reset')),	
							array('type' => 'auto-drafts',	'title' => __('Delete auto-drafts', 'advanced-wp-reset')),	
							array('type' => 'trash-posts',	'title' => __('Delete trashed posts', 'advanced-wp-reset')),
						));*/

	// Add items related to taxonomies
	/*$all_items_array['taxonomies'] = array(

		'table_title' 	=> __('Reset Taxonomies', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'categories',	'title' => __('Delete categories', 'advanced-wp-reset')),
							array('type' => 'tags',			'title' => __('Delete tags', 'advanced-wp-reset')),
						));*/

	// Add items related to comments
	$all_items_array['comments'] = array(

		'table_title' 	=> __('Reset Comments', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'all-comments',		'title' => __('Delete all comments', 'advanced-wp-reset'),		'deals_with' => DBR_generate_tooltip($deals_with_db)),
							array('type' => 'pending-comments',	'title' => __('Delete pending comments', 'advanced-wp-reset'),	'deals_with' => DBR_generate_tooltip($deals_with_db)),
							array('type' => 'spam-comments',	'title' => __('Delete spam comments', 'advanced-wp-reset'),		'deals_with' => DBR_generate_tooltip($deals_with_db)),
							array('type' => 'trashed-comments',	'title' => __('Delete trashed comments', 'advanced-wp-reset'),	'deals_with' => DBR_generate_tooltip($deals_with_db)),
							array('type' => 'pingbacks',		'title' => __('Delete pingbacks', 'advanced-wp-reset'),			'deals_with' => DBR_generate_tooltip($deals_with_db)),
							array('type' => 'trackbacks',		'title' => __('Delete trackbacks', 'advanced-wp-reset'),		'deals_with' => DBR_generate_tooltip($deals_with_db)),
						));

	// Add items related to users
	/*$all_items_array['users'] = array(

		'table_title' 	=> __('Reset Users', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'users',		'title' => __('Delete all users', 'advanced-wp-reset')),
							array('type' => 'user-roles',	'title' => __('Reset user roles', 'advanced-wp-reset')),
						));*/

	// Add items related to local data
	/*$all_items_array['local-data'] = array(

		'table_title' 	=> __('Local Data', 'advanced-wp-reset'),
		'table_rows'  	=> array(
							array('type' => 'cookies', 			'title' => __("Delete cookies", "advanced-wp-reset"),			'deals_with' => DBR_generate_tooltip($deals_with_nothing)),
							array('type' => 'local-storage',	'title' => __('Delete local storage', 'advanced-wp-reset'),		'deals_with' => DBR_generate_tooltip($deals_with_nothing)),
							array('type' => 'session-storage',	'title' => __('Delete session storage', 'advanced-wp-reset'),	'deals_with' => DBR_generate_tooltip($deals_with_nothing)),
						));*/

	return $all_items_array;
}

/**
* Generate a tooltip with a specific content
*
* @return String tooltip info
*/
function DBR_generate_tooltip($text_inside){

	$tooltip = "<span class='DBR-tooltips-headers'>
					<img style='width:15px' class='DBR-info-image' src='".  DBR_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
						<span>" . $text_inside ." </span>
				</span>";

	return $tooltip;
}


/**
* Prepares an array with explanations for all elements to reset in "Custom reset" tab
*
* @return array Array of explanations
*/
function DBR_prepare_explanations_for_custom_reset_items(){

	$all_explanations_array = array();
	$uploads_dir 			= wp_upload_dir(null, false);
	$uploads_dir_path 		= "<span class='DBR-path-style'>/wp-content/uploads</span>";
	$must_use_path 			= "<span class='DBR-path-style'>/wp-content/mu-plugins</span>";
	$wp_content_path 		= "<span class='DBR-path-style'>/wp-content</span>";

	$all_explanations_array['uploads-files'] 	= __("All media uploads inside $uploads_dir_path directory will be deleted! This includes images, videos, music, documents, subfolders, etc.", 'advanced-wp-reset');

	$all_explanations_array['themes-files'] 	= __("All themes will be deleted. If you want to keep the current active theme, check the 'Keep active theme' checkbox, otherwise, it will be deleted too.", 'advanced-wp-reset');

	$all_explanations_array['plugins-files'] 	= __("All plugins will be deleted except the current plugin, it will still be active after the reset.", 'advanced-wp-reset');

	$all_explanations_array['wp-content-files'] = __("All files and folders inside $wp_content_path directory will be deleted, except 'index.php' and the following folders: 'plugins', 'themes', 'uploads' and 'mu-plugins'.", 'advanced-wp-reset');

	$all_explanations_array['mu-plugins-files'] = __("All Must-use plugins in $must_use_path will be deleted. These are plugins that cannot be disabled except by removing their files from the must-use directory.", 'advanced-wp-reset');

	$all_explanations_array['htaccess-files'] 	= __("The .htaccess file will be deleted. This is a critical WordPress core file used to enable or disable features of websites hosted on Apache.", 'advanced-wp-reset');

	$all_explanations_array['nav-menus']	 	= "";

	$all_explanations_array['widgets'] 			= "";

	$all_explanations_array['user-roles'] 		= "";

	$all_explanations_array['transients'] 		= "";

	$all_explanations_array['themes-options'] 	= "";

	$all_explanations_array['users'] 			= "";

	$all_explanations_array['posts'] 			= "";

	$all_explanations_array['pages'] 			= "";

	$all_explanations_array['media'] 			= "";

	$all_explanations_array['revisions'] 		= "";

	$all_explanations_array['categories'] 		= "";

	$all_explanations_array['tags'] 			= "";

	$all_explanations_array['drafts'] 			= "";	

	$all_explanations_array['auto-drafts'] 		= "";

	$all_explanations_array['trash-posts'] 		= "";

	$all_explanations_array['all-comments'] 	= __('All types of comments will be deleted. Comments meta will also be deleted.', 'advanced-wp-reset');

	$all_explanations_array['pending-comments'] = __('Pending comments will be deleted. These are the comments that are awaiting moderation.', 'advanced-wp-reset');

	$all_explanations_array['spam-comments'] 	= __('Spam comments will be deleted.', 'advanced-wp-reset');

	$all_explanations_array['trashed-comments'] = __('Trashed comments will be deleted. These are comments that you have deleted and sent to the Trash', 'advanced-wp-reset');

	$all_explanations_array['pingbacks'] 		= __('All Pingbacks will be deleted. Pingbacks allow you to notify other website owners that you have linked to their article on your website.', 'advanced-wp-reset');

	$all_explanations_array['trackbacks'] 		= __('All Trackbacks will be deleted. Although there are some minor technical differences, a trackback is basically the same things as a pingback. ', 'advanced-wp-reset');

	$all_explanations_array['cookies'] 			= __('WordPress cookies associated with authentication will be deleted. After running this tool, you will be logged out.', 'advanced-wp-reset');

	$all_explanations_array['local-storage'] 	= __('Local storage data will be deleted. These are a key-value pairs stored in your browser. They help to save data even after closing the browser.', 'advanced-wp-reset');

	$all_explanations_array['session-storage'] 	= __('Session storage data will be delete. These are a key-value pairs stored in your browser. They help to maintain data while the browser is open and will be automatically deleted when the browser is closed.', 'advanced-wp-reset');

	return $all_explanations_array;
}

/**
* Calculates the number of items to reset
*
* @return int|string The number of items
*/
function DBR_calculate_number_items_to_reset(){

	// Security and role check
	check_ajax_referer('DBR_nonce', 'security');

	if(!current_user_can('administrator')){
		wp_send_json_error ('Not sufficient permissions!');
	}

	// Sanitize $_REQUEST['DBR_item_type']
	$item_type = sanitize_html_class($_REQUEST['DBR_item_type']);

	switch($item_type){

		case 'uploads-files' :

			// Get uploads dir path
			$uploads_dir = wp_upload_dir(null, false);
			$total_items = DBR_calculate_items_in_folder($uploads_dir['basedir'], array('.', '..', 'DBR.txt'));
			break;

		case 'themes-files' :

			$total_items = count(wp_get_themes(array('errors' => null)));
			if($total_items > 0){
				$keep_active_theme = $_REQUEST['DBR_keep_active_theme'] == 0 ? false : true;
				if($keep_active_theme){
					$total_items = $total_items - 1;
				}
			}
			break;

		case 'plugins-files' :

			$plugins_list = get_plugins();
			unset($plugins_list[DBR_PLUGIN_BASENAME]);
			$total_items = count($plugins_list);
			break;

		case 'wp-content-files' :

			$total_items = DBR_calculate_items_in_folder(WP_CONTENT_DIR, array('.', '..', 'plugins', 'themes', 'uploads', 'mu-plugins', 'index.php'));
			break;

		case 'mu-plugins-files' :

			$total_items = DBR_calculate_items_in_folder(WPMU_PLUGIN_DIR, array('.', '..', 'index.php'));
			break;

		case 'htaccess-files' :

			clearstatcache();
			if(file_exists(get_home_path() . '.htaccess')){
				$total_items = 1;
			}else{
				$total_items = 0;
			}
			break;

		case 'nav-menus' :
			$total_items = "-";
			break;
		case 'widgets' :
			$total_items = "-";
			break;
		case 'transients' :
			$total_items = "-";
			break;
		case 'themes-options' :
			$total_items = "-";
			break;
		case 'posts' :
			$total_items = "-";
			break;
		case 'pages' :
			$total_items = "-";
			break;
		case 'media' :
			$total_items = "-";
			break;
		case 'revisions' :
			$total_items = "-";
			break;
		case 'drafts' :
			$total_items = "-";
			break;
		case 'auto-drafts' :
			$total_items = "-";
			break;
		case 'trash-posts' :
			$total_items = "-";
			break;
		case 'categories' :
			$total_items = "-";
			break;
		case 'tags' :
			$total_items = "-";
			break;
		case 'all-comments' :
			$total_items = DBR_calculate_comments("all-comments");
			break;
		case 'pending-comments' :
			$total_items = DBR_calculate_comments("pending-comments");
			break;
		case 'spam-comments' :
			$total_items = DBR_calculate_comments("spam-comments");
			break;
		case 'trashed-comments' :
			$total_items = DBR_calculate_comments("trashed-comments");
			break;
		case 'pingbacks' :
			$total_items = DBR_calculate_comments("pingbacks");
			break;
		case 'trackbacks' :
			$total_items = DBR_calculate_comments("trackbacks");
			break;
		case 'users' :
			$total_items = "-";
			break;
		case 'user-roles' :
			$total_items = "-";
			break;
		default:
			// Default to show
			$total_items = " - ";
	}

	echo $total_items;

	wp_die(); // Always die after ajax call
}

/**
* Calculates the number of files/folders in a folder
*
* @param string $folder Folder to start from
* @param array $except Array of names to excludes from the count
*
* @return int The number of files and folders
*/
function DBR_calculate_items_in_folder($folder, $except = array()){

	if(!file_exists($folder))
		return 0;

	// Get all files and folders names exluding . and .. in $except param
	if(is_dir($folder)){
		$all_files = array_diff(scandir($folder), $except);
		return count($all_files);
	}else{
		return 0;
	}
}

/**
* Calculates the number of comments according to their type (all comment, pending, spam, trash, pingback, trackback)
*
* @param string $comment_type the comment type to count
*
* @return int The number of comments
*/
function DBR_calculate_comments($comment_type){

	global $wpdb;
	$sql_query = "";

	switch($comment_type){
		case 'all-comments' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments";
			break;
		case 'pending-comments' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments WHERE comment_approved = '0'";
			break;
		case 'spam-comments' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments WHERE comment_approved = 'spam'";
			break;
		case 'trashed-comments' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments WHERE comment_approved = 'trash'";
			break;
		case 'pingbacks' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments WHERE comment_type = 'pingback'";
			break;
		case 'trackbacks' :
			$sql_query = "SELECT COUNT(*) from $wpdb->comments WHERE comment_type = 'trackback'";
			break;
		default:
			return " - ";
	}

	$total = $wpdb->get_var($sql_query);
	return $total;
}

/**
* Deletes all files and folders in "uploads" directory
*
* @return null
*/
function DBR_reset_uploads_dir(){

	// Get uploads dir path
	$uploads_dir = wp_upload_dir(null, false);

	clearstatcache();

	if(file_exists($uploads_dir['basedir']))
		DBR_delete_folder($uploads_dir['basedir'], $uploads_dir['basedir'], false);

}

/**
* Deletes all content in a folder without deleting the folder itself
*
* @param string $folder current folder
* @param string $original_folder original folder
* @param bool $delete_original_folder Either to delete or no the original folder
*
* @return bool
*/
function DBR_delete_folder($folder, $original_folder, $delete_original_folder = false){

	// Get all files and folders names exluding . and .. and "DBR.txt"
	$all_files = array_diff(scandir($folder), array('.', '..', 'DBR.txt'));

	foreach($all_files as $file){
		if(is_dir($folder . DIRECTORY_SEPARATOR . $file)){
			DBR_delete_folder($folder . DIRECTORY_SEPARATOR . $file, $original_folder, $delete_original_folder);
		}else{
			@unlink($folder . DIRECTORY_SEPARATOR . $file);
		}
	}

	// Delete the original folder if $delete_original_folder == true, otherwise keep it. Delete all other content folders
	if($delete_original_folder == true || $folder != $original_folder){
		$result = @rmdir($folder);
		return $result;
	}else{
		return true;
	}
}

/**
* Deletes all themes
*
* @param bool $keep_active_theme Keep default theme
*
* @return null
*/
function DBR_delete_all_themes($keep_active_theme){

	// Return all themes
	$all_themes = wp_get_themes(array('errors' => null));

	$active_theme = get_template();
	$active_child = get_stylesheet();

	if($keep_active_theme == true){
		unset($all_themes[$active_theme]);
		unset($all_themes[$active_child]);
	}

	foreach($all_themes as $name_slug => $info){
		delete_theme($name_slug);
	}

	// After deleting all themes, update DB option
	if($keep_active_theme == false){
		update_option('template', '');
		update_option('stylesheet', '');
		update_option('current_theme', '');
	}
}

/**
* Deletes all plugins
*
* @param bool $keep_this_plugin Keep 'Advanced WP Reset' plugin or not
*
* @return null
*/
function DBR_delete_all_plugins($keep_this_plugin){

	$plugins_list = get_plugins();

	if($keep_this_plugin == true){
		unset($plugins_list[DBR_PLUGIN_BASENAME]);
	}

	if(!empty($plugins_list)){
		deactivate_plugins(array_keys($plugins_list));
		delete_plugins(array_keys($plugins_list));
	}
}

/**
* Deletes all 'wp-content' folder content except these folders: 'plugins', 'themes', 'uploads', 'mu-plugins' and 'index.php'
*
* @return null
*/
function DBR_reset_wp_content_dir(){

	if(!file_exists(WP_CONTENT_DIR))
		return;

	// Get all folders and files in 'wp-content' except the array in prameter.
	$all_files = array_diff(scandir(WP_CONTENT_DIR), array('.', '..', 'plugins', 'themes', 'uploads', 'mu-plugins', 'index.php'));

	// Delete
	foreach($all_files as $file){
		if(is_dir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file)){
			DBR_delete_folder(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file, "", true);
		}else{
			@unlink(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file);
		}
	}
}

/**
* Deletes all 'mu-plugins' content
*
* @return null
*/
function DBR_reset_mu_plugins_dir(){

	if(!file_exists(WPMU_PLUGIN_DIR))
		return;

	// Get all folders and files in 'mu-plugins' except the array in prameter
	$all_files = array_diff(scandir(WPMU_PLUGIN_DIR), array('.', '..', 'index.php'));

	// Delete
	foreach($all_files as $file){
		if(is_dir(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . $file)){
			DBR_delete_folder(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . $file, "", true);
		}else{
			@unlink(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . $file);
		}
	}
}

/**
 * Deletes .htaccess file
 *
 * @return null
 */
function DBR_delete_htaccess_file(){

	$htaccess_file = get_home_path() . '.htaccess';

	clearstatcache();

	if(!is_readable($htaccess_file)){
		wp_send_json_error(__('Cannot be deleted! The Htaccess file does not exist!', 'advanced-wp-reset'));
	}

	if(!is_writable($htaccess_file)){
		wp_send_json_error(__('Cannot be deleted! Htaccess file is not writable!', 'advanced-wp-reset'));
	}

	if(unlink($htaccess_file)){
		wp_send_json_success();
	}else{
		wp_send_json_error(__('Cannot be deleted! Unknown error!', 'advanced-wp-reset'));
	}
}

/**
* Deletes the type of comments in parameter
*
* @param string $comment_type the comment type to delete
*
* @return null
*/
function DBR_delete_comments($comment_type){

	global $wpdb;
	$sql_query = "";

	switch($comment_type){
		case 'all-comments' :
			$sql_query = "DELETE from $wpdb->comments";
			$wpdb->query("TRUNCATE TABLE $wpdb->commentmeta");
			break;
		case 'pending-comments' :
			$sql_query = "DELETE from $wpdb->comments WHERE comment_approved = '0'";
			break;
		case 'spam-comments' :
			$sql_query = "DELETE from $wpdb->comments WHERE comment_approved = 'spam'";
			break;
		case 'trashed-comments' :
			$sql_query = "DELETE from $wpdb->comments WHERE comment_approved = 'trash'";
			break;
		case 'pingbacks' :
			$sql_query = "DELETE from $wpdb->comments WHERE comment_type = 'pingback'";
			break;
		case 'trackbacks' :
			$sql_query = "DELETE from $wpdb->comments WHERE comment_type = 'trackback'";
			break;
		default:
			return " - ";
	}

	$total = $wpdb->get_var($sql_query);
	return $total;
}


?>