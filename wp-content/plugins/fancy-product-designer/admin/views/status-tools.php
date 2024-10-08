<?php
require_once(FPD_PLUGIN_ADMIN_DIR.'/views/modal-shortcodes.php');
?>
<div class="wrap" id="fpd-manage-status">

	<table class="fpd-status-table ui striped table">
		<thead>
			<tr>
				<th colspan="2">
					<?php esc_html_e( 'Server Environment', 'radykal'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="four wide">
					<em><?php esc_html_e('PHP Version', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The PHP version installed on your server.', 'radykal'); ?>">
						<i class="mdi mdi-information-outline icon"></i>
					</span>
				</td>
				<td class="twelve wide">
					<?php echo PHP_VERSION ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('Web Server Info', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The current server software that is used on your web hosting.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( $_SERVER['SERVER_SOFTWARE'] ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('Memory Limit', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The maximum amount of memory in bytes that a script is allowed to allocate.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( ini_get('memory_limit') ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('POST Max. Size', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The largest filesize that can be sent via one POST.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( ini_get('post_max_size') ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('Uploaded Max. Filesize', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The maximum size of an uploaded file.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( ini_get('upload_max_filesize') ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('Max. Execution Time', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('The maximum time in seconds a script is allowed to run.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( ini_get('max_execution_time') ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php esc_html_e('Max. Input Variables', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('How many input variables may be accepted.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td>
					<?php esc_html_e( ini_get('max_input_vars') ); ?>
				</td>
			</tr>
			<?php

				$classes_funcs = array(
					array(
						'type' => 'class',
						'name' => 'ZipArchive',
						'info' => __('Necessary for zipping/unzipping exported or imported products.', 'radykal')
					),
					array(
						'type' => 'function',
						'name' => 'getimagesize',
						'info' => __('Checks if file is an image.', 'radykal')
					),
					array(
						'type' => 'function',
						'name' => 'exif_read_data',
						'info' => __('Gets the orientation of an uploaded image. Required to rotate images uploaded from mobile devices correctly.', 'radykal')
					),
					array(
						'type' => 'function',
						'name' => 'curl_exec',
						'info' => __('Writes files on the server.', 'radykal')
					),
					array(
						'type' => 'function',
						'name' => 'file_put_contents',
						'info' => __(' Writes data to a file.', 'radykal')
					),
					array(
						'type' => 'INI',
						'name' => 'allow_url_fopen',
						'info' => __('Allows to read remote files.', 'radykal')
					),
					array(
						'type' => 'class',
						'name' => 'Imagick',
						'info' => __('Imagick is not enabled on your server.', 'radykal')
					)
				);

				foreach($classes_funcs as $cf) {

					$success_label = __( 'Installed', 'radykal' );
					$error_label = __( 'Not Installed', 'radykal' );

					if( $cf['type'] == 'INI' ) {
						$success_label = __( 'Activated', 'radykal' );
						$error_label = __( 'Disabled', 'radykal' );
					}

					if( $cf['type'] == 'class' && class_exists($cf['name']) )
						$status = '<span class="ui green tiny basic label"><span class="mdi mdi-check icon"></span> '. $success_label .' </span>';
					else if( $cf['type'] == 'function' && function_exists($cf['name']) )
						$status = '<span class="ui green tiny basic label"><span class="mdi mdi-check icon"></span> '. $success_label .' </span>';
					else if( $cf['type'] == 'INI' && (bool) ini_get($cf['name']) )
						$status = '<span class="ui green tiny basic label"><span class="mdi mdi-check icon"></span> '. $success_label .' </span>';
					else
						$status = '<span class="ui red tiny basic label"><span class="mdi mdi-close icon"></span> '. $error_label .' </span>';

					echo '<tr><td><em>'. $cf['type'].'</em>: '.$cf['name'].'<span data-variation="tiny" data-tooltip="'. esc_attr($cf['info']) .'"><i class="mdi mdi-information-outline icon"></i></span></td><td>'. $status .'</td></tr>';

				}

				do_action( 'fpd_status_server_table_end' );
			?>
		</tbody>
	</table>
	<p class="description"><?php _e( 'If any class or function is missing, please install these. Otherwise Fancy Product Designer may not work correctly. If you do not know how to install/activate the PHP classes/functions, please ask your server hoster!', 'radykal' ); ?></p>
	<br /><br />

	<table class="fpd-status-table ui striped table">
		<thead>
			<tr>
				<th colspan="3">
					<?php esc_html_e( 'Tools', 'radykal'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="four wide">
					<em><?php esc_html_e( 'Shortcodes', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('All shortcodes that comes with Fancy Product Designer.', 'radykal'); ?>"><i class="mdi mdi-information-outline icon"></i></span>
				</td>
				<td colspan="2">
					<span class="ui secondary tiny button" id="fpd-open-shortcode-builder">
						<?php _e('Open Shortcode Builder', 'radykal'); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<em><?php _e('Migrate Images', 'radykal'); ?></em>
					<span data-variation="tiny" data-tooltip="<?php esc_attr_e('Use this tool when you move your site to another domain or the protocol has been updated.', 'radykal'); ?>">
						<i class="mdi mdi-information-outline icon"></i>
					</span>
				</td>
				<td class="ui fluid input">
					<input type="text" id="fpd-old-domain" class="widefat" placeholder="<?php esc_attr_e('Enter the old domain incl. protocol (http or https), e.g. https://domain.com', 'radykal'); ?>" />
				</td>
				<td  class="two wide">
					<button class="ui secondary tiny button" id="fpd-reset-image-sources">
						<?php _e('Start Migration', 'radykal'); ?>
					</button>
				</td>
			</tr>
			<?php do_action( 'fpd_status_tools_table_end' ); ?>
		</tbody>
	</table>
	<div id="fpd-updated-infos" class="fpd-hidden">
		<h4><?php _e('Updated Entries in Database Tables'); ?></h4>
		<ul>
			<li><?php _e('Products'); ?>: <span id="fpd-updated-products"></span></li>
			<li><?php _e('Views'); ?>: <span id="fpd-updated-views"></span></li>
			<li><?php _e('Designs'); ?>: <span id="fpd-updated-designs"></span></li>
			<li><?php _e('Shortcode Orders'); ?>: <span id="fpd-updated-sc-orders"></span></li>
			<li><?php _e('WooCommerce Orders'); ?>: <span id="fpd-updated-wc-orders"></span></li>
		</ul>
	</div>
	<?php 

		$pj_limit = 5;
		$print_jobs_total = FPD_Print_Job::get_total();

		$pj_offset = 0;
		if( isset($_GET['print_job_offset'])	)
			$pj_offset = intval($_GET['print_job_offset']);

		$pj_offset = intval( $pj_offset );

		$print_jobs = FPD_Print_Job::get_print_jobs(
			array(
				'order_by' => 'ID',
				'limit' => $pj_limit,
				'offset' => $pj_offset * $pj_limit
			)
		);

	?>
	<h3><?php esc_html_e( 'Print Jobs', 'radykal'); ?> (Total: <?php echo $print_jobs_total; ?>)</h3>
	<p><?php esc_html_e( 'A list of all print jobs using the PRO export.', 'radykal'); ?></p>
	<table class="fpd-print-jobs-table ui striped table">
		<thead>
			<tr>
				<th class="one wide">
					<?php esc_html_e( 'ID', 'radykal'); ?>
				</th>
				<th class="two wide">
					<?php esc_html_e( 'GUID', 'radykal'); ?>
				</th>
				<th class="four wide">
					<?php esc_html_e( 'Details', 'radykal'); ?>
				</th>
				<th class="four wide">
					<?php esc_html_e( 'Data', 'radykal'); ?>
				</th>
				<th class="two wide">
					<?php esc_html_e( 'Status', 'radykal'); ?>
				</th>
				<th class="right aligned">
					<?php esc_html_e( 'Created', 'radykal'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($print_jobs as $print_job): ?>
				<tr>
					 <td><?php echo $print_job->ID; ?></td>
					 <td><?php echo $print_job->guid; ?></td>
					 <td><?php echo $print_job->details; ?></td>
					 <td class="four wide" style="word-break: break-all;"><?php echo $print_job->data; ?></td>
					 <td><?php echo $print_job->status; ?></td>
					 <td class="right aligned"><?php echo $print_job->created_at; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
    		<tr>
				<th colspan="6">
					<div class="ui right floated pagination mini menu">
						<a 
							class="icon item <?php echo $pj_offset == 0 ? 'disabled': ''; ?>" 
							href="?page=fpd_status&print_job_offset=<?php echo $pj_offset-1; ?>"
						>
								<i class="left chevron icon"></i>
						</a>
						<a 
							class="icon item <?php echo ($pj_offset * $pj_limit + $pj_limit) > $print_jobs_total ? 'disabled': ''; ?>" 
							href="?page=fpd_status&print_job_offset=<?php echo $pj_offset+1; ?>"
						>
								<i class="right chevron icon"></i>
						</a>
					</div>
				</th>
			</tr>
		</tfoot>
	</table>

</div>