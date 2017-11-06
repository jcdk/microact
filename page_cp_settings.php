<?php
/*
Template Name: _settings
*/
if (!is_user_logged_in()) { wp_redirect(home_url('/login/?redirect_to=' . home_url('/settings/'))); exit; }

//Let users who login thru facebook and twitter change their username
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_name'] == 'change_username_form') {
	global $wpdb;
	
	if ($_GET['user'] && (current_user_can('administrator') || current_user_can('editor'))) {
		$user_info = get_userdata($_GET['user']);
	} else {
		$user_info = get_userdata($user_ID);
	}
	
	$sanitized_user_login = sanitize_user( $_POST['change_username'] );
	
	// Check the username
	if ( $sanitized_user_login == '' ) {
		$username_error = __( '<strong>ERROR</strong>: Please enter a username.', 'ipin' );
	} elseif ( ! validate_username( $user_login ) ) {
		$username_error = __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'ipin' );
		$sanitized_user_login = '';
	} elseif ( username_exists( $sanitized_user_login ) && $sanitized_user_login != $user_info->user_login ) {
		$username_error = __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.', 'ipin' );
	}
	
	if (!$username_error) {
		$user_nicename = sanitize_title( $sanitized_user_login );
		$q = sprintf( "UPDATE %s SET user_login='%s', user_nicename='%s' WHERE ID=%d", $wpdb->users, $sanitized_user_login, $user_nicename, (int) $user_info->ID );
		$wpdb->query($q);
		update_user_meta( $user_info->ID, 'nickname', $sanitized_user_login );
		update_user_meta( $user_info->ID, 'ipin_changed_username', '1' );
		wp_redirect(home_url('/login/?redirect_to=' . home_url('/settings/')));
		exit;
	}
}

//Save Settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_name'] == 'save_settings_form') {
	if ($_GET['user'] && (current_user_can('administrator') || current_user_can('editor'))) {
		$user_info = get_userdata($_GET['user']);
		$errors = ipin_edit_user($_GET['user']);
	} else {
		$user_info = get_userdata($user_ID);
		$errors = ipin_edit_user($user_ID);
	}

	if ($_POST['ipin_user_location'] != $user_info->ipin_user_location)
		update_user_meta($user_info->ID, 'ipin_user_location', sanitize_text_field($_POST['ipin_user_location']));
	if ($_POST['ipin_user_facebook'] != $user_info->ipin_user_facebook)
		update_user_meta($user_info->ID, 'ipin_user_facebook', sanitize_text_field($_POST['ipin_user_facebook']));
	if ($_POST['ipin_user_twitter'] != $user_info->ipin_user_twitter)
		update_user_meta($user_info->ID, 'ipin_user_twitter', sanitize_text_field($_POST['ipin_user_twitter']));
	if ($_POST['ipin_user_pinterest'] != $user_info->ipin_user_pinterest)
		update_user_meta($user_info->ID, 'ipin_user_pinterest', sanitize_text_field($_POST['ipin_user_pinterest']));
	if ($_POST['ipin_user_googleplus'] != $user_info->ipin_user_googleplus)
		update_user_meta($user_info->ID, 'ipin_user_googleplus', sanitize_text_field($_POST['ipin_user_googleplus']));
	if ($_POST['ipin_user_notify_likes'] != $user_info->ipin_user_notify_likes)
		update_user_meta($user_info->ID, 'ipin_user_notify_likes', sanitize_text_field($_POST['ipin_user_notify_likes']));
	if ($_POST['ipin_user_notify_repins'] != $user_info->ipin_user_notify_repins)
		update_user_meta($user_info->ID, 'ipin_user_notify_repins', sanitize_text_field($_POST['ipin_user_notify_repins']));
	if ($_POST['ipin_user_notify_follows'] != $user_info->ipin_user_notify_follows)
		update_user_meta($user_info->ID, 'ipin_user_notify_follows', sanitize_text_field($_POST['ipin_user_notify_follows']));
	if ($_POST['ipin_user_notify_comments'] != $user_info->ipin_user_notify_comments)
		update_user_meta($user_info->ID, 'ipin_user_notify_comments', sanitize_text_field($_POST['ipin_user_notify_comments']));

	$savesuccess = '1';
}

