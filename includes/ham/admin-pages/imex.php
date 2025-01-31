<div class="wrap">
	<h2><?php _e( 'Hide Admin Menu - Import & Export', 'cnw-hide-admin-menu' ); ?></h2>

	<h3><?php _e( 'Export', 'cnw-hide-admin-menu' ); ?></h3>

	<form method="post" action="">
		<?php wp_nonce_field( 'ham-export' ); ?>
		<p><?php _e( 'Click the button below to download setting file. You can import it later on this site or on another site.', 'cnw-hide-admin-menu' ); ?></p>

		<?php submit_button( __( 'Export Settings', 'cnw-hide-admin-menu' ), 'primary', 'export' ); ?>
	</form>

	<h3><?php _e( 'Import', 'cnw-hide-admin-menu' ); ?></h3>

	<form method="post" action="" enctype="multipart/form-data">
		<?php wp_nonce_field( 'ham-import' ); ?>
		<p><?php _e( 'Choose exported setting file and click "Import Settings" to start importing.', 'cnw-hide-admin-menu' ); ?></p>

		<p><input type="file" name="setting"/></p>

		<?php submit_button( __( 'Import Settings', 'cnw-hide-admin-menu' ), 'primary', 'import' ); ?>
	</form>
</div>
