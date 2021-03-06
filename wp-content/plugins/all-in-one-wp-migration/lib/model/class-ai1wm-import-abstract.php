<?php
/**
 * Copyright (C) 2014 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

abstract class Ai1wm_Import_Abstract {

	protected $args    = array();

	protected $storage = null;

	public function __construct( array $args = array() ) {
		$this->args = $args;

		// HTTP resolve
		if ( $this->args['method'] === 'import' ) {
			Ai1wm_Http::resolve( admin_url( 'admin-ajax.php?action=ai1wm_resolve' ) );
		}
	}

	/**
	 * Unpack archive
	 *
	 * @return void
	 */
	public function start() {
		// Set default progress
		Ai1wm_Status::set( array(
			'type'    => 'info',
			'message' => __( 'Unpacking archive...', AI1WM_PLUGIN_NAME ),
		) );

		// Open the archive file for reading
		$archive = new Ai1wm_Extractor( $this->storage()->archive() );

		// Unpack package.json and database.sql files
		$archive->extract_by_files_array(
			$this->storage()->path(),
			array(
				AI1WM_PACKAGE_NAME,
				AI1WM_DATABASE_NAME,
			)
		);

		// Close the archive file
		$archive->close();

		// Validate the archive file
		if ( $this->validate() ) {

			// Parse the package file
			$service = new Ai1wm_Service_Package( $this->args );
			if ( $service->import() ) {
				$this->route_to( 'confirm' );
			} else {
				throw new Ai1wm_Import_Exception( __( 'Invalid package.json file.', AI1WM_PLUGIN_NAME ) );
			}

		} else {
			throw new Ai1wm_Import_Exception(
				__( 'Invalid archive file. It should contain <strong>package.json</strong> file.', AI1WM_PLUGIN_NAME )
			);
		}
	}

	/**
	 * Confirm import
	 *
	 * @return void
	 */
	public function confirm() {
		// Obtain the size of the archive
		$size = @filesize( $this->storage()->archive() );

		if ( false === $size ) {
			throw new Ai1wm_Not_Accesible_Exception(
				sprintf(
					__(
						'Unable to get the file size of <strong>"%s"</strong>',
						AI1WM_PLUGIN_NAME
					),
					$this->storage()->archive()
				)
			);
		}

		$allowed_size = apply_filters( 'ai1wm_max_file_size', AI1WM_MAX_FILE_SIZE );

		// Let's check the size of the file to make sure it is less than the maximum allowed
		if ( ( $allowed_size > 0 ) && ( $size > $allowed_size ) ) {
			throw new Ai1wm_Import_Exception(
				sprintf(
					__(
						'The file that you are trying to import is over the maximum upload file size limit of %s.' .
						'<br />You can remove this restriction by purchasing our ' .
						'<a href="https://servmask.com/products/unlimited-extension" target="_blank">Unlimited Extension</a>.',
						AI1WM_PLUGIN_NAME
					),
					apply_filters( 'ai1wm_max_file_size', AI1WM_MAX_FILE_SIZE )
				)
			);
		}

		// Set progress
		Ai1wm_Status::set(
			array(
				'type'    => 'confirm',
				'message' => __(
					'The import process will overwrite your database, media, plugins, and themes. ' .
					'Please ensure that you have a backup of your data before proceeding to the next step.',
					AI1WM_PLUGIN_NAME
				),
			)
		);
	}

	/**
	 * Enumerate content files and directories
	 *
	 * @return void
	 */
	public function enumerate() {
		// Set progress
		Ai1wm_Status::set( array(
			'type'    => 'info',
			'message' => __( 'Retrieving a list of all WordPress files...', AI1WM_PLUGIN_NAME )
		) );

		// Open the archive file for reading
		$archive = new Ai1wm_Extractor( $this->storage()->archive() );

		// Get number of files
		$total = $archive->get_number_of_files();

		// Close the archive file
		$archive->close();

		// Set total
		$this->args['total'] = $total;

		// Set progress
		Ai1wm_Status::set( array(
			'type'    => 'info',
			'message' => __( 'Done retrieving a list of all WordPress files.', AI1WM_PLUGIN_NAME ),
		) );

		// Redirect
		$this->route_to( 'truncate' );
	}

	/**
	 * Truncate content files and directories
	 *
	 * @return void
	 */
	public function truncate() {
		// Enable maintenance mode
		Ai1wm_Maintenance::enable();

		// Redirect
		$this->route_to( 'content' );
	}

	/**
	 * Add content files and directories
	 *
	 * @return void
	 */
	public function content() {
		// Set content offset
		if ( isset( $this->args['content_offset'] ) ) {
			$content_offset = $this->args['content_offset'];
		} else {
			$content_offset = 0;
		}

		// Set archive offset
		if ( isset( $this->args['archive_offset']) ) {
			$archive_offset = $this->args['archive_offset'];
		} else {
			$archive_offset = 0;
		}

		// Set total files
		if ( isset( $this->args['total'] ) ) {
			$total = $this->args['total'];
		} else {
			$total = 1;
		}

		// Set processed files
		if ( isset( $this->args['processed'] ) ) {
			$processed = $this->args['processed'];
		} else {
			$processed = 0;
		}

		// What percent of files have we processed?
		$progress = (int) ( ( $processed / $total ) * 100 );

		// Set progress
		if ( empty( $content_offset ) ) {
			Ai1wm_Status::set( array(
				'type'    => 'info',
				'message' => sprintf( __( 'Restoring %d files...<br />%.2f%% complete', AI1WM_PLUGIN_NAME ), $total, $progress ),
			) );
		}

		// Start time
		$start = microtime( true );

		// Flag to hold if all files have been processed
		$completed = true;

		// Open the archive file for reading
		$archive = new Ai1wm_Extractor( $this->storage()->archive() );

		// Set the file pointer to the one that we have saved
		$archive->set_file_pointer( null, $archive_offset );

		while ( $archive->has_not_reached_eof() ) {
			try {

				// Extract a file from archive to WP_CONTENT_DIR
				if ( ( $content_offset = $archive->extract_one_file_to( WP_CONTENT_DIR, array( AI1WM_PACKAGE_NAME, AI1WM_DATABASE_NAME ), $content_offset, 3 ) ) ) {

					// Set progress
					if ( ( $sub_progress = ( $content_offset / $archive->get_current_filesize() ) ) < 1 ) {
						$progress += $sub_progress;
					}

					// Set progress
					Ai1wm_Status::set( array(
						'type'    => 'info',
						'message' => sprintf( __( 'Restoring %d files...<br />%.2f%% complete', AI1WM_PLUGIN_NAME ), $total, $progress ),
					) );

					// Set content offset
					$this->args['content_offset'] = $content_offset;

					// Set archive offset
					$this->args['archive_offset'] = $archive_offset;

					// Close the archive file
					$archive->close();

					// Redirect
					return $this->route_to( 'content' );

				}

				// Set content offset
				$content_offset = 0;

				// Set archive offset
				$archive_offset = $archive->get_file_pointer();

			} catch ( Exception $e ) {
				// Skip bad file permissions
			}

			// Increment processed files counter
			$processed++;

			// Time elapsed
			if ( ( microtime( true ) - $start ) > 3 ) {
				// More than 3 seconds have passed, break and do another request
				$completed = false;
				break;
			}
		}

		// Set content offset
		$this->args['content_offset'] = $content_offset;

		// Set archive offset
		$this->args['archive_offset'] = $archive_offset;

		// Set processed files
		$this->args['processed'] = $processed;

		// Close the archive file
		$archive->close();

		// Redirect
		if ( $completed ) {
			$this->route_to( 'database' );
		} else {
			$this->route_to( 'content' );
		}
	}

	/**
	 * Add database
	 *
	 * @return void
	 */
	public function database() {
		// Set exclude database
		if ( ! is_file( $this->storage()->database() ) ) {
			return $this->route_to( 'finish' );
		}

		// Display progress
		Ai1wm_Status::set( array(
			'type'    => 'info',
			'message' => __( 'Restoring database...', AI1WM_PLUGIN_NAME ),
		) );

		// Get database file
		$service  = new Ai1wm_Service_Database( $this->args );
		$service->import();

		// Redirect
		$this->route_to( 'finish' );
	}

	/**
	 * Finish import process
	 *
	 * @return void
	 */
	public function finish() {
		// Set progress
		Ai1wm_Status::set( array(
			'type'    => 'finish',
			'title'   => __( 'Your data has been imported successfuly!', AI1WM_PLUGIN_NAME ),
			'message' => sprintf(
				__(
					'You need to perform two more steps:<br />' .
					'<strong>1. You must save your permalinks structure twice. <a class="ai1wm-no-underline" href="%s" target="_blank">Permalinks Settings</a></strong> <small>(opens a new window)</small><br />' .
					'<strong>2. <a class="ai1wm-no-underline" href="https://wordpress.org/support/view/plugin-reviews/all-in-one-wp-migration?rate=5#postform" target="_blank">Review the plugin</a>.</strong> <small>(opens a new window)</small>',
					AI1WM_PLUGIN_NAME
				),
				admin_url( 'options-permalink.php#submit' )
			)
		) );

		// Disable maintenance mode
		Ai1wm_Maintenance::disable();
	}

	/**
	 * Stop import and clean storage
	 *
	 * @return void
	 */
	public function stop() {
		$this->storage->clean();
	}

	/**
	 * Clean storage path
	 *
	 * @return void
	 */
	public function clean() {
		$this->storage()->clean();
	}

	/**
	 * Get import archive
	 *
	 * @return void
	 */
	abstract public function import();

	/**
	 * Validate archive and WP_CONTENT_DIR permissions
	 *
	 * @return boolean
	 */
	protected function validate() {
		if ( is_file( $this->storage()->package() ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Get storage object
	 *
	 * @return Ai1wm_Storage
	 */
	protected function storage() {
		if ( $this->storage === null ) {
			if ( isset( $this->args['archive'] ) ) {
				$this->args['archive'] = basename( $this->args['archive'] );
			}

			$this->storage = new Ai1wm_Storage( $this->args );
		}

		return $this->storage;
	}

	/**
	 * Route to method
	 *
	 * @param  string $method Name of the method
	 * @return void
	 */
	protected function route_to( $method ) {
		// Redirect arguments
		$this->args['method']     = $method;
		$this->args['secret_key'] = get_site_option( AI1WM_SECRET_KEY, false, false );

		// Check the status of the export, maybe we need to stop it
		if ( ! is_file( $this->storage()->archive() ) ) {
			exit;
		}

		// HTTP request
		Ai1wm_Http::get( admin_url( 'admin-ajax.php?action=ai1wm_import' ), $this->args );
	}
}