//function from wp-admin/includes/user.php
function ipin_edit_user( $user_id = 0 ) {
	global $wp_roles;
	$user = new stdClass;
	if ( $user_id ) {
		$update = true;
		$user->ID = (int) $user_id;
		$userdata = get_userdata( $user_id );
		$user->user_login = wp_slash( $userdata->user_login );
	} else {
		$update = false;
	}

	if ( !$update && isset( $_POST['user_login'] ) )
		$user->user_login = sanitize_user($_POST['user_login'], true);

	$pass1 = $pass2 = '';
	if ( isset( $_POST['pass1'] ) )
		$pass1 = $_POST['pass1'];
	if ( isset( $_POST['pass2'] ) )
		$pass2 = $_POST['pass2'];

	if ( isset( $_POST['role'] ) && current_user_can( 'edit_users' ) ) {
		$new_role = sanitize_text_field( $_POST['role'] );
		$potential_role = isset($wp_roles->role_objects[$new_role]) ? $wp_roles->role_objects[$new_role] : false;
		// Don't let anyone with 'edit_users' (admins) edit their own role to something without it.
		// Multisite super admins can freely edit their blog roles -- they possess all caps.
		if ( ( is_multisite() && current_user_can( 'manage_sites' ) ) || $user_id != get_current_user_id() || ($potential_role && $potential_role->has_cap( 'edit_users' ) ) )
			$user->role = $new_role;

		// If the new role isn't editable by the logged-in user die with error
		$editable_roles = get_editable_roles();
		if ( ! empty( $new_role ) && empty( $editable_roles[$new_role] ) )
			wp_die(__('You can&#8217;t give users that role.', 'ipin'));
	}

	//edited: store the original email
	$original_user_email = $userdata->user_email;

	if ( isset( $_POST['email'] ))
		$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
	if ( isset( $_POST['url'] ) ) {
		if ( empty ( $_POST['url'] ) || $_POST['url'] == 'http://' ) {
			$user->user_url = '';
		} else {
			$user->user_url = esc_url_raw( $_POST['url'] );
			$protocols = implode( '|', array_map( 'preg_quote', wp_allowed_protocols() ) );
			$user->user_url = preg_match('/^(' . $protocols . '):/is', $user->user_url) ? $user->user_url : 'http://'.$user->user_url;
		}
	}
	if ( isset( $_POST['first_name'] ) )
		$user->first_name = sanitize_text_field( $_POST['first_name'] );
	if ( isset( $_POST['last_name'] ) )
		$user->last_name = sanitize_text_field( $_POST['last_name'] );
	if ( isset( $_POST['nickname'] ) )
		$user->nickname = sanitize_text_field( $_POST['nickname'] );
	if ( isset( $_POST['display_name'] ) )
		$user->display_name = sanitize_text_field( $_POST['display_name'] );

	if ( isset( $_POST['description'] ) )
		$user->description = trim( $_POST['description'] );

	foreach ( wp_get_user_contact_methods( $user ) as $method => $name ) {
		if ( isset( $_POST[$method] ))
			$user->$method = sanitize_text_field( $_POST[$method] );
	}

	if ( $update ) {
		$user->rich_editing = isset( $_POST['rich_editing'] ) && 'false' == $_POST['rich_editing'] ? 'false' : 'true';
		$user->admin_color = isset( $_POST['admin_color'] ) ? sanitize_text_field( $_POST['admin_color'] ) : 'fresh';
		$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] ) ? 'true' : 'false';
	}

	$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && 'true' == $_POST['comment_shortcuts'] ? 'true' : '';

	$user->use_ssl = 0;
	if ( !empty($_POST['use_ssl']) )
		$user->use_ssl = 1;

	$errors = new WP_Error();

	/* checking that username has been typed */
	if ( $user->user_login == '' )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.', 'ipin' ) );

	/* checking the password has been typed twice */
	/**
	 * Fires before the password and confirm password fields are checked for congruity.
	 *
	 * @since 1.5.1
	 *
	 * @param string $user_login The username.
	 * @param string &$pass1     The password, passed by reference.
	 * @param string &$pass2     The confirmed password, passed by reference.
	 */
	do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );

	if ( $update ) {
		if ( empty($pass1) && !empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.', 'ipin' ), array( 'form-field' => 'pass1' ) );
		elseif ( !empty($pass1) && empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.', 'ipin' ), array( 'form-field' => 'pass2' ) );
			
		//edited: added to check password length
		if ( !empty($pass1) && !empty($pass2) )
			if ( strlen( $pass1 ) < 6 ) {
			$errors->add('password_too_short', "<strong>ERROR</strong>: Passwords must be at least 6 characters long", 'ipin');
		}
	} else {
		if ( empty($pass1) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password.', 'ipin' ), array( 'form-field' => 'pass1' ) );
		elseif ( empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password twice.', 'ipin' ), array( 'form-field' => 'pass2' ) );
	}

	/* Check for "\" in password */
	if ( false !== strpos( wp_unslash( $pass1 ), "\\" ) )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".', 'ipin' ), array( 'form-field' => 'pass1' ) );

	/* checking the password has been typed twice the same */
	if ( $pass1 != $pass2 )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in the two password fields.', 'ipin' ), array( 'form-field' => 'pass1' ) );

	if ( !empty( $pass1 ) )
		$user->user_pass = $pass1;

	if ( !$update && isset( $_POST['user_login'] ) && !validate_username( $_POST['user_login'] ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'ipin' ));

	if ( !$update && username_exists( $user->user_login ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.', 'ipin' ));

	/* checking e-mail address */
	$verify_new_email = $user_id; //edited: verify new email
	if ( empty( $user->user_email ) ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an email address.', 'ipin' ), array( 'form-field' => 'email' ) );
	} elseif ( !is_email( $user->user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', 'ipin' ), array( 'form-field' => 'email' ) );
	} elseif ( ( $owner_id = email_exists($user->user_email) ) && ( !$update || ( $owner_id != $user->ID ) ) ) {
		$errors->add( 'email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.', 'ipin'), array( 'form-field' => 'email' ) );
	//edited: requires email verification if email is changed
	} elseif ($userdata->user_email != $_POST['email'] && !current_user_can('administrator') && !current_user_can('editor'))  {
		//store new email temporarily
		update_user_meta($user_id, '_new_email', $user->user_email);

		$new_email_key = wp_generate_password(20, false);
		update_user_meta($user_id, '_new_email_key', $new_email_key);
		
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message .= __('Please click the link to verify your email:', 'ipin') . "\r\n";
		$message .= home_url('/settings/');
		$message .= sprintf('?email=verify&login=%s&key=%s', rawurlencode($user->user_login), $new_email_key);

		wp_mail($user->user_email, sprintf(__('[%s] Email Verification', 'ipin'), $blogname), $message);

		$user->user_email = $original_user_email;
		$verify_new_email = 'verify_new_email';
	}

	/**
	 * Fires before user profile update errors are returned.
	 *
	 * @since 2.8.0
	 *
	 * @param array   &$errors An array of user profile update errors, passed by reference.
	 * @param bool    $update  Whether this is a user update.
	 * @param WP_User &$user   WP_User object, passed by reference.
	 */
	do_action_ref_array( 'user_profile_update_errors', array( &$errors, $update, &$user ) );

	if ( $errors->get_error_codes() )
		return $errors;

	if ( $update ) {
		$user_id = wp_update_user( $user );
	} else {
		$user_id = wp_insert_user( $user );
		wp_new_user_notification( $user_id, isset( $_POST['send_password'] ) ? wp_unslash( $pass1 ) : '' );
	}
	return $verify_new_email; //edited: verify new email
}

//check email verification
if (isset($_GET['email']) && $_GET['email'] == 'verify') {
	if ($_GET['user'] && (current_user_can('administrator') || current_user_can('editor')))
		$user_info = get_userdata($_GET['user']);
	else
		$user_info = get_userdata($user_ID);
		
	if ($_GET['login'] == $user_info->user_login && $_GET['key'] == $user_info->_new_email_key) {
		wp_update_user(array('ID'=> $user_info->ID, 'user_email' => get_user_meta($user_info->ID, '_new_email', true)));
		delete_user_meta($user_info->ID, '_new_email');
		delete_user_meta($user_info->ID, '_new_email_key');
		$email_verified = 'yes';
	} else {
		$email_verified = 'no';
	}
}

//retreive latest userinfo even after updating above
if (isset($_GET['user']) && (current_user_can('administrator') || current_user_can('editor')))
	$user_info = get_userdata($_GET['user']);
else
	$user_info = get_userdata($user_ID);

if (!$user_info) wp_die(__('No Such User.', 'ipin'));

if (user_can($user_info->ID, 'administrator') && !current_user_can('administrator')) wp_die(__('Administrator Profile: No Access', 'ipin'));

get_header();
?>
<div class="container">
	<div class="row">

		<div class="col-sm-2"></div>

		<div class="col-sm-8 usercp-wrapper">
			<?php $wsl_email_example = stripos($user_info->user_email, '@example.com'); if ($user_info->wsl_current_provider != '' && $wsl_email_example === false) { ?>
			<div class="hide">
			<?php } ?>
				<h1><?php _e('Settings', 'ipin'); if (isset($_GET['user']) && (current_user_can('administrator') || current_user_can('editor'))) echo ' - ' . $user_info->user_login; ?></h1>
			<?php if ($user_info->wsl_current_provider != '' && $wsl_email_example === false) { ?>
			</div>
			<?php } ?>
			
			<?php 
			$user_registered_timestamp = strtotime($user_info->user_registered);
			$interval = time()- $user_registered_timestamp;
			$days_since_registered = floor($interval/60/60/24);
			$days_left = 3 - $days_since_registered;
			if ($days_left > 0 && $user_info->wsl_current_provider != '' && get_user_meta($user_info->ID, 'ipin_changed_username', 'true') != '1') {
			 ?>
			<div class="alert alert-info">
				<form id="usernameform" name="usernameform" action="<?php echo home_url('/settings/'); echo $_GET['user'] ? '?user='  . $_GET['user'] : ''; ?>" method="post">
					<br />
					<?php
					if ($username_error) {
						echo '<p class="text-error"><strong>';
						echo $username_error;
						echo '</strong></p>';
					}
					?>
					<p><strong><?php _e('Change Profile Username? (Days Left:', 'ipin'); ?> <?php echo $days_left; ?>)</strong></p>
					<input id="change_username" class="form-control" type="text" name="change_username" value="<?php echo esc_attr($user_info->user_login); ?>" />
					<input type="hidden" name="form_name" id="form_name" value="change_username_form" />
					<p></p>
					<input type="submit" class="btn btn-success btn-block" style="font-weight: bold;" name="username-change" id="username-change" value="<?php _e('Change', 'ipin'); ?>" />
					<p class="help-block"><?php _e('You will be logged out upon successful changing of username. Please login again with', 'ipin'); echo ' ' . $user_info->wsl_current_provider; ?>.</p>
					<br />
				</form>
			</div>
			<?php } ?>

			<?php if (isset($errors) && is_wp_error($errors)) { ?>
				<div class="error-msg"><div class="alert alert-warning"><strong><?php echo $errors->get_error_message(); ?></strong></div></div>
			<?php }	else if (isset($savesuccess) && $savesuccess == '1') { ?>
				<div class="error-msg"><div class="alert alert-success"><strong><?php _e('Settings Saved.', 'ipin'); ?></strong></div></div>
			<?php } ?>
			
			<?php if (isset($errors) && $errors == 'verify_new_email') { ?>
				<div class="error-msg"><div class="alert alert-warning"><strong><?php _e('Your email will be changed upon verification. Please check your new email for verification link.', 'ipin'); ?></strong></div></div>
			<?php } ?>
			
			<?php if (isset($email_verified) && $email_verified == 'yes') { ?>
				<div class="error-msg"><div class="alert alert-success"><strong><?php _e('Your email has been verified and updated.', 'ipin'); ?></strong></div></div>
			<?php } else if (isset($email_verified) && $email_verified == 'no') { ?>
				<div class="error-msg"><div class="alert alert-warning"><strong><?php _e('Invalid verification key', 'ipin'); ?></strong></div></div>
			<?php } ?>

			<br />
			<form id="settingsform" name="settingsform" action="<?php echo home_url('/settings/'); echo $_GET['user'] ? '?user='  . $_GET['user'] : ''; ?>" method="post" enctype="multipart/form-data">
				<?php if ($user_info->wsl_current_provider != '' && $wsl_email_example === false) { ?>
				<div class="hide">
				<?php } ?>
					<div class="form-group">
						<label class="form-label" for="email"><?php _e('Email', 'ipin'); ?></label>
						<input class="form-control" type="email" name="email" id="email" value="<?php echo esc_attr($user_info->user_email); ?>" tabindex="10" />
						<?php if ($wsl_email_example !== false) { ?>
							<p class="help-block"><?php echo __('Invalid email provided by', 'ipin') . ' ' . $user_info->wsl_current_provider . '. ' . __('Please enter a valid email to receive email notifications.', 'ipin'); ?></p>
						<?php } ?>
					</div>
	
					<?php if ($user_info->wsl_current_provider == '') { ?>
						<div class="form-group">
							<label class="form-label" for="pass1"><?php _e('Password', 'ipin') ?></label>
							<input class="form-control" type="password" name="pass1" id="pass1" size="20" value="" autocomplete="off" tabindex="20" />
						</div>
	
						<div class="form-group">					
							<label class="form--label" for="pass2"><?php _e('Confirm Password', 'ipin') ?></label>
							<input class="form-control" type="password" name="pass2" id="pass2" size="20" value="" autocomplete="off" tabindex="30" />
							<p class="help-block"><?php _e('If you would like to change the password, type a new one. Otherwise leave them blank.', 'ipin'); ?></p>
						</div>
					<?php } ?>

					<br /><br />
				<?php if ($user_info->wsl_current_provider != '' && $wsl_email_example === false) { ?>
				</div>
				<?php } ?>
				<h1><?php _e('Profile', 'ipin'); ?><p></p><a class="btn btn-success btn-sm" href="<?php if ($_GET['user']) echo get_author_posts_url($_GET['user']); else echo get_author_posts_url($user_ID);  ?>" target="_blank"><strong><?php _e('See Your Public Profile', 'ipin'); ?></strong></a></h1>

				<br />
				<div class="form-group">
					<label class="form-label" for="display_name"><?php _e('Display Name', 'ipin'); ?></label>
					<input class="form-control" type="text" name="display_name" id="display_name" value="<?php echo esc_attr($user_info->display_name); ?>" tabindex="40" />
				</div>

				<div class="form-group">
					<label class="form-label" for="description"><?php _e('About', 'ipin'); ?></label>
					<textarea class="form-control" name="description" id="description" tabindex="50"><?php echo $user_info->user_description; ?></textarea>
				</div>

				<div class="form-group">
					<label class="form-label" for="ipin_user_location"><?php _e('Location', 'ipin'); ?></label>
					<input class="form-control" type="text" name="ipin_user_location" id="ipin_user_location" value="<?php echo esc_attr($user_info->ipin_user_location); ?>" tabindex="60" placeholder="<?php _e('city, country', 'ipin'); ?>" />
				</div>

				<div class="form-group">
					<label class="form-label" for="url"><?php _e('Website', 'ipin'); ?></label>
					<input class="form-control" type="text" name="url" id="url" value="<?php echo esc_attr($user_info->user_url); ?>" tabindex="70" placeholder="<?php _e('myweb.com', 'ipin'); ?>" />
				</div>

				<div class="form-group">
					<label class="form-label" for="ipin_user_facebook"><?php _e('Facebook Username', 'ipin'); ?></label>
					<input class="form-control" type="text" name="ipin_user_facebook" id="ipin_user_facebook" value="<?php echo esc_attr($user_info->ipin_user_facebook); ?>" tabindex="80" placeholder="<?php _e('myfb', 'ipin'); ?>" />
				</div>

				<div class="form-group">
					<label class="form-label" for="ipin_user_twitter"><?php _e('Twitter Username', 'ipin'); ?></label>
					<input class="form-control" type="text" name="ipin_user_twitter" id="ipin_user_twitter" value="<?php echo esc_attr($user_info->ipin_user_twitter); ?>" tabindex="90" placeholder="<?php _e('mytwitter', 'ipin'); ?>" />
				</div>

				<div class="form-group">
					<label class="form-label" for="ipin_user_pinterest"><?php _e('Pinterest Username', 'ipin'); ?></label>
					<input class="form-control" type="text" name="ipin_user_pinterest" id="ipin_user_pinterest" value="<?php echo esc_attr($user_info->ipin_user_pinterest); ?>" tabindex="90" placeholder="<?php _e('mypinterest', 'ipin'); ?>" />
				</div>

				<div class="form-group">
					<label class="form-label" for="ipin_user_googleplus"><?php _e('Google+ ID', 'ipin'); ?></label>
					<input class="form-control" type="text" name="ipin_user_googleplus" id="ipin_user_googleplus" value="<?php echo esc_attr($user_info->ipin_user_googleplus); ?>" tabindex="100"  placeholder="<?php _e('+mygoogle or 123456789', 'ipin'); ?>" />
				</div>
				
				<span id="avatar-anchor"></span>
				
				<br />
				<h1><?php _e('Email Notifications', 'ipin'); ?></h1>
				<?php if ($wsl_email_example !== false) { ?>
					<p>
					<label><?php echo __('Invalid email provided by', 'ipin') . ' ' . $user_info->wsl_current_provider . '. ' . __('Please enter a valid email to receive email notifications.', 'ipin'); ?></label>
					</p>
				<?php } ?>

				<br />
				<div class="form-group">
					<div class="onoffswitch<?php if ($wsl_email_example !== false) echo ' disabled'; ?>">
					<input id="ipin_user_notify_follows" class="onoffswitch-checkbox" type="checkbox" name="ipin_user_notify_follows" value="1"<?php if (get_user_meta($user_info->ID, 'ipin_user_notify_follows', true) == '1') echo ' checked'; ?>>
					<label class="onoffswitch-label" for="ipin_user_notify_follows" tabindex="120">
						<span class="onoffswitch-inner"></span>
						<span class="onoffswitch-switch"></span>
					</label>
					</div>

					<span class="onoffswitch-text"><?php _e('Notify when someone follows me', 'ipin'); ?></span>
					<div class="clearfix"></div>
				</div>
				
				<div class="form-group">
					<div class="onoffswitch<?php if ($wsl_email_example !== false) echo ' disabled'; ?>">
					<input id="ipin_user_notify_likes" class="onoffswitch-checkbox" type="checkbox" name="ipin_user_notify_likes" value="1"<?php if (get_user_meta($user_info->ID, 'ipin_user_notify_likes', true) == '1') echo ' checked'; ?>>
					<label class="onoffswitch-label" for="ipin_user_notify_likes" tabindex="130">
						<span class="onoffswitch-inner"></span>
						<span class="onoffswitch-switch"></span>
					</label>
					</div>

					<span class="onoffswitch-text"><?php _e('Notify when someone likes my pin', 'ipin'); ?></span>
					<div class="clearfix"></div>
				</div>
				
				<div class="form-group">
					<div class="onoffswitch<?php if ($wsl_email_example !== false) echo ' disabled'; ?>">
					<input id="ipin_user_notify_repins" class="onoffswitch-checkbox" type="checkbox" name="ipin_user_notify_repins" value="1"<?php if (get_user_meta($user_info->ID, 'ipin_user_notify_repins', true) == '1') echo ' checked'; ?>>
					<label class="onoffswitch-label" for="ipin_user_notify_repins" tabindex="140">
						<span class="onoffswitch-inner"></span>
						<span class="onoffswitch-switch"></span>
					</label>
					</div>

					<span class="onoffswitch-text"><?php _e('Notify when someone repins my pin', 'ipin'); ?></span>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<div class="onoffswitch<?php if ($wsl_email_example !== false) echo ' disabled'; ?>">
					<input id="ipin_user_notify_comments" class="onoffswitch-checkbox" type="checkbox" name="ipin_user_notify_comments" value="1"<?php if (get_user_meta($user_info->ID, 'ipin_user_notify_comments', true) == '1') echo ' checked'; ?>>
					<label class="onoffswitch-label" for="ipin_user_notify_comments" tabindex="150">
						<span class="onoffswitch-inner"></span>
						<span class="onoffswitch-switch"></span>
					</label>
					</div>

					<span class="onoffswitch-text"><?php _e('Notify when someone comments on my pin', 'ipin'); ?></span>
					<div class="clearfix"></div>
				</div>
				
				<br /><br />

				<input type="hidden" name="user_login" id="user_login" value="<?php echo esc_attr($user_info->user_login); ?>" />
				<input type="hidden" name="form_name" id="form_name" value="save_settings_form" />
				<input type="submit" class="btn btn-success btn-block btn-ipin-custom" name="wp-submit" id="wp-submit" value="<?php _e('Save Settings', 'ipin'); ?>" tabindex="200" />
			</form>
			
			<form name="avatarform" id="avatarform" method="post" enctype="multipart/form-data">
				<br />
				<label class="form-label" for="ipin_user_avatar"><?php _e('Avatar (Recommended 300 x 300px)', 'ipin'); ?></label>
				<br />
				
				<div class="upload-wrapper btn btn-sm btn-success">
					<span><?php _e('Browse &amp; Upload', 'ipin'); ?></span>
					<input id="ipin_user_avatar" class="upload" type="file" name="ipin_user_avatar" accept="image/*" tabindex="110" /> 
				</div>
				
				<p></p>
				
				<?php if ($user_info->ipin_user_avatar != '' && $user_info->ipin_user_avatar != 'deleted') {
						$imgsrc = wp_get_attachment_image_src($user_info->ipin_user_avatar,'thumbnail');
				} ?>
				<div id="avatar-wrapper"<?php if ($user_info->ipin_user_avatar == '' || $user_info->ipin_user_avatar == 'deleted') echo ' class="hide"'?>>
					<img src="<?php echo $imgsrc[0]; ?>" alt="" class="img-polaroid" width="120" height="120" />
					<button id="avatar-delete" class="btn btn-danger btn-xs" data-id="<?php echo $user_info->ID; ?>" type="button"><i class="fa fa-times"></i></button>
				</div>
				<input type="hidden" name="avatar-userid" id="avatar-userid" value="<?php echo $user_info->ID; ?>" />
				<input type="hidden" name="action" id="action" value="ipin-upload-avatar" />
				<input type="hidden" name="ajax-nonce" id="ajax-nonce" value="<?php echo wp_create_nonce('upload_avatar'); ?>" />
				<div class="ajax-loader-avatar ajax-loader hide"></div>
				<div class="error-msg-avatar"></div>
			</form>
			
			<form name="coverform" id="coverform" method="post" enctype="multipart/form-data">
				<br />
				<label for="ipin_user_cover"><?php _e('Profile Cover (Recommended 1500 x 300px)', 'ipin'); ?></label>
				<br />
				
				<div class="upload-wrapper btn btn-sm btn-success">
					<span><?php _e('Browse &amp; Upload', 'ipin'); ?></span>
					<input id="ipin_user_cover" class="upload" type="file" name="ipin_user_cover" accept="image/*" tabindex="115" /> 
				</div>
				
				<p></p>
				
				<?php if ($user_info->ipin_user_cover != '') {
						$imgsrc = wp_get_attachment_image_src($user_info->ipin_user_cover,'thumbnail');
				} ?>
				<div id="cover-wrapper"<?php if ($user_info->ipin_user_cover == '') echo ' class="hide"'?>>
					<img src="<?php echo $imgsrc[0]; ?>" alt="" class="img-polaroid" width="120" height="120" />
					<button id="cover-delete" class="btn btn-danger btn-xs" data-id="<?php echo $user_info->ID; ?>" type="button"><i class="fa fa-times"></i></button>
				</div>
				<input type="hidden" name="cover-userid" id="cover-userid" value="<?php echo $user_info->ID; ?>" />
				<input type="hidden" name="action" id="action" value="ipin-upload-cover" />
				<input type="hidden" name="ajax-nonce" id="ajax-nonce" value="<?php echo wp_create_nonce('upload_cover'); ?>" />
				<div class="ajax-loader-cover ajax-loader hide"></div>
				<div class="error-msg-cover"></div>
			</form>

			<?php if (of_get_option('delete_account') == 'enable' && (current_user_can('administrator') || $user_info->ID == $user_ID)) { ?>
			<br />
			<p class="moreoptions text-center">
			<button class="btn btn-grey" id="ipin-delete-account" type="button"><?php _e('Delete Account', 'ipin'); ?></button>
			<?php } ?>
			</p>
		</div>

		<div class="col-sm-2"></div>
	</div>
</div>

<div class="modal ipin-modal" id="delete-account-modal" tabindex="-1" aria-hidden="true" role="alertdialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4>
				<?php if (!user_can($user_info->ID, 'administrator')) { ?>
				<h4><?php _e('All data and profile will be deleted.', 'ipin'); ?> <br /> <?php _e('Are you sure you want to permanently delete this account?', 'ipin'); ?></h4>
				<?php } else { ?>		
					<?php _e('This is an administrator account. To delete this account, go to WP-Admin > Users.', 'ipin'); ?>
				<?php } ?>
				</h4>
			</div>
			<div class="modal-body text-right">
				<a href="#" class="btn btn-default" data-dismiss="modal"><strong><?php _e('Cancel', 'ipin'); ?></strong></a>
				<?php if (!user_can($user_info->ID, 'administrator')) { ?>
				<a href="#" id="ipin-delete-account-confirmed" class="btn btn-danger" data-user_id="<?php echo $user_info->ID; ?>"><strong><?php _e('Delete Account', 'ipin'); ?></strong></a> 
				<?php } ?>
				<div class="ajax-loader-delete-account ajax-loader hide"></div>
				<p></p>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#avatarform').css('top', $('#avatar-anchor').offset().top-120);
	$('#coverform').css('top', $('#avatarform').offset().top+$('#avatarform').height()-70);
	$('#avatar-anchor').css('margin-bottom', $('#avatarform').height()+$('#coverform').height()+30);
	
	$(window).resize(function() {
		$('#avatarform').css('top', $('#avatar-anchor').offset().top-105);
		$('#coverform').css('top', $('#avatarform').offset().top+$('#avatarform').height()-70);
		$('#avatar-anchor').css('margin-bottom', $('#avatarform').height()+$('#coverform').height()+30);
	});
});
</script>

<?php
wp_enqueue_script('ipin_jquery_form', get_template_directory_uri() . '/js/jquery.form.min.js', array('jquery'), null, true);
get_footer();
?>