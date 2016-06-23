<?php
// require('pdf/fpdf.php');
if(!class_exists('Partner')){ include_once 'classes.php'; }

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Users_List_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'User', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Users', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_customers( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}accountably_user";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $user_id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}accountably_user",
			[ 'user_id' => $user_id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}accountably_user";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No members avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'last_name':
            case 'email':
            case 'phone':
            case 'age':
            case 'job_title':
            case 'industry':
            case 'location':
            case 'goal':
            case 'create_time':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	* Custom column methods.
	**/
    function column_last_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=persons_form&user_id=%s">%s</a>', $item['user_id'], __('Edit', 'custom_table_example')),
            'delete'    => sprintf('<a href="?page=%s&action=%s&user=%s">Delete</a>',$_REQUEST['page'],'delete',$item['user_id']),
        );
        
        //Return the title contents
        return sprintf('<strong><a href="?page=persons_form&user_id=%3$s">%1$s, %2$s</a></strong> %4$s',
            /*$1%s*/ $item['last_name'],
            /*$2%s*/ $item['first_name'],
            /*$3%s*/ $item['user_id'],
            /*$4%s*/ $this->row_actions($actions)
        );
    }

    function column_email($item){
        
        //Return the title contents
        return sprintf('<a href="mailto:%1$s">%1$s</a>',
            /*$1%s*/ $item['email']
        );
    }

    function column_create_time($item){
        
        //Return the title contents
        return sprintf('%1$s',
            /*$1%s*/ date("m/d/Y - g:ia", strtotime($item['create_time']))
        );
    }

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['user_id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&user=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['user_id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'last_name'    => __( 'Name', 'sp' ),
			'email' => __( 'Email', 'sp' ),
			'phone' => __( 'Phone', 'sp' ),
			'age' => __( 'Age', 'sp' ),
			'job_title' => __( 'Title', 'sp' ),
			'industry' => __( 'Industry', 'sp' ),
			'location' => __( 'Location', 'sp' ),
			'goal' => __( 'Goal', 'sp' ),
			'create_time'    => __( 'Created On', 'sp' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'last_name'     => array('last_name',false),
            'age'     => array('age',false),
            'job_title'     => array('title',false),
            'industry'     => array('industry',false),
            'location'     => array('location',false),
            'create_time'     => array('create_time',false)
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete',
			'export-selected' => 'Export',
            'export-all' => 'Export All'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'customers_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_customers( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_customer( absint( $_GET['user_id'] ) );

				echo "<script type=\"text/javascript\">
		        window.location = \"/wp-admin/admin.php?page=accountably/\"
		        </script>";
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );

			}

			echo "<script type=\"text/javascript\">
		        window.location = \"/wp-admin/admin.php?page=accountably\"
		        </script>";
			exit;
		}

		if ('export-all' === $this->current_action()) {

			// $wpdb->show_errors();
			global $wpdb;
			ob_end_clean();							
			// Grab any post values you sent with your submit function
			// $DownloadReportFrom = $_POST['ReportDateFrom'];
			// $DownloadReportTo = $_POST['ReportDateTo'];


		$accountably_table_name = $wpdb->prefix . 'accountably_user';
			// Build your query						
			$MyQuery = $wpdb->get_results( "SELECT * FROM $accountably_table_name");
									

			// Process report request
			if (! $MyQuery) {
			$Error = $wpdb->print_error();
			die("The following error was found: $Error");
			} else {
			// Prepare our csv download

			// Set header row values
			$csv_fields=array();
			$csv_fields[] = 'Field Name 1';
			$csv_fields[] = 'Field Name 2';
			$output_filename = 'MyReport_' . $DownloadReportFrom .'-'. $DownloadReportTo  . '.csv';
			$output_handle = @fopen( 'php://output', 'w' );
			 
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename=' . $output_filename );
			header( 'Expires: 0' );
			header( 'Pragma: public' );	

			// Insert header row
			fputcsv( $output_handle, $csv_fields );

			// Parse results to csv format
			foreach ($MyQuery as $Result) {
				$leadArray = (array) $Result; // Cast the Object to an array
				// Add row to file
				fputcsv( $output_handle, $leadArray );
				}
			 
			// Close output file stream
			fclose( $output_handle ); 

			die();
			}

        }
	}

	// public function process_bulk_action() {
 //        $user_id = ( is_array( $_REQUEST['user'] ) ) ? $_REQUEST['user'] : array( $_REQUEST['user'] );
 //        //Detect when a bulk action is being triggered...
 //        if( 'bulk-delete'===$this->current_action() ) {
 //            global $wpdb;
		
 //        foreach ( $user_id as $id ) {
 //            $id = absint( $id );
 //            echo "<script type=\"text/javascript\">
 //        alert('".$id."');
 //        </script>";
 //            $wpdb->query( "DELETE FROM ".$wpdb->prefix."accountably_user WHERE user_id = $id" );
 //        }
 //        }
        
 //        if( 'export-selected'===$this->current_action() ) {
        
 //        $_SESSION['user_id'] = $user_id;
 //        echo "<script type=\"text/javascript\">
 //        alert('Pop-up Blocking must be turned off to export PDF\'s');
 //        window.open('".WP_PLUGIN_URL."/accountably/build-pdf.php');
 //        window.location = \"/wp-admin/admin.php?page=users/\"
 //        </script>";
 //        }
        
 //        if( 'export-all'===$this->current_action() ) {
        
 //        echo "<script type=\"text/javascript\">
 //        alert('Pop-up Blocking must be turned off to export PDF\'s');
 //        window.open('".WP_PLUGIN_URL."/accountably/build-pdf-all.php');
 //        window.location = \"/wp-admin/admin.php?page=users/\"
 //        </script>";
 //        }
        
 //    }

}


