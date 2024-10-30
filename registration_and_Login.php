<?php

/*
Plugin Name: Custom Registration and Login
Plugin URI: https://www.f5buddy.com
Description: This plugin involves log-in & registration’s functionality on front-end. We can easily create our login & registration page on front-end through this plug-in. It also provides profile page & is most user friendly from user’s perspective. It provides short code by which an user can easily use this.
Version: 1.0
Author: F5buddy
Author URI: https://f5buddy.com
*/
//$plugins_url = plugins_url('/registration_login/css/form-style.css');
wp_register_style( 'custom_css', plugins_url('css/form-style.css',__FILE__ ) );
wp_enqueue_style('custom_css');

/* user registration login form start*/
function crlregistrationform() {

	

	// only show the registration form to non-logged-in members

	if(!is_user_logged_in()) {

	

		global $custom_load_css;

		

		// set this to true so the CSS is loaded

		$custom_load_css = true;

		

		// check to make sure user registration is enabled

		$registration_enabled = get_option('users_can_register');

	

		// only show the registration form if allowed

		if($registration_enabled) {

			$output = crlregistrationformfields();

		} else {

			$output = __('User registration is not enabled');

		}

		return $output;

	}

}

add_shortcode('register_form', 'crlregistrationform');

$shortcode = 'register_form';
/* user registration login form end*/
/*user login form start*/
function crlloginform() {
	$output='';
	if(!is_user_logged_in()) {
		global $custom_load_css;
		// set this to true so the CSS is loaded
		$custom_load_css = true;
		$output = crlloginformfields();
	} else { ?>
		<p>User is Already Logedin</p>
	<?php wp_loginout( get_home_url().'/login/'); ?>
	<?php }
	return $output;
}

add_shortcode('login_form', 'crlloginform');

$shortcode = 'login_form';
/*user login form end*/
/*registration form fields start*/
function crlregistrationformfields() {

	

	ob_start(); ?>	

		<h3 class="custom_header"><?php _e('Register New Account'); ?></h3>

		

		<?php 

		// show any error messages after form submission

		crlshowerrormessages(); ?>

		

		<form id="crlregistrationform" class="custom_form" action="" method="POST">

			<fieldset>

				<p>

					<label for="custom_user_Login"><?php _e('Username'); ?></label>

					<input name="custom_user_login" id="custom_user_login" class="required" type="text"/>

				</p>

				<p>

					<label for="custom_user_email"><?php _e('Email'); ?></label>

					<input name="custom_user_email" id="custom_user_email" class="required" type="email"/>

				</p>

				<p>

					<label for="custom_user_first"><?php _e('First Name'); ?></label>

					<input name="custom_user_first" id="custom_user_first" type="text"/>

				</p>

				<p>

					<label for="custom_user_last"><?php _e('Last Name'); ?></label>

					<input name="custom_user_last" id="custom_user_last" type="text"/>

				</p>

				<p>

					<label for="password"><?php _e('Password'); ?></label>

					<input name="custom_user_pass" id="password" class="required" type="password"/>

				</p>

				<p>

					<label for="password_again"><?php _e('Confirm Password'); ?></label>

					<input name="custom_user_pass_confirm" id="password_again" class="required" type="password"/>

				</p>

				<p>

					<input type="hidden" name="custom_register_nonce" value="<?php echo wp_create_nonce('custom-register-nonce'); ?>"/>

					<input type="submit" id="custom_registration_submit" value="<?php _e('Register Your Account'); ?>"/>

				</p>

			</fieldset>

		</form>

	<?php

	return ob_get_clean();



}
/*registration form fields end*/
/*login form fields start*/

function crlloginformfields() {

		

	ob_start(); ?>

		<h3 class="custom_header"><?php _e('Login'); ?></h3>

		

		<?php

		// show any error messages after form submission

		crlshowerrormessages(); ?>


		<form id="crlloginform"  class="custom_form"action="" method="post">

			<fieldset>

				<p>

					<label for="custom_user_Login">Username</label>

					<input name="custom_user_login" id="custom_user_login" class="required" type="text"/>

				</p>

				<p>

					<label for="custom_user_pass">Password</label>

					<input name="custom_user_pass" id="custom_user_pass" class="required" type="password"/>

				</p>

				<p>

					<input type="hidden" name="custom_login_nonce" value="<?php echo wp_create_nonce('custom-login-nonce'); ?>"/>

					<input id="custom_login_submit" type="submit" value="Login"/>

				</p>

				<p class="textcenter"><a href="<?php echo get_home_url();  ?>/forgot-password/">Forgot Password</a></p>

			</fieldset>

		</form>

	<?php

	return ob_get_clean();

}
/*login form fields end*/
/*logs a member in after submitting a form*/

