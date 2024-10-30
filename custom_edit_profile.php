<?php

// Add post state to the projects page

add_filter( 'display_post_states', 'crl_add_post_state', 10, 2 );

function crl_add_post_state( $post_states, $post ) {

	if( $post->post_name == 'edit-profile' ) {
		$post_states[] = 'Profile edit page';
	}

	return $post_states;
}
// Add notice to the profile edit page

add_action( 'admin_notices', 'crl_add_post_notice' );

function crl_add_post_notice() {

	global $post;

	if( isset( $post->post_name ) && ( $post->post_name == 'edit-profile' ) ) {
	  /* Add a notice to the edit page */
		add_action( 'edit_form_after_title', 'crl_add_page_notice', 1 );
		/* Remove the WYSIWYG editor */
		remove_post_type_support( 'page', 'editor' );
	}	
}

function crl_add_page_notice() {
	echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the profile edit page. Do not edit the title or slug of this page!', 'textdomain' ) . '</p></div>';
}
?>
<?php
	//check_page_security();
	//require_once('includes/update-profile.php');
?>

<?php get_template_part('parts/dashboard/user'); ?>

	<?php //if ( !have_posts() ) get_template_part( 'parts/notice/no-posts' ); ?>

	<?php //while (have_posts()) : the_post(); ?>

		<section id="dashboard-content">
		
			<div class="wrap">
<?php $crl_updated = sanitize_text_field($_GET['updated']);
$crl_validation = sanitize_text_field($_GET['validation']); ?>
				<?php get_template_part( 'parts/dashboard/edit-profile/intro' ); ?>

				<?php if( !empty( $crl_updated ) ): ?>
					<div class="success"><?php _e('Profile successfully updated', 'textdomain'); ?></div>
				<?php endif; ?>

				<?php if( !empty( $crl_validation ) ): ?>
					
					<?php if( $crl_validation == 'emailnotvalid' ): ?>
						<div class="error"><?php _e('The given email address is not valid', 'textdomain'); ?></div>
					<?php elseif( $crl_validation == 'emailexists' ): ?>
						<div class="error"><?php _e('The given email address already exists', 'textdomain'); ?></div>
					<?php elseif( $crl_validation == 'passwordmismatch' ): ?>
						<div class="error"><?php _e('The given passwords did not match', 'textdomain'); ?></div>
					<?php elseif( $crl_validation == 'unknown' ): ?>
						<div class="error"><?php _e('An unknown error accurd, please try again or contant the website administrator', 'textdomain'); ?></div>
					<?php endif; ?>

				<?php endif; ?>

				<?php $current_user = wp_get_current_user(); ?>

				<form method="post" id="adduser" action="<?php the_permalink(); ?>">

				    <h3><?php _e('Personal info', 'textdomain'); ?></h3>

				    <p>
				        <label for="first-name"><?php _e('Username', 'textdomain'); ?></label>
				        <input class="text-input" name="user_login" type="text" id="user_login" value="<?php the_author_meta( 'user_login', $current_user->ID ); ?>" disabled/>
				        <?php _e('It is not possible to change your username.', 'textdomain'); ?>
				    </p>
				    
				    <p>
				        <label for="display_name"><?php _e('Name', 'textdomain'); ?></label>
				        <input class="text-input" name="display_name" type="text" id="display_name" value="<?php the_author_meta( 'display_name', $current_user->ID ); ?>" />
				    </p>

				    <p>
				        <label for="email"><?php _e('E-mail *', 'textdomain'); ?></label>
				        <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
				    </p>

				    <p>
				        <label for="phone_number"><?php _e('Phone Number', 'textdomain'); ?></label>
				        <input class="text-input" name="phone_number" type="text" id="phone_number" value="<?php the_author_meta( 'phone_number', $current_user->ID ); ?>" pattern="[0123456789][0-9]{9}"/>
				    </p>

				    <?php 
				        // action hook for plugin and extra fields
				        do_action('edit_user_profile', $current_user); 
				    ?>

				    <h4><?php _e('Change your current password', 'textdomain'); ?></h4>

				    <p><?php _e('When both password fields are left empty, your password will not change', 'textdomain'); ?></p>

				    <p class="form-password">
				        <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
				        <input class="text-input" name="pass1" type="password" id="pass1" />
				    </p><!-- .form-password -->
				    <p class="form-password">
				        <label for="pass2"><?php _e('Repeat password *', 'profile'); ?></label>
				        <input class="text-input" name="pass2" type="password" id="pass2" />
				    </p><!-- .form-password -->

				    <p class="form-submit">
				        <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update profile', 'textdomain'); ?>" />
				        <?php wp_nonce_field( 'update-user' ) ?>
				        <input name="honey-name" value="" type="text" style="display:none;" />
				        <input name="action" type="hidden" id="action" value="update-user" />
				    </p>
				</form>
			</div>
</section>
	<?php //endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php
	/* Recheck if user is logged in just to be sure, this should have been done already */
if( !is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}
$action = sanitize_text_field($_POST['action']);
$crl_email = sanitize_email($_POST['email']);
$crl_pass1 = sanitize_text_field($_POST['pass1']);
$crl_pass2 = sanitize_text_field($_POST['pass2']);
$crl_display_name = sanitize_text_field($_POST['display_name']);
$crl_phone_number = sanitize_text_field($_POST['phone_number']);
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $action ) && $action == 'update-user' ) {

	$current_user = wp_get_current_user();

	/* Update profile fields */

	if ( !empty( $crl_email ) ){

		$posted_email = sanitize_email( $crl_email );

        if ( !is_email( $posted_email ) ) {
        	wp_redirect( get_permalink() . '?validation=emailnotvalid' );
			exit;
        } elseif( email_exists( $posted_email ) && ( email_exists( $posted_email ) != $current_user->ID ) ) {
        	wp_redirect( get_permalink() . '?validation=emailexists' );
			exit;
        } else{
            wp_update_user( array ('ID' => $current_user->ID, 'user_email' => $posted_email ) );
        }
    }

    if ( !empty($crl_pass1) || !empty( $crl_pass2 ) ) {

        if ( $crl_pass1 == $crl_pass2 ) {
            wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => sanitize_text_field( $crl_pass1 ) ) );
        }
        else {
        	wp_redirect( get_permalink() . '?validation=passwordmismatch' );
			exit;
        }
            
    }

    if ( !empty( $crl_display_name ) ) {
    	$display_name .= ' ' . sanitize_text_field( $_POST['display_name'] );
        $fullname = sanitize_text_field($_POST['display_name']);
        update_user_meta( $current_user->ID, 'display_name', $fullname );
    }

    if ( $display_name ) {
     	wp_update_user( array ('ID' => $current_user->ID, 'display_name' => sanitize_text_field( $display_name ) ) );
    }

    if ( !empty( $crl_phone_number ) ) {
        $number = sanitize_text_field($_POST['phone_number']);
        update_user_meta( $current_user->ID, 'phone_number', $number );

    }


    /* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
    do_action('edit_user_profile_update', $current_user->ID);

    /* We got here, assuming everything went OK */
   	$red = get_site_url() . '/profile?updated=true';
	echo "<script>window.location.href = '".$red."'</script>";
	// Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot /
}
?>
