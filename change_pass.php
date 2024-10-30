<?php
/*
Template Name: Custom WordPress Password Reset
*/
?>
<?php
global $wpdb, $user_ID;
wp_enqueue_script( 'jquery' );
 
if (!$user_ID) { //block logged in users
   //Validation stuffs, Form stuffs, etc
}
else {
//wp_redirect( home_url() ); exit;
  //redirect logged in users to home page
}
?>
<div id="content">
<h1><?php the_title(); ?></h1>
<div id="result"></div> <!-- To hold validation results -->
<form id="wp_pass_reset" action="" method="post">
 
<label>Username or E-mail</label><br />
<input type="text" name="user_input" value="" /><br />
<input type="hidden" name="action" value="tg_pwd_reset" />
<input type="hidden" name="tg_pwd_nonce" value="<?php echo wp_create_nonce("tg_pwd_nonce"); ?>" />
<input type="submit" id="submitbtn" name="submit" value="Reset password" />
 
</form>
<script type="text/javascript">
jQuery("#wp_pass_reset").submit(function() {
	jQuery("#result").html("<span class='loading'>Validating...</span>").fadeIn();
	var input_data = jQuery("#wp_pass_reset").serialize();
	jQuery.ajax({
		type: "POST",
		url:  "'. get_permalink( $post->ID ).'",
		data: input_data,
		success: function(msg){
			jQuery(".loading").remove();
			jQuery("<div>").html(msg).appendTo("div#result").hide().fadeIn("slow");
		}
	});
	return false;
});
</script>
</div>

<?php
function crlvalidateurl() {
global $post;
$page_url = esc_url_raw(get_permalink( $post->ID ));

$urlget = strpos($page_url, "?");
if ($urlget === false) {
	$concate = "?";
} else {
	$concate = "&";
}
return $page_url.$concate;
}
$crl_action = sanitize_text_field($_POST['action']);
$crl_tg_pwd_nonce = sanitize_text_field($_POST['tg_pwd_nonce']);
$crl_user_input = sanitize_text_field($_POST['user_input']);
if($crl_action == "tg_pwd_reset"){
if ( !wp_verify_nonce( $crl_tg_pwd_nonce, "tg_pwd_nonce")) {
  exit("No trick please");
}
if(empty($crl_user_input)) {
	echo'<span class="error">Please enter your Username or E-mail address</span>';
	exit();
}
//We shall SQL escape the input
$user_input = $wpdb->escape(trim($crl_user_input));
 
if ( strpos($user_input, '@') ) {
	$user_data = get_user_by_email($user_input);
	if(empty($user_data) || $user_data->caps[administrator] == 1) {
	//the condition $user_data->caps[administrator] == 1 to prevent password change for admin users.
	//if you prefer to offer password change for admin users also, just delete that condition.
	echo'<span class="error">Invalid E-mail address!</span>';
	exit();
	}
}
else {
	$user_data = get_userdatabylogin($user_input);
	if(empty($user_data) || $user_data->caps[administrator] == 1) {
	//the condition $user_data->caps[administrator] == 1 to prevent password change for admin users.
	//if you prefer to offer password change for admin users also, just delete that condition.
echo'<span class="error">Invalid Username!</span>';
	exit();
	}
}
 
$user_login = $user_data->user_login;
$user_email = $user_data->user_email;
 
$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
if(empty($key)) {
	//generate reset key
	$key = wp_generate_password(20, false);
	$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
}
 
//emailing password change request details to the user
$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
$message .= get_option('siteurl') . "\r\n\r\n";
$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
$message .= crlvalidateurl() . "action=reset_pwd&key=$key&login=" . rawurlencode($user_login) . "\r\n";
if ( $message && !wp_mail($user_email, 'Password Reset Request', $message) ) {
	echo "<div class='error'>Email failed to send for some unknown reason.</div>";
	exit();
}
else echo '<div class="success">We have just sent you an email with Password reset instructions.</div>';
 
}
$crl_key = sanitize_key($_GET['key']);
$crl_action = sanitize_text_field($_GET['action']);
if(isset($crl_key) && $crl_action == "reset_pwd") {
$reset_key = $crl_key;
$user_login = sanitize_text_field($_GET['login']);
$user_data = $wpdb->get_row($wpdb->prepare("SELECT ID, user_login, user_email FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $reset_key, $user_login));
$user_login = $user_data->user_login;
$user_email = $user_data->user_email;
if(!empty($reset_key) && !empty($user_data)) {
	$new_password = wp_generate_password(7, false); //you can change the number 7 to whatever length needed for the new password
	wp_set_password( $new_password, $user_data->ID );
	//mailing the reset details to the user
	$message = __('Your new password for the account at:') . "\r\n\r\n";
	$message .= get_bloginfo('name') . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('Password: %s'), $new_password) . "\r\n\r\n";
	$message .= __('You can now login with your new password at: ') . get_option('siteurl')."/login" . "\r\n\r\n";
 
	if ( $message && !wp_mail($user_email, 'Password Reset Request', $message) ) {
		echo "<div class='error'>Email failed to sent for some unknown reason</div>";
		exit();
	}
	else {
		$redirect_to = get_bloginfo('url')."/login?action=reset_success";
		wp_safe_redirect($redirect_to);
		exit();
	}
}
else exit('Not a Valid Key.');
 
}
//This goes in to your custom login page template
if(isset($crl_action) && $crl_action == "reset_success") {
echo '<span class="success">You password has been changed. Now you can login with your new password</span>';
}

 ?>