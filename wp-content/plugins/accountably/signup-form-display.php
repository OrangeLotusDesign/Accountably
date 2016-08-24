<?php
// global $wpdb;
global $current_user;
get_currentuserinfo();
// include('classes.php');

// $MyVolunteers = new Volunteers();
// $MyVolunteers = $MyVolunteers->GetByUser($current_user->ID);
	
// 	foreach($MyVolunteers as $MyVolunteer) {
// 	}
?>
<div id="accountably_signup">
	<p><em>* Denotes required field.</em></p>
	<form action="<?php echo plugins_url( 'admin.php' , __FILE__ ) ?>" method="POST" name="signup_form" id="signup_form">
		<div class="row">
			<div class="medium-6 columns">
				<label>First Name*
					<input type="text" name="first_name" id="first_name" placeholder="Jack">
				</label>
		</div>
			<div class="medium-6 columns">
				<label>Last Name*
					<input type="text" name="last_name" id="last_name" placeholder="Burton">
				</label>
			</div>
		</div>
		<div class="row">
			<div class="medium-6 columns">
				<label>Email Address*
					<input type="email" name="email" id="email" placeholder="me@jackburton.com">
				</label>
			</div>
			<div class="medium-6 columns">
				<label>Confirm Email Address*
					<input type="email" name="confirm_email" id="confirm_email" placeholder="me@jackburton.com">
				</label>
			</div>
		</div>
		<div class="row">
			<div class="medium-6 columns">
				<label>Phone <small>(For us only, not for sharing.)</small>
					<input type="text" name="phone" id="phone" placeholder="(212) 555-5555">
				</label>
			</div>
			<div class="medium-6 columns">
				<label>How old are you?* <small>(We won't tell.)</small>
					<input type="number" name="age" id="age" maxlength="3" placeholder="We know...but it helps with pairing.">
				</label>
			</div>
		</div>
		<div class="row">
			<div class="medium-6 columns">
				<label>Location*
					<input type="text" name="location" id="location" placeholder="Little China">
				</label>
			</div>
			<div class="medium-6 columns ui-widget">
				<label>Industry*
					<input type="text" name="industry" id="industry" placeholder="Transportation">
				</label>
			</div>
		</div>
		<div class="row">
			<div class="medium-6 columns">
				<label>Title*
					<input type="text" name="job_title" id="job_title" placeholder="President">
				</label>
			</div>
			<div id="team-cb" class="medium-6 columns">
				<input id="team" type="checkbox"><label for="team">Are you a member of a company team?*</label>
			</div>
			<div id="team-dd" class="medium-6 columns active">
				<label>Company Team
				<select id="team_id" name="team_id">
					<option value="">Select Team</option>
					<?php $MyTeams = new Teams();
					$MyTeams = $MyTeams->GetAll();
					foreach($MyTeams as $MyTeam) {
					?>
					<option value="<?= $MyTeam->TeamId ?>"><?= $MyTeam->OrgName ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="small-12 columns">
				<label>Goal* <small>(Briefly in 100 words or less.)</small>
					<textarea name="goal" id="goal" placeholder="Getting my truck back."></textarea>
					<script type="text/javascript">
						$("#goal").textareaCounter({ limit: 100 });
					</script>
				</label>
			</div>
		</div>
		<div class="row">
			<div class="medium-6 columns">
				<div class="g-recaptcha" data-sitekey="6LdLxBgTAAAAAMcbmDjXeuPg2NOQqh0h2rlxM9zv"></div>
			</div>
		</div>
		<input type="hidden" name="wp_id" value="<?= $current_user->ID ?>" id="wp_id" />
		<input type="hidden" name="active" value="0" id="active" />
		<input type="hidden" name="available" value="1" id="available" />
		<input type="hidden" name="teammate" value="None" id="teammate" />
		<div class="row">
			<div class="small-12 columns">
				<input type="submit" name="submit" value="Sign Up!" id="submit" class="button large expanded" style="margin-top: 20px;">
			</div>
		</div>
	</form>
</div>