class SP_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function plugin_menu() {
		$icon = plugins_url( 'images/accountably_icon.png' , __FILE__ );

		$hook = add_menu_page(
			'Accountably Member Management',
			'Accountably',
			'manage_options',
			'accountably',
			[ $this, 'plugin_settings_page' ],
			$icon
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

		add_submenu_page( 'accountably', 'Members', 'Members', 'manage_options', 'accountably' );
		add_submenu_page('accountably', 'Add new', 'Add new', 'activate_plugins', 'persons_form', 'custom_table_example_persons_form_page_handler');
	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Accountably Members <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons_form');?>">Add new</a></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$customers_obj = new Users_List_Table();
								$customers_obj->search_box('search', 'search_id');
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Members',
			'default' => 10,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new Users_List_Table();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
	SP_Plugin::get_instance();
} );

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function custom_table_example_persons_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'accountably_user'; // do not forget about tables prefix

    $partnership_view = $wpdb->prefix . 'partnerships_v'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    date_default_timezone_set('America/New_York');
	$current_time = date('Y-m-d G:i:s');

    $default = array(
        'user_id' => '',
        'create_time' => $current_time,
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'age' => null,
        'location' => '',
        'industry' => '',
        'job_title' => '',
        'goal' => '',
        'active' => '',
        'notes' => '',
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = custom_table_example_validate_person($item);
        if ($item_valid === true) {
            if ($item['user_id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['user_id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Member was successfully saved', 'custom_table_example');
                } else {
                    $notice = __('There was an error while saving member', 'custom_table_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('user_id' => $item['user_id']));
                if ($result) {
                    $message = __('Member was successfully updated', 'custom_table_example');
                } else {
                    $notice = __('There was an error while updating member', 'custom_table_example');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['user_id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $_REQUEST['user_id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Member not found', 'custom_table_example');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Member Details', 'custom_table_example_persons_form_meta_box_handler', 'person', 'core', 'default');
    add_meta_box('member_date_meta_box', 'Satus', 'member_date_meta_box_handler', 'person', 'side', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2>Member <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=accountably');?>"><?php _e('back to list', 'custom_table_example')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				
		    <form id="form" method="POST">
		        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
		        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
		        <input type="hidden" name="user_id" value="<?php echo $item['user_id'] ?>"/>
		        <input type="hidden" name="active" value="1" id="active" />
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('person', 'core', $item); ?>
                    
                    <input type="submit" value="<?php _e('Save', 'custom_table_example')?>" id="submit" class="button-primary" name="submit">
			    </form>
            </div>
            <div id="postbox-container-1" class="postbox-container">
			    <div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_meta_boxes('person', 'side', $item); ?>
			    </div>
		    </div>
        </div>
        <br class="clear">
    </div>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function custom_table_example_persons_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="first_name">First Name</label>
        </th>
        <td>
            <input id="first_name" name="first_name" type="text" style="width: 95%" value="<?= esc_attr($item['first_name'])?>" size="50" placeholder="Jack" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="last_name">Last Name</label>
        </th>
        <td>
            <input id="last_name" name="last_name" type="text" style="width: 95%" value="<?= esc_attr($item['last_name'])?>" size="50" placeholder="Burton" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="email">Email Address</label>
        </th>
        <td>
            <input id="email" name="email" type="email" style="width: 95%" value="<?= esc_attr($item['email'])?>" size="50" placeholder="me@jackburton.com" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="phone">Phone</label>
        </th>
        <td>
            <input id="phone" name="phone" type="text" style="width: 95%" value="<?= esc_attr($item['phone'])?>" size="50" placeholder="212-555-5555">
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="age">Age</label>
        </th>
        <td>
            <input id="age" name="age" type="number" style="width: 95%" value="<?= esc_attr($item['age'])?>" size="50" placeholder="33" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="location">Location</label>
        </th>
        <td>
            <input id="location" name="location" type="text" style="width: 95%" value="<?= esc_attr($item['location'])?>" size="50" placeholder="Little China" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="industry">Industry</label>
        </th>
        <td>
            <input id="industry" name="industry" type="text" style="width: 95%" value="<?= esc_attr($item['industry'])?>" size="50" placeholder="Transportation" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="job_title">Title</label>
        </th>
        <td>
            <input id="job_title" name="job_title" type="text" style="width: 95%" value="<?= esc_attr($item['job_title'])?>" size="50" placeholder="President" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="goal">Goal</label>
        </th>
        <td>
            <textarea id="goal" name="goal" type="text" style="width: 95%" size="50" placeholder="Getting my truck back." required><?= esc_attr($item['goal'])?></textarea>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="goal">Active</label>
        </th>
        <td>
            <input id="active" name="active" type="checkbox" <?php if (esc_attr($item['active']) == 1){echo "checked";} ?> value="1" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="notes">Notes</label>
        </th>
        <td>
        	<?php
        	$content = $item['notes'];
			$editor_id = 'notes';
			$settings = array( 'media_buttons' => false );

			wp_editor( $content, $editor_id, $settings );

			?>
            <!-- <textarea id="notes" name="notes" type="text" style="width: 95%" size="50" height="100" placeholder="Consider this your captain's log."><?= esc_attr($item['notes'])?></textarea> -->
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Sidebar data
 *
 * @param $item
 */
function member_date_meta_box_handler($item)
{
    ?>
    <p><strong>Created on:</strong> <?= date("m-d-Y, h:i A", strtotime($item['timestamp'])) ?></p>
    <p><strong>Updated on:</strong> <?= date("m-d-Y, h:i A", strtotime($item['last_timestamp'])) ?></p>
    <p><strong>Teammate:</strong>
		<?php
			$MyPartners = new Partners();
			$MyPartners = $MyPartners->GetById($item['user_id']);
			
			foreach($MyPartners as $MyPartner) {
				$MyCopartners = new Partners();
				$MyCopartners = $MyCopartners->GetCopartner($MyPartner->UserId, $MyPartner->PartnershipId);
				foreach($MyCopartners as $MyCopartner) {
					echo $MyCopartner->FirstName." ".$MyCopartner->LastName;
				}
			}
	 	?>
	</p>
<?php
}
/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function custom_table_example_validate_person($item)
{
    $messages = array();

    if (empty($item['first_name'])) $messages[] = __('First Name is required', 'custom_table_example');
    if (empty($item['last_name'])) $messages[] = __('Last Name is required', 'custom_table_example');
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('Email is in wrong format', 'custom_table_example');
    if (empty($item['last_name'])) $messages[] = __('Last Name is required', 'custom_table_example');
    if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'custom_table_example');
    if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == custom_table_example
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function custom_table_example_languages()
{
    load_plugin_textdomain('custom_table_example', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'custom_table_example_languages');
