<?php
$user = $_GET['user'];

include('classes.php');

$MyUsers = new Users();
$MyUsers = $MyUsers->GetById($user);
	
	foreach($MyUsers as $MyUser) {
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Volunteer Application for <?= $MyUser->FirstName ?> <?= $MyUser->LastName ?></title>
	<?php
	if(@file_exists(TEMPLATEPATH.'/accountably-app.css')) {
		echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/accountably-app.css" type="text/css" media="screen" />'."\n";	
	} else {
		echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/accountably/accountably-app.css" type="text/css" media="screen" />'."\n";
	}
	?>
</head>
<body>
<div id="volunteer_view">
	<p style="text-transform: uppercase; font-size: 14px; color: #666;"><strong>Last Save:</strong> <?php echo date("m-d-Y, h:i A", strtotime($MyUser->Timestamp)); ?></p>
	<form action="<?php echo plugins_url( 'admin.php?action=member-update' , __FILE__ ) ?>" method="POST" name="member_form" id="member_form">
		<fieldset>
			<legend id="personal_information_toggle">Partner Information</legend>
			<ol id="personal_information">
				<li><label for="first_name">First Name: *</label><input type="text" name="first_name" id="first_name" value="<?= $MyUser->FirstName ?>"></li>
				<li><label for="last_name">Last Name: *</label><input type="text" name="last_name" id="last_name" value="<?= $MyUser->LastName ?>"></li>
				<li><label for="email">Email: *</label><input type="text" name="email" id="email" value="<?= $MyUser->Email ?>"></a></li>
				<li><label for="phone">Phone: *</label><input type="text" name="phone" id="phone" value="<?= $MyUser->Phone ?>"></li>
				<li><label for="age">Age: *</label><input type="text" name="age" id="age" value="<?= $MyUser->Age ?>"></li>
				<li><label for="location">Location: *</label><input type="text" name="location" id="location" value="<?= $MyUser->Location ?>"></li>
				<li><label for="industry">Industry: *</label><input type="text" name="industry" id="industry" value="<?= $MyUser->Industry ?>"></li>
				<li><label for="job_title">Title: *</label><input type="text" name="job_title" id="job_title" value="<?= $MyUser->JobTitle ?>"></li>
				<li><label for="goal">Goal: *</label><input type="text" name="goal" id="goal" value="<?= $MyUser->Goal ?>"></li>
				<li><label for="notes">Notes: *</label><textarea name="notes" id="notes"><?= $MyUser->Notes ?></textarea></li>
			</ol>
		</fieldset>
		<input type="hidden" name="confirmed" value="<?= $MyUser->Confirmed ?>" id="confirmed" />
		<input type="hidden" name="user_id" value="<?= $current_user->ID ?>" id="user_id" />
		<input type="submit" name="save" value="Save" id="submit" class="button">
	</form>
</div>
</body>
</html>