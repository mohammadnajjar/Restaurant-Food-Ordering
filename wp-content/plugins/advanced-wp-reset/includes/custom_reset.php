
<div id="custom_reset_tab_content" class="DBR_tabcontent">

	<div class="DBR-box-warning" style="">
		<span class="dashicons dashicons-warning DBR-dashicons-warning" style="color:red"></span>
		<span style="color:red"><strong><?php _e('WARNING','advanced-wp-reset'); ?></strong></span>:
		<?php _e('The plugin does not backup your database and files before the rest. Please don\'t forget to make a backup in case you think you will need it.','advanced-wp-reset'); ?>
	</div>

	<div style="display:none">

		<input id="" class="button button-primary button-large" type="submit" value="Reset all selected items">
		<input id="" class="button button-primary button-large" type="submit" value="Create new profile">

		<div id="DBR_reorder_tables" class="DBR-reorder-tables">
			<span><?php _e('Reorder tables', 'advanced-wp-reset')?></span>
			<span class="dashicons dashicons-editor-ul"></span>
		</div>

		<div id="DBR_save_order_tables" class="DBR-save-order-tables">
			<span><?php _e('Save the new order', 'advanced-wp-reset')?></span>
			<span class="dashicons dashicons-saved"></span>
		</div>

		<div id="DBR_collapse_all_tables" class="DBR-collapse-expand-all-tables">
			<span class=""><?php _e('Collapse all', 'advanced-wp-reset')?></span>
			<span class="DBR-hor-divider">|</span>
		</div>

		<div id="DBR_expand_all_tables" class="DBR-collapse-expand-all-tables">
			<span class=""><?php _e('Expand all', 'advanced-wp-reset')?></span>
			<span class="DBR-hor-divider">|</span>
		</div>

	</div>

	<ul id="my_sortable">

		<?php

		// Prepare the list of items to reset with their explanations
		$all_items_array 	= DBR_prepare_custom_reset_items();
		$all_explanations 	= DBR_prepare_explanations_for_custom_reset_items();

		foreach($all_items_array as $item_type => $item_info){ ?>

			<li id="order_<?php echo $item_type?>" value="<?php echo $item_type?>" class="DBR-order-li-element">

				<div id="DBR_accordion_div_<?php echo $item_type?>" class="DBR-table-header-title">

					<?php echo '<span style="color:#EB7F63;font-size:1.2em;font-weight:700;padding-bottom:10px">' . $item_info['table_title'] . '</span>'?>

					<span id="DBR_accordion_link_<?php echo $item_type?>" class="dashicons dashicons-arrow-up DBR-accordion" style="display:none"></span>

				</div>

				<table id="DBR_table_custom_reset_<?php echo $item_type?>" class="wp-list-table widefat DBR-custom-reset-table">
					<tbody>

						<?php

						foreach($item_info['table_rows'] as $row_info){ ?>

						<tr class="DBR-custom-reset-tr">

							<!--<th style="width:4%;padding-top:19px">
								<input id="DBR_checkbox_<?php //echo $row_info['type'] ?>" type="checkbox" value="true">
							</th>-->

							<td style="width:20%;padding-top:15px">

								<span value="<?php echo $row_info['type'] ?>" class="DBR-item-to-reset-title">
									<?php echo $row_info['title'] ?>
								</span>
								<?php echo $row_info['deals_with'] ?>

								<?php
								// Add checkbox in case of themes-files and plugins-files to keep active theme and DBR plugin
								if($row_info['type'] == 'themes-files'){ ?>
									<div style="margin:3px 0px">
										<label class="DBR-switch">
										  <input id="DBR_keep_active_theme" type="checkbox" checked>
										  <span class="DBR-slider round"></span>
										</label>
										<?php _e('Keep active theme', 'advanced-wp-reset') ?>
									</div>
								<?php
								}
								//if($row_info['type'] == 'plugins-files'){ ?>
									<!--<div style="margin:3px 0px">
										<label class="DBR-switch">
										  <input id="DBR_keep_this_plugin" type="checkbox" checked>
										  <span class="DBR-slider round"></span>
										</label>
									<?php //_e('Keep current plugin', 'advanced-wp-reset') ?>
									</div>-->
								<?php
								//}
								?>								

								<div id="" class="DBR-item-to-reset-exclude">

									<?php _e('Total', 'advanced-wp-reset')?>

									<span id="DBR_total_<?php echo $row_info['type']?>" class="DBR-item-to-reset-total">-</span>

									<img id="DBR_spinning_checkbox_<?php echo $row_info['type']?>" class="DBR-spinning-checkbox" src="<?php echo DBR_PLUGIN_DIR_PATH; ?>/images/loading20px.svg"/>

								</div>

							</td>

							<td style="width:20%;padding-top:15px">

								<div style="text-align:center">
									<button name="<?php echo $row_info['type']?>" value="<?php echo $row_info['title']?>" class="button button-secondary DBR-only-in-desktop DBR-custom-reset-button" type="button">

										<?php _e('Run reset now', 'advanced-wp-reset')?>

									</button>

									<button name="<?php echo $row_info['type']?>" value="<?php echo $row_info['title']?>" class="button button-secondary DBR-only-in-mobile DBR-custom-reset-button" type="button">

										<?php _e('Reset', 'advanced-wp-reset')?>

									</button>
								</div>

							</td>
							<td class="DBR-only-in-desktop">
								<?php echo $all_explanations[$row_info['type']]?>
							</td>

						</tr>

						<?php
						}
						?>

					</tbody>

				</table>

			</li>

		<?php
		}
		?>
	</ul>

	<!--<div style="margin-top:15px">
		<input id="" class="button button-primary button-large" type="submit" value="Reset all selected items">
		<input id="" class="button button-primary button-large" type="submit" value="Create new profile">
	</div>-->

</div>