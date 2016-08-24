<?php
// require('pdf/fpdf.php');
// include('classes.php');

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Partnerships_List_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Partnership', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Partnerships', 'sp' ), //plural name of the listed records
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

		$sql = "SELECT * FROM {$wpdb->prefix}partnerships_v";

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
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}accountably_partners",
			[ 'partnership_id' => $id ],
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

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}partnerships_v WHERE active = 1";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No partnerships avaliable.', 'sp' );
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
			case 'partners':
            case 'create_time':
            case 'update_time':
            case 'health':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	* Custom column methods.
	**/
    function column_partners($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=partnerships_form&partnership_id=%s">%s</a>', $item['partnership_id'], __('Edit', 'custom_table_example')),
            'delete'    => sprintf('<a href="?page=%s&action=%s&partnerships=%s">Delete</a>',$_REQUEST['page'],'delete',$item['partnership_id']),
        );
        
        //Return the title contents
        return sprintf('<strong><a target="_blank" href="'.WP_PLUGIN_URL.'/accountably/edit-partnership.php?action=edit&partnership=%2$s">%1$s</a></strong> %3$s',
            /*$1%s*/ $item['partners'],
            /*$2%s*/ $item['partnership_id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    // function column_email($item){
        
    //     //Return the title contents
    //     return sprintf('<a href="mailto:%1$s">%1$s</a>',
    //         /*$1%s*/ $item['email']
    //     );
    // }

    function column_create_time($item){
        
        //Return the title contents
        return sprintf('%1$s',
            /*$1%s*/ date("m/d/Y - g:ia", strtotime($item['create_time']))
        );
    }

    function column_update_time($item){
        if ($item['update_time'] != NULL) {
	        //Return the title contents
	        return sprintf('%1$s',
	            /*$1%s*/ date("m/d/Y - g:ia", strtotime($item['update_time']))
	        );
        } else {
        	return "Not updated.";
        }
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
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['partnership_id']
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
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
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
			'partners'    => __( 'Partners', 'sp' ),
			'create_time' => __( 'Created On', 'sp' ),
			'update_time' => __( 'Updated On', 'sp' ),
			'health' => __( 'Health', 'sp' )
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
			'partners'     => array('partners',false),
            'create_time'     => array('create_time',false),
            'update_time'     => array('update_time',false),
            'health'     => array('health',false)
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
        $partner_id = ( is_array( $_REQUEST['partner'] ) ) ? $_REQUEST['partner'] : array( $_REQUEST['partner'] );
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            global $wpdb;
		
        foreach ( $partner_id as $id ) {
            $id = absint( $id );
            $wpdb->query( "DELETE FROM ".$wpdb->prefix."accountably_partners WHERE id = $id" );
        }
        }
        
        if( 'export-selected'===$this->current_action() ) {
        
        $_SESSION['partner_id'] = $partner_id;
        echo "<script type=\"text/javascript\">
        alert('Pop-up Blocking must be turned off to export PDF\'s');
        window.open('".WP_PLUGIN_URL."/accountably/build-pdf.php');
        window.location = \"/wp-admin/admin.php?page=partners/\"
        </script>";
        }
        
        if( 'export-all'===$this->current_action() ) {
        
        echo "<script type=\"text/javascript\">
        alert('Pop-up Blocking must be turned off to export PDF\'s');
        window.open('".WP_PLUGIN_URL."/accountably/build-pdf-all.php');
        window.location = \"/wp-admin/admin.php?page=partners/\"
        </script>";
        }
        
    }

}


class SP_Partnerships_Plugin {

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