function crloginmember() {
$custom_user_login = sanitize_user($_POST['custom_user_login']);
$custom_login_nonce = sanitize_user($_POST['custom_login_nonce']);
$custom_user_pass = sanitize_user($_POST['custom_user_pass']);
	if(isset($custom_user_login) && isset($custom_login_nonce) &&wp_verify_nonce($custom_login_nonce, 'custom-login-nonce')) {

				

		// this returns the user ID and other info from the user name

		$user = get_userdatabylogin($custom_user_login);

		

		if(!$user) {

			// if the user name doesn't exist

			crlerrors()->add('empty_username', __('Invalid username'));

		}

		

		if(!isset($custom_user_pass) || $custom_user_pass == '') {

			// if no password was entered

			crlerrors()->add('empty_password', __('Please enter a password'));

		}

				

		// check the user's login with their password

		if(!wp_check_password($custom_user_pass, $user->user_pass, $user->ID)) {

			// if the password is incorrect for the specified user

			crlerrors()->add('empty_password', __('Incorrect password'));

		}

		

		// retrieve all error messages

		$errors = crlerrors()->get_error_messages();

		

		// only log the user in if there are no errors

		if(empty($errors)) {

			

			wp_setcookie($custom_user_login, $custom_user_pass, true);

			wp_set_current_user($user->ID, $custom_user_login);	

			do_action('wp_login', $custom_user_login);

			

		//	wp_redirect(home_url());

		

			wp_redirect( get_home_url().'/profile', 301 ); 

		//}

		exit;

		}

	}

}

add_action('init', 'crloginmember');

function crladdnewmember() {
$crl_user_login = sanitize_user($_POST["custom_user_login"]);
$custom_register_nonce = sanitize_text_field($_POST['custom_register_nonce']);
  	if (isset( $crl_user_login ) && wp_verify_nonce($custom_register_nonce, 'custom-register-nonce')) {

		$user_login		= sanitize_user($_POST["custom_user_login"]);	

		$user_email		= sanitize_email($_POST["custom_user_email"]);

		$user_first 	= sanitize_user($_POST["custom_user_first"]);

		$user_last	 	= sanitize_user($_POST["custom_user_last"]);

		$user_pass		= sanitize_user($_POST["custom_user_pass"]);

		$pass_confirm 	= sanitize_user($_POST["custom_user_pass_confirm"]);

		

		// this is required for username checks

		require_once(ABSPATH . WPINC . '/registration.php');

		

		if(username_exists($user_login)) {

			// Username already registered

			crlerrors()->add('username_unavailable', __('Username already taken'));

		}

		if(!validate_username($user_login)) {

			// invalid username

			crlerrors()->add('username_invalid', __('Invalid username'));

		}

		if($user_login == '') {

			// empty username

			crlerrors()->add('username_empty', __('Please enter a username'));

		}

		if(!is_email($user_email)) {

			//invalid email

			crlerrors()->add('email_invalid', __('Invalid email'));

		}

		if(email_exists($user_email)) {

			//Email address already registered

			crlerrors()->add('email_used', __('Email already registered'));

		}

		if($user_pass == '') {

			// passwords do not match

			crlerrors()->add('password_empty', __('Please enter a password'));

		}

		if($user_pass != $pass_confirm) {

			// passwords do not match

			crlerrors()->add('password_mismatch', __('Passwords do not match'));

		}

		

		$errors = crlerrors()->get_error_messages();

		

		// only create the user in if there are no errors

		if(empty($errors)) {

			

			$new_user_id = wp_insert_user(array(

					'user_login'		=> $user_login,

					'user_pass'	 		=> $user_pass,

					'user_email'		=> $user_email,

					'first_name'		=> $user_first,

					'last_name'			=> $user_last,

					'user_registered'	=> date('Y-m-d H:i:s'),

					'role'				=> 'subscriber'

				)

			);

			if($new_user_id) {

				// send an email to the admin alerting them of the registration

				wp_new_user_notification($new_user_id);

				// log the new user in

				wp_setcookie($user_login, $user_pass, true);

				wp_set_current_user($new_user_id, $user_login);	
			 	$user = get_userdatabylogin($user_login);
				do_action('wp_login', $user_login,$user);

				//echo "hello";exit();

				// send the newly created user to the home page after logging them in

				wp_redirect(home_url()); exit;

			}

			

		}

	

	}

}

