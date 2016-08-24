<?php
global $wpdb;

if(!class_exists('User')){ include_once 'classes.php'; }

function form_insert() {
date_default_timezone_set('America/New_York');
$current_time = date('Y-m-d G:i:s');

		$MyUser = new User();
		$MyUser->WPId = $_POST['wp_id'];
		$MyUser->CreateTime = $current_time;
		$MyUser->FirstName = $_POST['first_name'];
		$MyUser->LastName = $_POST['last_name'];
		$MyUser->Email = $_POST['email'];
		$MyUser->Phone = $_POST['phone'];
		$MyUser->Age = $_POST['age'];
		$MyUser->JobTitle = $_POST['job_title'];
		$MyUser->Industry = $_POST['industry'];
		$MyUser->Location = $_POST['location'];
		$MyUser->Goal = $_POST['goal'];
		$MyUser->TeamId = $_POST['team_id'];
		$MyUser->Active = $_POST['active'];

		$MyUser->Save();
	}

form_insert();

header('Location: http://accountably.dev/thank-you/');

?>