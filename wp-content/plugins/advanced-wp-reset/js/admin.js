jQuery(document).ready(function(){


	/******************************************************************************
	*
	* When the page is loaded/refreshed, test if we should load the last active tab
	*
	******************************************************************************/

	var current_tab_id = localStorage.getItem("DBR_current_tab");
	if(current_tab_id != '' && current_tab_id != null){

		// Add class 'active' to the current active tab and show its contents
		showCurrentTabAndContent(jQuery("#" + current_tab_id));

	}else{
		// If not tab is saved in the browser, show the first tab
		showCurrentTabAndContent(jQuery("#reset_all_tab"));
	}

	/**********************************************************************************************
	*
	* When the page is loaded/refreshed, test which tables (in 'custom reset' tab) should be hidden
	*
	***********************************************************************************************/

	whichTablesShouldBeHidden();

	// This function hides tables that should be hidden
	function whichTablesShouldBeHidden(){

		var hidden_tables = localStorage.getItem("DBR_hidden_tables");
		if(hidden_tables != '' && hidden_tables != null){

			// Get array with tables names that should be hidden
			var items_array = hidden_tables.split(":");

			for(var i = 0; i < items_array.length; i++ ){
				var name = items_array[i];
				if(name != ""){
					// Hide the table
					jQuery('#DBR_table_custom_reset_' + name).hide();
					// Change arrow class
					jQuery('#DBR_accordion_link_' + name).removeClass('dashicons-arrow-up');
					jQuery('#DBR_accordion_link_' + name).addClass('dashicons-arrow-down');
				}
			}
		}
	}

	/**********************************************************************************************
	*
	* When the page is loaded/refreshed, reorder the tables order in "custom reset" tab if needed
	*
	**********************************************************************************************/
	var tables_order = localStorage.getItem("DBR_tables_order");
	if(tables_order != '' && tables_order != null){

		// Get array with tables names that should be ordered
		var items_array = tables_order.split(":");

		for(var i = 0; i < items_array.length; i++ ){
			var name = items_array[i];
			if(name != ""){
				jQuery("#order_" + name).remove().appendTo("#my_sortable");
			}
		}
	}

	/********************************************************************************************
	*
	* When the page is loaded/refreshed, calculate number of items to reset in "custom reset" tab
	*
	********************************************************************************************/

	// Loop over all tables items titles and calculate
	jQuery('.DBR-item-to-reset-total').each(function(i, obj){

		// Get name
		var accordion_id 	= jQuery(obj).attr('id');
		var names_array 	= accordion_id.split("_");
		var item_name 		= names_array[names_array.length-1];

		// If we are calculating local data items:
		if(item_name == "cookies" || item_name == "local-storage" || item_name == "session-storage"){

			if(item_name == "cookies"){
				var total = DBR_count_cookies();
			}else if(item_name == "local-storage"){
				var total = DBR_count_local_storage();
			}else if(item_name == "session-storage"){
				var total = DBR_count_session_storage();
			}

			jQuery('#DBR_spinning_checkbox_' + item_name).hide();
			jQuery('#DBR_total_' + item_name).show();
			jQuery('#DBR_total_' + item_name).text('(' + total + ')');

			return;
		}

		calculateTotalItems(item_name);

	});

	// Processing tabs clicks
	jQuery('.DBR-tablinks').on('click', function(e){

		// Prevent button from its default behaviour
		e.preventDefault();

		// Add class 'active' to the current active tab and show its contents
		showCurrentTabAndContent(jQuery(this));

		// Save the current tab in the browser
		localStorage.setItem("DBR_current_tab", jQuery(this).attr("id"));

	});

	// This function shows the current tab and its content and hides all other contents
	function showCurrentTabAndContent(my_jquery_object){

		// Add class 'active' to the current active tab
		jQuery('.DBR-tablinks').removeClass('active');
		my_jquery_object.addClass('active');

		// Show corresponding content to the current tab
		jQuery('.DBR_tabcontent').hide();
		var content_to_show = my_jquery_object.attr("id") + "_content";
		jQuery('#'+content_to_show).show();
	}

	// Reset wordpress back to its first status
	jQuery('#DBR_reset_button').on('click', function(e){

		// Prevent doaction button from its default behaviour
		e.preventDefault();

		var confiramation_msg = jQuery('#DBR_reset_comfirmation').val();

		if(confiramation_msg != "reset"){

			// If confirmation != reset, show msg box
			Swal.fire({
			  icon					: 'error',
			  confirmButtonColor	: '#0085ba',
			  showCloseButton		: true,
			  html					: DBR_ajax_obj.type_reset
			})

		}else{

			Swal.fire({
				title				: '<font size="4px" color="red">' + DBR_ajax_obj.are_you_sure + '</font>',
				text				: DBR_ajax_obj.warning_msg + " " + DBR_ajax_obj.irreversible_msg,
				imageUrl			: DBR_ajax_obj.images_path + 'alert_delete.svg',
				imageWidth			: 60,
				imageHeight			: 60,
				showCancelButton	: true,
				showCloseButton		: true,
				cancelButtonText	: DBR_ajax_obj.cancel,
				cancelButtonColor	: '#555',
				confirmButtonText	: DBR_ajax_obj.Continue,
				confirmButtonColor	: '#0085ba',
				focusCancel 		: true,

			}).then((result) => {

				// If the user clicked on "confirm", call reset function
				if(result.value){

					// Show processing msg box
					showProcessingMsgBox();

					jQuery.ajax({
						type 	: "post",
						url		: DBR_ajax_obj.ajaxurl,
						cache	: false,
						data: {
							'action'	: 'DBR_wp_reset',
							'security'	: DBR_ajax_obj.ajax_nonce
						},
						success: function(result) {
							Swal.fire(DBR_ajax_obj.done, '', 'success')
						},
						complete: function(){
							// wait for 1 sec then reload the page.
							setTimeout(function(){location.reload();}, 1000);
						}
					});
				}
			})
		}
	});

	// Check/uncheck the checkbox in custom reset Tab when clicking on the title of the item to reset
	jQuery('.DBR-item-to-reset-title').on('click', function(e){

		// Get the id of the clicked title
		var title_type = jQuery(this).attr("value");

		// Get the checkbox object in the same line
		var current_checkbox = jQuery("#DBR_checkbox_" + title_type);

		if(current_checkbox.prop("checked") == true){
			current_checkbox.prop("checked", false);
		}else{
			current_checkbox.prop("checked", true);
		}
	});

	// Hide/show tables when clicking on accordion right arrow (in custom reset tab)
	jQuery('.DBR-accordion').on('click', function(e){

		// Get the id of the clicked arrow
		var my_id 			= jQuery(this).attr("id");
		var items_array 	= my_id.split("_");
		var itemname 		= items_array[items_array.length-1];
		var my_table 		= jQuery('#DBR_table_custom_reset_' + itemname);
		var accordion_icon 	= jQuery('#DBR_accordion_link_' + itemname);

		// If the table is visible/shown
		if(my_table.is(":visible")){

			// Change arrow class
			accordion_icon.removeClass('dashicons-arrow-up');
			accordion_icon.addClass('dashicons-arrow-down');
			// Hide the table
			my_table.fadeOut("fast");

		}else{

			// Change arrow class
			accordion_icon.removeClass('dashicons-arrow-down');
			accordion_icon.addClass('dashicons-arrow-up');
			// Show the table
			my_table.fadeIn("fast");
		}

		// When hiding a table, save its ID in localStorage to hide it again if the page is reloaded
		var list_of_tables = "";
		jQuery('.dashicons-arrow-down').each(function(i, obj){

			// Get name
			var accordion_id 	= jQuery(obj).attr('id');
			var names_array 	= accordion_id.split("_");
			var name 			= names_array[names_array.length-1];

			list_of_tables = name + ":" + list_of_tables;
		});

		// If all tables are shown, save empty string
		if(list_of_tables == ""){
			localStorage.setItem("DBR_hidden_tables", "");
		}else{
			localStorage.setItem("DBR_hidden_tables", list_of_tables);
		}

	});

	/*****************************************************************************
	*
	* When clicking on "Keep active theme", recalculate the total themes to delete
	*
	******************************************************************************/
	jQuery('#DBR_keep_active_theme').on('click', function(){
		calculateTotalItems("themes-files");
	});

	/*****************************************************************************
	*
	* Reset items when clicking on "Run reset now" button in "Custom reset" tab
	*
	******************************************************************************/
	jQuery('.DBR-custom-reset-button').on('click', function(){

		// Get item name and title to delete
		var item_name		= jQuery(this).attr('name');
		var item_title		= jQuery(this).attr('value');

		// When clicking on 'Delete themes' button, check if keep active theme and keep current plugin are checked
		var keep_current	= "";
		if(item_name == "themes-files" && jQuery("#DBR_keep_active_theme").prop("checked") == true){
			keep_current = " + <b>" + DBR_ajax_obj.keep_active_theme + "</b>";
		}

		Swal.fire({
			title				: '<font size="4px" color="red">' + DBR_ajax_obj.are_you_sure + '</font>',
			html				: DBR_ajax_obj.custom_warning + "<br><br><b>" + item_title + "</b>" + keep_current,
			footer				: "<font color='red'>" + DBR_ajax_obj.irreversible_msg + "</font>",
			imageUrl			: DBR_ajax_obj.images_path + 'alert_delete.svg',
			imageWidth			: 60,
			imageHeight			: 60,
			showCancelButton	: true,
			showCloseButton		: true,
			cancelButtonText	: DBR_ajax_obj.cancel,
			cancelButtonColor	: '#555',
			confirmButtonText	: DBR_ajax_obj.Continue,
			confirmButtonColor	: '#0085ba',
			focusCancel 		: true,

		}).then((result) => {

			// If the user clicked on "confirm", call reset function
			if(result.value){

				// Show processing icon
				showProcessingMsgBox();

				// If the user clicked on "Reset Local Data" tools
				if(item_name == "cookies" || item_name == "local-storage" || item_name == "session-storage"){

					// Show spinning
					jQuery('#DBR_total_' + item_name).hide();
					jQuery('#DBR_spinning_checkbox_' + item_name).show();

					if(item_name == "cookies"){

						DBR_clear_local_data(true, false, false);
						var total = DBR_count_cookies();

					}else if(item_name == "local-storage"){

						DBR_clear_local_data(false, true, false);
						var total = DBR_count_local_storage();

					}else if(item_name == "session-storage"){

						DBR_clear_local_data(false, false, true);
						var total = DBR_count_session_storage();

					}

					jQuery('#DBR_spinning_checkbox_' + item_name).hide();
					jQuery('#DBR_total_' + item_name).show();
					jQuery('#DBR_total_' + item_name).text('(' + total + ')');

					Swal.fire({
						title					: '<font size="4px">' + DBR_ajax_obj.done + '</font>',
						icon					: 'success',
						showConfirmButton		: false,
						timer					: 1000,
						timerProgressBar		: true,
					});

					// Return here and prevent calling code bellow
					return;
				}

				// If the user clicked on all other reset tools except "Reset Local Data"
				// Get "keep active theme" checkbox value
				var DBR_keep_active_theme = 0;
				if(jQuery("#DBR_keep_active_theme").prop("checked") == true){
					DBR_keep_active_theme = 1;
				}

				jQuery.ajax({
					type 	: "post",
					url		: DBR_ajax_obj.ajaxurl,
					cache	: false,
					data: {
						'action'				: 'DBR_execute_called_tool',
						'security'				: DBR_ajax_obj.ajax_nonce,
						'DBR_item_to_reset' 	: item_name,
						'DBR_keep_active_theme' : DBR_keep_active_theme,
					},
					success: function(result){

						// Show success/error message
						if(true === result.success){

							Swal.fire({
								title					: '<font size="4px">' + DBR_ajax_obj.done + '</font>',
								icon					: 'success',
								showConfirmButton		: false,
								timer					: 1000,
								timerProgressBar		: true,
							});

						}else{
							Swal.fire({
								html	: '<font size="4px">' + result.data + '</font>',
								icon	: 'error'
							});
						}

						// Recalculate the corresponding remaining number of items
						calculateTotalItems(item_name);

						// If we have deleted a specific type of comments, recalculate the "AlL comments" section as well
						var comments_types = ["pending-comments", "spam-comments", "trashed-comments", "pingbacks", "trackbacks"];
						if(jQuery.inArray(item_name, comments_types) != -1){
							calculateTotalItems("all-comments");
						}

					},
					error: function(){
						Swal.fire({
							html	: '<font size="4px">' + DBR_ajax_obj.unknown_error + '</font>',
							icon	: 'error'
						});
					}
	
				});
			}
		})
	});


	/*****************************************************************************
	*
	* This function calculates the total number of items for specific tools
	*
	******************************************************************************/
	function calculateTotalItems(item_name){

		// Get "keep active theme" checkbox value
		var DBR_keep_active_theme = 0;
		if(jQuery("#DBR_keep_active_theme").prop("checked") == true){
			DBR_keep_active_theme = 1;
		}

		jQuery('#DBR_total_' + item_name).hide();
		jQuery('#DBR_spinning_checkbox_' + item_name).show();

		jQuery.ajax({
			type 	: "post",
			url		: DBR_ajax_obj.ajaxurl,
			cache	: false,
			data: {
				'action'				: 'DBR_calculate_number_items_to_reset',
				'security'				: DBR_ajax_obj.ajax_nonce,
				'DBR_item_type' 		: item_name,
				'DBR_keep_active_theme' : DBR_keep_active_theme,
			},
			success: function(result){
				jQuery('#DBR_spinning_checkbox_' + item_name).hide();
				jQuery('#DBR_total_' + item_name).show();
				if(jQuery.isNumeric(result)){
					jQuery('#DBR_total_' + item_name).text('(' + result + ')');
				}else{
					jQuery('#DBR_total_' + item_name).text('(' + 'NaN' + ')');
				}
			},
			complete: function(){}
		});
	}

	/*****************************************************************************
	*
	* This function shows a "processing" msg box when performing an action
	*
	******************************************************************************/
	function showProcessingMsgBox(){

		Swal.fire({
		  imageUrl				: DBR_ajax_obj.images_path + 'loading20px.svg',
		  imageWidth			: 60,
		  imageHeight			: 60,					  
		  showCloseButton		: false,
		  showConfirmButton		: false,
		  allowOutsideClick		: false,
		  text					: DBR_ajax_obj.processing
		})
	}

	/***********************************************************************************
	*
	* When clicking on "Reorder tables"/"Save order" links, prepare tables to be ordered
	*
	***********************************************************************************/
	jQuery('#DBR_reorder_tables, #DBR_save_order_tables').on('click', function(){

		var my_id = jQuery(this).attr("id");

		// If the user clicked on "Reorder tables" link
		if(my_id == "DBR_reorder_tables"){

			// Hide current link and show save link
			jQuery("#DBR_reorder_tables").hide();
			jQuery("#DBR_save_order_tables").show();

			// Hide all accordion icons
			jQuery(".DBR-accordion").hide();

			// Hide collapse all and expand all links
			jQuery(".DBR-collapse-expand-all-tables").hide();

			// Make cursor pointer for tables header
			jQuery(".DBR-table-header-title").css("cursor", "move");

			// When changing places of custom reset tables, hide all tables so that dragging will be easy for the user
			jQuery(".DBR-custom-reset-table").hide();

			// Make the UL elements sortable
			jQuery("#my_sortable").sortable({

				placeholder: "DBR-sortable-highlight",
				//scroll: false,
				appendTo: document.body,
				helper: "clone",

			});
		// If we are saving the new order
		}else if(my_id == "DBR_save_order_tables"){

			// Hide current link and show order link
			jQuery("#DBR_reorder_tables").show();
			jQuery("#DBR_save_order_tables").hide();

			// Show all accordion icons
			jQuery(".DBR-accordion").show();

			// Show collapse all and expand all links
			jQuery(".DBR-collapse-expand-all-tables").show();

			// Make cursor default for tables header
			jQuery(".DBR-table-header-title").css("cursor", "default");

			// After changing places of custom reset tables, show tables that should be shown; because we hided them in "start"
			jQuery(".DBR-custom-reset-table").show();
			whichTablesShouldBeHidden();

			// Save new order in localStorage
			// Loop over all tables items titles and save them in their order
			var li_order = "";
			jQuery('.DBR-order-li-element').each(function(i, obj){
				if(li_order == ""){
					li_order =  jQuery(obj).attr('value');
				}else{
					li_order =  li_order + ":" + jQuery(obj).attr('value');
				}
			});

			// Save order in localStorage
			localStorage.setItem("DBR_tables_order", li_order);

			// Delete sortable from the UL element
			jQuery("#my_sortable").sortable("destroy");

		}
	});

	/***********************************************************************************
	*
	* When clicking on "Expand all" / "Collapse all" links, show/hide all tables
	*
	***********************************************************************************/
	jQuery('#DBR_collapse_all_tables').on('click', function(){

		// Hide all tables
		jQuery('.DBR-custom-reset-table').hide();

		// Loop over all tables items titles and save them in their names
		var list_of_tables = "";
		jQuery('.DBR-order-li-element').each(function(i, obj){
			if(list_of_tables == ""){
				list_of_tables = jQuery(obj).attr('value');
			}else{
				list_of_tables = list_of_tables + ":" + jQuery(obj).attr('value');
			}
		});

		// Update tables that should be hidden to "all tables"
		localStorage.setItem("DBR_hidden_tables", list_of_tables);

	});

	jQuery('#DBR_expand_all_tables').on('click', function(){

		// Show all tables
		jQuery('.DBR-custom-reset-table').show();

		// Update tables that should be hidden to "empty"
		localStorage.setItem("DBR_hidden_tables", "");

	});


});

// Clear Local Data
function DBR_clear_local_data(clean_cookies, clean_local, clean_session){

	if(clean_cookies){
		var cookies = Cookies.get();
		for(cookie in cookies){
			Cookies.remove(cookie);
		}
	}

	if(clean_local){
		localStorage.clear();
	}

	if(clean_session){
		sessionStorage.clear();
	}
}

function DBR_count_cookies(){

	var total = 0;
	var cookies = Cookies.get();
	total = Object.keys(cookies).length;
	return total;
}

function DBR_count_local_storage(){

	var total = localStorage.length;
	return total;
}

function DBR_count_session_storage(){

	var total = sessionStorage.length;
	return total;
}
