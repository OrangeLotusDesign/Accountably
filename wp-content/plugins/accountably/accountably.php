<?php

/*
Plugin Name: Accountably
Plugin URI: http://accountably.io
Description: Accountably partner sign up and management.
Author: Alexander Tague
Version: 1.0
*/
global $wpdb;
global $db_version;

// Create Accountably Application table
function accountably_app_install()
{
    global $wpdb;
    $accountably_team = $wpdb->prefix."accountably_team";
    $accountably_user = $wpdb->prefix."accountably_user";
    $accountably_partnership = $wpdb->prefix."accountably_partnership";
    $accountably_partnerships = $wpdb->prefix."accountably_partnerships";
    $accountably_checkins = $wpdb->prefix."accountably_checkins";
    $partnerships_v = $wpdb->prefix."partnerships_v";
    $team_members_v = $wpdb->prefix."team_members_v";
    $checkins_v = $wpdb->prefix."checkins_v";

$accountably_team_sql = "CREATE TABLE IF NOT EXISTS $accountably_team(
    team_id int(11) unsigned NOT NULL AUTO_INCREMENT,
    create_time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    org_name varchar(45) DEFAULT NULL,
    org_slug varchar(45) DEFAULT NULL,
    PRIMARY KEY (team_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$accountably_user_sql .= "CREATE TABLE IF NOT EXISTS $accountably_user (
    user_id int(11) unsigned NOT NULL AUTO_INCREMENT,
    wp_id int(11) NOT NULL,
    create_time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    update_time timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    first_name varchar(45) DEFAULT NULL,
    last_name varchar(45) DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    phone varchar(15) DEFAULT NULL,
    age int(3) DEFAULT NULL,
    job_title varchar(45) DEFAULT NULL,
    industry varchar(45) DEFAULT NULL,
    location varchar(45) DEFAULT NULL,
    goal varchar(250) DEFAULT NULL,
    team_id int(11) unsigned DEFAULT NULL,
    active tinyint(1) NOT NULL DEFAULT '0',
    notes longtext,
    available tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (user_id),
    KEY team_id (team_id),
    CONSTRAINT team_id FOREIGN KEY (team_id) REFERENCES $accountably_team (team_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$accountably_partnership_sql .= "CREATE TABLE IF NOT EXISTS $accountably_partnership (
  partnership_id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  update_time timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  active tinyint(1) NOT NULL DEFAULT '0',
  health int(3) DEFAULT NULL,
  notes longtext,
  PRIMARY KEY (partnership_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$accountably_partnerships_sql .= "CREATE TABLE IF NOT EXISTS $accountably_partnerships (
  partnership_id int(11) unsigned NOT NULL,
  user_id int(11) unsigned NOT NULL,
  PRIMARY KEY (partnership_id,user_id),
  KEY user_id (user_id),
  KEY partnership_id (partnership_id),
  CONSTRAINT partnership_id FOREIGN KEY (partnership_id) REFERENCES $accountably_partnership(partnership_id),
  CONSTRAINT user_id FOREIGN KEY (user_id) REFERENCES $accountably_user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$accountably_checkins_sql .= "CREATE TABLE IF NOT EXISTS $accountably_checkins (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  update_time timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  user_id int(11) unsigned NOT NULL,
  partnership_id int(11) unsigned NOT NULL,
  phase_id int(11) DEFAULT NULL,
  phase_name varchar(45) DEFAULT NULL,
  phase_value varchar(45) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY partnership_id_UNIQUE (partnership_id),
  KEY user_id_UNIQUE (user_id),
  CONSTRAINT partnership_id_chk FOREIGN KEY (partnership_id) REFERENCES $accountably_partnership (partnership_id),
  CONSTRAINT user_id_chk FOREIGN KEY (user_id) REFERENCES $accountably_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$partnerships_detail_v_sql .= "CREATE  OR REPLACE VIEW $partnerships_detail_v
    AS SELECT
      au.user_id, 
      au.first_name,
      au.last_name,
      au.wp_id,
      au.email,
      au.phone,
      au.age,
      au.job_title,
      au.industry,
      au.location,
      au.goal,
      au.team_id,
      au.available,
      ap.partnership_id,
      ap.create_time,
      ap.update_time,
      ap.active,
      ap.health,
      ap.notes
    FROM
      $accountably_partnerships AS aps
    LEFT JOIN
      $accountably_user AS au ON au.user_id = aps.user_id
    LEFT JOIN
      $accountably_partnership AS ap ON ap.partnership_id = aps.partnership_id;";

$partnerships_v_sql .= "CREATE  OR REPLACE VIEW $partnerships_v
    AS SELECT
        ap.partnership_id,
        GROUP_CONCAT(au.user_id) user_ids,
        GROUP_CONCAT(au.wp_id) wp_ids,
        GROUP_CONCAT(au.last_name,', ',au.first_name SEPARATOR ' & ') partners,
        ap.health,
        ap.notes,
        ap.create_time,
        ap.update_time,
        ap.active
    FROM
        $accountably_partnerships AS aps
            INNER JOIN
        $accountably_user AS au ON au.user_id = aps.user_id
          INNER JOIN
        $accountably_partnership AS ap ON ap.partnership_id = aps.partnership_id
    GROUP   BY ap.partnership_id;";

$team_members_v_sql .= "CREATE  OR REPLACE VIEW $team_members_v
  AS SELECT
    au.user_id,
    at.team_id,
    at.org_name,
    concat(au.first_name,' ',au.last_name) as team_member,
    au.email,
    au.phone,
    au.age,
    au.job_title,
    au.industry,
    au.location,
    au.goal,
    au.notes,
    au.active,
    au.available
  FROM
    $accountably_team AS at
  LEFT JOIN
    $accountably_user AS au ON au.team_id = at.team_id;";

$checkins_v_sql .= "CREATE OR REPLACE VIEW $checkins_v
  AS SELECT
    au.user_id,
    ac.partnership_id,
    concat(au.first_name,' ',au.last_name) as partner,
    ac.create_time,
    ac.update_time,
    ac.phase_id,
    ac.phase_name,
    ac.phase_value
  FROM
    $accountably_user AS au
  LEFT JOIN
    $accountably_checkins AS ac ON au.user_id = ac.user_id
  WHERE
    ac.phase_id IS NOT NULL;";


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$wpdb->query("set foreign_key_checks=0");
$wpdb->query($accountably_team_sql);
$wpdb->query($accountably_user_sql);
$wpdb->query($accountably_partnership_sql);
$wpdb->query($accountably_partnerships_sql);
$wpdb->query($accountably_checkins_sql);
$wpdb->query($partnerships_detail_v_sql);
$wpdb->query($partnerships_v_sql);
$wpdb->query($team_members_v_sql);
$wpdb->query($checkins_v_sql);
add_option("db_version", $db_version);

}

add_action('activate_accountably/accountably.php', 'accountably_app_install');

function init_sessions() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'init_sessions');



// Volunteer Header
add_action('wp_head', 'the_form_header');
function the_form_header() {
$file = accountably.php;
if(@file_exists(TEMPLATEPATH.'/css/accountably.css')) {
    echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/css/accountably.css" type="text/css" media="screen" />'."\n"; 
  } else {
    echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/accountably/css/accountably.css" type="text/css" media="screen" />'."\n";
  }
echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="'.plugins_url( 'js/jquery-2.2.1.min.js' , __FILE__ ).'"></script>
<script   src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"   integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw="   crossorigin="anonymous"></script>
<script type="text/javascript" src="'.plugins_url( 'js/jquery.validate.min.js' , __FILE__ ).'"></script>
<script type="text/javascript" src="'.plugins_url( 'js/accountably-app.js' , __FILE__ ).'"></script>';
echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
// echo '<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>';

wp_enqueue_script('mylib', plugins_url() . '/accountably/js/accountably-app.js');
wp_localize_script('mylib', 'WPURLS', array( 'plugin_url' => get_option('plugin_url') ));
}

function admin_header() {
$file = accountably.php;
if(@file_exists(TEMPLATEPATH.'/accountably-app.css')) {
    echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/css/accountably.css" type="text/css" media="screen" />'."\n"; 
  } else {
    echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/accountably/css/accountably.css" type="text/css" media="screen" />'."\n";
  }
}
add_action('admin_head', 'admin_header');

function create_signup_form() {
ob_start();
  include ('signup-form-display.php');
  $inquiry = ob_get_clean();
  return $inquiry;
}

add_shortcode("signup", "create_signup_form");

// add_action('admin_menu', 'accountably_app_menu');

// function accountably_app_menu() {

// $icon = plugins_url( 'images/watermark_icon.png' , __FILE__ );

//   add_menu_page( 'Manage Accountably Partners', 'Accountably', 'manage_options', 'partners', 'accountably_partners', $icon );
//   add_submenu_page( 'partners', 'Partners', 'Partners', 'manage_options', 'partners', 'accountably_partners' );
//   add_submenu_page ( 'partners', 'Partnerships', 'Partnerships', 'manage_options', 'partnerships', 'accountably_partnerships' );
// }

include('partner-admin-page.php');
include('partnership-admin-page.php');

// function accountably_partners() {
//   if (!current_user_can('manage_options'))  {
//     wp_die( __('You do not have sufficient permissions to access this page.') );
//   }
//   include('partner-admin-display.php');
//   // echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
//   //       echo '<h2>My Custom Submenu Page</h2>';
//   //   echo '</div>';
// }

// function accountably_partnerships() {
//   if (!current_user_can('manage_options'))  {
//     wp_die( __('You do not have sufficient permissions to access this page.') );
//   }
//   include('partnerships-admin-display.php');
// }
?>