add_action('init', 'crladdnewmember');

/*used for tracking error messages*/

function crlerrors(){

	    static $wp_error; // Will hold global variable safely

	    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));

	}

	// displays error messages from form submissions

function crlshowerrormessages() {

	if($codes = crlerrors()->get_error_codes()) {

		echo '<div class="crlerrors">';

	    // Loop error codes and display errors

	   	foreach($codes as $code){

	        $message = crlerrors()->get_error_message($code);

	        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';

	    }

			echo '</div>';

	}	

}



if ( ! defined('ABSPATH')) exit;  // if direct access 

/*restrict profile page*/
function crl_non_logged_redirect() {

	global $current_user; 

	get_currentuserinfo(); 

	
	if (!(is_user_logged_in()) && is_page('profile')){

			wp_redirect( home_url() );

			exit;

	}
		
	if (!(is_user_logged_in()) && is_page('edit-profile')){

		wp_redirect( home_url() );

		exit;
	}

	}

add_action('template_redirect','crl_non_logged_redirect'); 

/*profile shortcode*/

function crl_profile_shortcode() {

		ob_start();

	// get_template_part('password_reset');

		include 'custom_profile.php'; 

	// get_template_part( 'password_reset' );

	// do_shortcode('[Custom WordPress Password Reset]'); 

		return ob_get_clean();   

} 

add_shortcode( 'my_profile', 'crl_profile_shortcode' );

	$shortcode = 'my_profile';

/*edit prodile shortcode*/

function crl_profile_edit_shortcode() {

		 ob_start();

		include 'custom_edit_profile.php'; 

		return ob_get_clean();   

} 

add_shortcode( 'my_profile_edit', 'crl_profile_edit_shortcode' );

/*forget password*/

function crl_change_pass_shortcode() {

		ob_start();

		include 'change_pass.php'; 

		return ob_get_clean();   

} 

	add_shortcode( 'reset_password', 'crl_change_pass_shortcode' );



/*Add custom page*/

/*For Registration*/

function add_crl_registration_page() {

// Create post object

		 $my_post = array(

      'post_title'    => wp_strip_all_tags( 'Registration' ),

      'post_content'  => '[register_form]',

      'post_status'   => 'publish',

      'post_author'   => 1,

      'post_type'     => 'page',

			);



// Insert the post into the database

	wp_insert_post( $my_post );

}

register_activation_hook(__FILE__, 'add_crl_registration_page');

/*For Login*/

function add_crl_login_page() {

    // Create post object

    $my_post = array(

      'post_title'    => wp_strip_all_tags( 'Login' ),

      'post_content'  => '[login_form]',

      'post_status'   => 'publish',

      'post_author'   => 1,

      'post_type'     => 'page',

    );



    // Insert the post into the database

    wp_insert_post( $my_post );

}
register_activation_hook(__FILE__, 'add_crl_login_page');

/*For Profile*/

function add_crl_profile_page() {

    // Create post object

    $my_post = array(

      'post_title'    => wp_strip_all_tags( 'Profile' ),

      'post_content'  => '[my_profile]',

      'post_status'   => 'publish',

      'post_author'   => 1,

      'post_type'     => 'page',

    );



    // Insert the post into the database

    wp_insert_post( $my_post );

}



register_activation_hook(__FILE__, 'add_crl_profile_page');

/*For Edit Profile*/

function add_crl_edit_profile_page() {

    // Create post object

    $my_post = array(

      'post_title'    => wp_strip_all_tags( 'Edit Profile' ),

      'post_content'  => '[my_profile_edit]',

      'post_status'   => 'publish',

      'post_author'   => 1,

      'post_type'     => 'page',

    );



    // Insert the post into the database

    wp_insert_post( $my_post );

}



register_activation_hook(__FILE__, 'add_crl_edit_profile_page');

/*For Forgot passwoed*/

function add_crl_change_password() {

    // Create post object

    $my_post = array(

      'post_title'    => wp_strip_all_tags( 'Forgot Password' ),

      'post_content'  => '[reset_password]',

      'post_status'   => 'publish',

      'post_author'   => 1,

      'post_type'     => 'page',

    );



    // Insert the post into the database

    wp_insert_post( $my_post );

}



register_activation_hook(__FILE__, 'add_crl_change_password');