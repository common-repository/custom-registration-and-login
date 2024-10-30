<?php
$user = wp_get_current_user();
$user_id= get_current_user_id(); ?> 

<style type="text/css">
.change {
    text-align: right;
}
</style>
     
    <h3><b>Name :- </b><?php echo $user->display_name; ?></h3>
    <h3><b>Email :- </b><?php echo $user->user_email; ?></h3>
    <h3><b>UserName :- </b><?php echo $user->user_login; ?></h3>
    <h3><b>Phone Number :- </b><?php echo $user->phone_number; ?></h3>
<div class="change"> 
	<div class="container">
  		<a href="edit-profile/" class="btn btn-info" role="button">Edit Profile</a>
	</div>
</div>

        