		$hook = add_submenu_page(
			'accountably',
			'Accountably Partnerships',
			'Partnerships',
			'manage_options',
			'partnerships',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

		// add_submenu_page( 'accountably', 'Partners', 'Partners', 'manage_options', 'partnerships' );
		add_submenu_page('accountably', 'Add new', 'Add new', 'activate_plugins', 'partnerships_form', 'partnership_form_page_handler');
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Accountably Partners <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partnerships_form');?>">Add new</a></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$customers_obj = new Partnerships_List_Table();
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
			'label'   => 'Partnerships',
			'default' => 10,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new Partnerships_List_Table();
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
	SP_Partnerships_Plugin::get_instance();
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
function partnership_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'accountably_partnership'; // do not forget about tables prefix
    $relational_table = $wpdb->prefix . 'accountably_partnerships'; // do not forget about tables prefix
    $user_table = $wpdb->prefix . 'accountably_user'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    date_default_timezone_set('America/New_York');
	$current_time = date('Y-m-d G:i:s');

    $default = array(
        'partnership_id' => 0,
        'create_time' => $current_time,
        'active' => '1',
        'health' => '',
        'notes' => '',
    );

    $relation = array(
        'partnership_id' => 0,
        'user_id' => $_POST['user_id'],
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        $partnership = shortcode_atts($relation, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = validate_partnership($item);
        if ($item_valid === true) {
            if ($item['partnership_id'] == 0) {
                $result = $wpdb->insert($table_name, $item);

                $relation['partnership_id'] = $wpdb->insert_id;

				    foreach ($relation['user_id'] as $partner) {
		                $result = $wpdb->insert($relational_table, array('partnership_id'=>$relation['partnership_id'], 'user_id'=>$partner));
		                $result = $wpdb->update($user_table, array('available'=>0), array('user_id'=>$partner));
				    }
                if ($result) {
                    $message = __('Partnership was successfully saved', 'custom_table_example');
                    function wpdocs_set_html_mail_content_type() {
					    return 'text/html';
					}
					add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
                    foreach ($relation['user_id'] as $partner) {
                    	$MyUsers = new Users();
						$MyUsers = $MyUsers->GetById($partner);
						foreach($MyUsers as $MyUser) {
		                    /**
							 * Filter the mail content type.
							 */
							
							$headers[] = 'From: Me Myself <alex@orangelotusdesign.com>';
							$headers[] = array('Content-Type: text/html; charset=UTF-8');
							 
							$to      = $MyUser->Email;
							$subject = 'You have a new Accountably partner!';
							$body    = '<h1>"The benefits of an accountability partner have been studied and proven. The hardest part is getting started and forming the habit."</h1>

										<p>Dre here. It’s the big day! We’ve matched you as Accountably partners and it’s time to get cranking on those goals.</p> 

										<p>This is important for what comes next: to let me know that you’ve received this email, click here.</p>

										<p>Now, don’t stress. You two are not in this alone. Here’s the best way to set up your partnership for success:</p>

										<p>1. Connect with your partner. Make contact with one another in the next 72 hours. Ideally, this would be a phone call, but if that’s not possible, at least let the other person know you’re alive and get a kick off call on the schedule.</p>

										<p>2. Read the attached PDF. This will give you instructions on how to best launch this partnership for ultimate success.</p>

										<p>3. Watch the weekly live webinar on Tuesday at 7PM EST. We’ll walk you through how to get on the right road to an ideal partnership, and you’ll have a chance to ask questions. Here’s the link to RSVP:</p>

										<p>4. Join the Accountably Slack page. I’ll send you each an email inviting you in just a moment. This is a good way for us to be able to help your partnership if you get stuck, so sign up as quickly as you can.</p>

										<p>Nail this stuff and joining Accountably will be the best thing you do all year.</p>

										<p>Rock on,<br>
										- Dre</p>';
							 
							wp_mail( $to, $subject, $body );
						}
					}
				// Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
				remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
                } else {
                    $notice = __('There was an error while saving the partnership', 'custom_table_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('partnership_id' => $item['partnership_id']));
                if ($result) {
                    $message = __('Partnership was successfully updated', 'custom_table_example');
                } else {
                    $notice = __('There was an error while updating the partnership', 'custom_table_example');
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
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'custom_table_example');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Create Partnership', 'partnership_form_meta_box_handler', 'partnership', 'core', 'default');
    add_meta_box('member_date_meta_box', 'Available Members', 'partnership_date_meta_box_handler', 'partnership', 'side', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2>New Partnership <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=partnerships');?>"><?php _e('back to list', 'custom_table_example')?></a>
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
		        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
		        <input type="hidden" name="user_id[]" value="" id="user_id1" />
		        <input type="hidden" name="user_id[]" value="" id="user_id2" />
		        <input type="hidden" name="active" value="1" id="active" />
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('partnership', 'core', $item); ?>
                    
                    <input type="submit" value="<?php _e('Create Partnership', 'custom_table_example')?>" id="submit" class="button-primary" name="submit">
			    </form>
            </div>
            <div id="postbox-container-1" class="postbox-container">
			    <div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_meta_boxes('partnership', 'side', $item); ?>
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
function partnership_form_meta_box_handler($item)
{
    ?>
<style>
.member-item {
	width: auto;
	padding: 10px;
	border: solid 1px #ccc;
	background-color: #efefef;
	margin-bottom: 5px;
}
.partner-drop {
	float: left;
	width: 30%;
	height: auto;
	margin: 20px;
	padding: 40px;
	border: dashed 1px #ccc;
}
.partner-drop h3 {
	color: #ccc;
	font-size: 22px;
	font-weight: 100;
	text-align: center;
}

.partner-drop-active, .partner-drop-active h3 {
	border-color: #a8bf12;
	color: #a8bf12;
}

.partner-drop-hover, .partner-drop-hover h3 {
	border-color: #ff8400;
	color: #ff8400;
}
</style>
 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
 
<script type="text/javascript">
 
$( init );
 
function init() {
  $('.member-item').draggable({
    cursor: 'move',
    containment: 'document',
    revert : function(event, ui) {
        // on older version of jQuery use "draggable"
        // $(this).data("draggable")
        $(this).data("uiDraggable").originalPosition = {
            top : 0,
            left : 0
        };
        return !event;
        // return (event !== false) ? false : true;
    }

  });
  $('.partner-drop').droppable( {
  	accept: '.member-item',
  	hoverClass: 'partner-drop-hover',
  	activeClass: 'partner-drop-active',
    drop: handleDropEvent,
    out: handleDropOut
  } );
  $('.partner-drop').droppable( {
  	accept: '.member-item',
  	hoverClass: 'partner-drop-hover',
  	activeClass: 'partner-drop-active',
    drop: handleDropEvent,
    out: handleDropOut
  } );
}
 
function handleDropEvent( event, ui ) {
  var draggable = ui.draggable;
  var droppable = $(this).data('input');
  // ui.draggable.draggable( 'option', 'revert', false );
  $(this).droppable('option', 'accept', ui.draggable);
  // alert( 'The square with ID "' + ui.draggable.data('userid') + '" was dropped onto me!' );
  $('#'+droppable).val(ui.draggable.data('userid'));
  // $(this).animate({ borderTopColor: '#a8bf12', borderLeftColor: '#a8bf12', borderRightColor: '#a8bf12', borderBottomColor: '#a8bf12' }, 'slow');
  // $(this).('h3').text(''
  var newHeight = $(this).width() + 60;
  $(draggable).animate({
    // opacity: 0.25,
    left: "+=50",
    height: newHeight,
    width: newHeight
  }, 200, function() {
    // Animation complete.
  });
  var $this = $(this);
    ui.draggable.position({
      my: "center",
      at: "center",
      of: $this,
      using: function(pos) {
        $(this).animate(pos, 200, "linear");
      }
    });
}

function handleDropOut( event, ui ) {
	$(this).droppable('option', 'accept', '.member-item');
	var droppable = $(this).data('input');
	$('#'+droppable).val('');
	// $(this).animate({ borderTopColor: '#ccc', borderLeftColor: '#ccc', borderRightColor: '#ccc', borderBottomColor: '#ccc' }, 'slow');
}

$( window ).load(function() {
          resizeBoxes();
          $(window).resize(resizeBoxes);

          function resizeBoxes() {
              //scale business and venue blocks and resize text accordingly
              $(".partner-drop").each(function () {
                  //get the initial height of every div
                  var newHeight = $('.partner-drop').width();
                  $(this).css("height", newHeight);
                  $(this).css("font-size", newHeight / 8);
              });
          }
        });
 
</script>

<div id="content" style="height: 400px;">
 
  <div id="partner-drop1" class="partner-drop" data-input="user_id1"><h3>Drop First Partner Here</h3></div>
  <div id="partner-drop2" class="partner-drop" data-input="user_id2"><h3>Drop Second Partner Here</h3></div>
 
</div>
<?php
}

/**
 * Sidebar data
 *
 * @param $item
 */
function partnership_date_meta_box_handler($item)
{
?>
			
    <ul>
    	<li class="member-items">
    	<?php
    	$OrderBy = 'industry';
		$MyUsers = new Users();
		$MyUsers = $MyUsers->GetAvailable($OrderBy);
		foreach($MyUsers as $MyUser) {
		?>
    		<ul id="<?= $MyUser->LastName ?><?= $MyUser->UserId ?>" class="member-item" data-userid="<?= $MyUser->UserId ?>">
	    		 <li><strong><?= $MyUser->FirstName ?> <?= $MyUser->LastName ?></strong></li>
	    		 <li>Location: <?= $MyUser->Location ?></li>
	    		 <li>Industry: <?= $MyUser->Industry ?></li>
    		</ul>
    		<?php } ?>
    	</li>
    </ul>
<?php
}
/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function validate_partnership($item)
{
    $messages = array();

    // if (empty($item['user_id'])) $messages[] = __('You must select two partners.', 'custom_table_example');
    // if (empty($item['last_name'])) $messages[] = __('Last Name is required', 'custom_table_example');
    // if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('Email is in wrong format', 'custom_table_example');
    // if (empty($item['last_name'])) $messages[] = __('Last Name is required', 'custom_table_example');
    // if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'custom_table_example');
    // if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    // if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    

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
function accountably_partnership_languages()
{
    load_plugin_textdomain('custom_table_example', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'accountably_partnership_languages');
