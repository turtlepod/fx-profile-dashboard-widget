<?php
/**
 * f(x) Profile Dashboard Widget
 * Edit Profile Email and Password using Dashboard Widget
 * @version 0.1.0
 */
class fx_Profile_Dashboard_Widget{

	/**
	 * Settings Page Slug
	 * @since 0.1.0
	 */
	public $capability = 'edit_dashboard';

	/**
	 * Class Constructor
	 * @since 0.1.0
	 */
	public function __construct() {

		/* User script: Password Strength */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		/* Dashboard Setup */
		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
	}

	/**
	 * Enqueue User Profile JS, including password strength meter.
	 * @since 0.1.2
	 */
	public function admin_enqueue_scripts(){

		/* Get current page context */
		global $pagenow;

		/* Get current user data  */
		if( function_exists( 'wp_get_current_user' ) ){
			$current_user = wp_get_current_user();
		}
		else{
			global $current_user;
			get_currentuserinfo();
		}

		/* Only load script when editing this dashboard widget & check user caps */
		if( 'index.php' == $pagenow ){

			/* Add CSS */
			wp_enqueue_style( 'fx-profile-dashboard-widget', FX_PDW_URI . 'css/admin.css', array(), FX_PDW_VERSION );

			/* Only when editing tghe widget */
			if( isset( $_GET['edit'] ) && 'fx_profile_dashboard_widget' == $_GET['edit'] && current_user_can( $this->capability ) && current_user_can( 'edit_user', $current_user->ID ) ){

				/* Add WP user profile scripts (password strength, etc) */
				//wp_enqueue_style( 'user-profile' );
				wp_enqueue_script( 'user-profile' );
			}

		}
	}

	/**
	 * Setup
	 * @since 0.1.0
	 */
	public function setup(){

		/* Get current user data */
		if( function_exists( 'wp_get_current_user' ) ){
			$current_user = wp_get_current_user();
		}
		else{
			global $current_user;
			get_currentuserinfo();
		}

		/* Check capability */
		if( current_user_can( $this->capability ) && current_user_can( 'edit_user', $current_user->ID ) ){

			/* Add dashboard widget */
			wp_add_dashboard_widget( 'fx_profile_dashboard_widget', __( 'Your Account', 'fx-profile-dashboard-widget' ), array( $this, 'dashboard_widget_callback' ), array( $this, 'dashboard_widget_control_callback' ) );
		}
	}

	/**
	 * Dashboard Widget Callback
	 * @since 0.1.0
	 */
	public function dashboard_widget_callback(){

		/* Edit Widget URL */
		$edit_url = 'index.php?edit=fx_profile_dashboard_widget#fx_profile_dashboard_widget';

		/* Get current user data  */
		if( function_exists( 'wp_get_current_user' ) ){
			$current_user = wp_get_current_user();
		}
		else{
			global $current_user;
			get_currentuserinfo();
		}

		/* Get notice */
		$notice = get_transient( 'fx_profile_dashboard_widget_notice' );

		/* Check transient */
		if ( $notice ){

			/* Display notice */
			if ( isset( $notice['email'] ) && !empty( $notice['email'] ) ){
				echo '<div class="fx-pdw-notice"><p>' . $notice['email'] . '</p></div>';
			}
			if ( isset( $notice['name'] ) && !empty( $notice['name'] ) ){
				echo '<div class="fx-pdw-notice"><p>' . $notice['name'] . '</p></div>';
			}
			if ( isset( $notice['pass'] ) && !empty( $notice['pass'] ) ){
				echo '<div class="fx-pdw-notice"><p>' . $notice['pass'] . '</p></div>';
			}

			/* Delete notice */
			delete_transient( 'fx_profile_dashboard_widget_notice' ); 
		}

		/* User Info */ ?>
		<p><strong><?php _e( 'Login Name:', 'fx-profile-dashboard-widget' ); ?></strong><br />
		<?php echo $current_user->user_login ?></p>

		<p><strong><?php _e( 'Name:', 'fx-profile-dashboard-widget' ); ?></strong><br />
		<?php echo $current_user->display_name ?></p>

		<p><strong><?php _e( 'Email:', 'fx-profile-dashboard-widget' ); ?></strong><br />
		<?php echo $current_user->user_email ?></p>

		<p><a href="<?php echo $edit_url; ?>" class="button-primary"><?php _e( 'Edit Account', 'fx-profile-dashboard-widget' ); ?></a></p>

	<?php }


	/**
	 * Dashboard Widget Control Callback
	 * @since 0.1.0
	 */
	public function dashboard_widget_control_callback(){

		/* Get current user data  */
		if( function_exists( 'wp_get_current_user' ) ){
			$current_user = wp_get_current_user();
		}
		else{
			global $current_user;
			get_currentuserinfo();
		}

		/* Notice */
		$email_notice = '';
		$password_notice = '';

		/* Submit Data, Check Nonce, Check Capability, Save */
		if ( isset( $_POST['fx_profile_dashboard_widget'] ) && isset( $_POST['dashboard-widget-nonce'] ) && wp_verify_nonce( $_POST['dashboard-widget-nonce'], 'edit-dashboard-widget_fx_profile_dashboard_widget' ) && current_user_can( 'edit_user', $current_user->ID ) ) {

			/* Data submitted */
			$submit_data = $_POST['fx_profile_dashboard_widget'];
			$password1_edit = $_POST['pass1'];
			$password2_edit = $_POST['pass2'];

			/* Update user email */
			if ( !empty( $submit_data['email'] ) ){

				/* If it's not email, add notice about it */
				if ( !is_email( esc_attr( $submit_data['email'] ) ) ){
					$email_notice = '<strong>' . __('Your email input is not valid. Please try again.', 'fx-profile-dashboard-widget') . '</strong>';
				}

				/* If it's the same email, do nothing, no notice */
				elseif( $submit_data['email'] == $current_user->user_email ){
					$email_notice = '';
				}

				/* If another user already using the same email, add notice about it */
				elseif( email_exists( $submit_data['email'] ) ){
					$email_notice = '<strong>' . __( 'Your email input already used by another user. Please try again.', 'fx-profile-dashboard-widget' ) . '</strong>';
				}

				/* Save email, and add notice */
				else{
					$email_notice = '<strong>' . __( 'Your email is updated.', 'fx-profile-dashboard-widget' ) . ' </strong>';
					wp_update_user( array ('ID' => $current_user->ID, 'user_email' => esc_attr( $submit_data['email'] ) ) );
				}
			}

			/* Update user nickname */
			if ( !empty( $submit_data['name'] ) ){

				/* If it's the same name, do nothing, no notice */
				if( $submit_data['name'] != $current_user->display_name ){
					$name_notice = '<strong>' . __( 'Your name is updated.', 'fx-profile-dashboard-widget' ) . ' </strong>';
					wp_update_user( array ('ID' => $current_user->ID, 'display_name' => esc_attr( $submit_data['name'] ) ) );
					wp_update_user( array ('ID' => $current_user->ID, 'nickname' => esc_attr( $submit_data['name'] ) ) );
					wp_update_user( array ('ID' => $current_user->ID, 'first_name' => esc_attr( $submit_data['name'] ) ) );
				}
			}

			/* Update user password, only if the field is not empty */
			if ( !empty( $password1_edit ) && !empty( $password2_edit ) ) {

				/* Check repeat new password field, if it's the same, continue */
				if ( $password1_edit == $password2_edit ){

					$password_notice = '<strong>' . __( 'Your Password is updated.', 'fx-profile-dashboard-widget') . '</strong>';
					wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $password1_edit ) ) );
				}
				/* If repeat new password, didn't match, add notice */
				else{
					$password_notice = '<strong>' . __( 'Your password input do not match. Please try again.', 'fx-profile-dashboard-widget' ) . '</strong>';
				}
			}

			/* Notice using transient, expire in 5 second */
			$notice = array(
				'email' => $email_notice,
				'name' => $name_notice,
				'pass' => $password_notice
			);
			set_transient( 'fx_profile_dashboard_widget_notice', $notice, 5 );
		}

		/* Display the form input */ ?>

		<p><label for="fx-profile-dashboard-widget-login-name"><strong><?php _e( 'Login Name:', 'fx-profile-dashboard-widget' ); ?></strong></label>
		<input id="fx-profile-dashboard-widget-login-name" autocomplete="off" class="widefat" readonly="readonly" type="text" value="<?php echo $current_user->user_login; ?>"/></p>

		<p><label for="fx-profile-dashboard-widget-name"><strong><?php _e( 'Edit Name:', 'fx-profile-dashboard-widget' ); ?></strong></label>
		<input id="fx-profile-dashboard-widget-name" autocomplete="off" class="widefat" name="fx_profile_dashboard_widget[name]" type="text" value="<?php the_author_meta( 'display_name', $current_user->ID ); ?>"/></p>

		<p><label for="fx-profile-dashboard-widget-email"><strong><?php _e( 'Edit Email:', 'fx-profile-dashboard-widget' ); ?></strong></label>
		<input id="fx-profile-dashboard-widget-email" autocomplete="off" class="widefat" name="fx_profile_dashboard_widget[email]" type="text" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>"/></p>

		<?php /* ============================================== */ ?>

		<table class="form-table">
		<tr id="password" class="user-pass1-wrap">
			<th><label for="pass1"><?php _e( 'New Password:', 'fx-profile-dashboard-widget' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php _e( 'Generate Password' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
						<span class="dashicons dashicons-hidden"></span>
						<span class="text"><?php _e( 'Hide' ); ?></span>
					</button>
					<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
						<span class="text"><?php _e( 'Cancel' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php _e( 'Repeat New Password' ); ?></label></th>
			<td>
			<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" />
			<p class="description"><?php _e( 'Type your new password again.' ); ?></p>
			</td>
		</tr>
		<tr class="pw-weak">
			<th><?php _e( 'Confirm Password' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<?php _e( 'Confirm use of weak password' ); ?>
				</label>
			</td>
		</tr>
		</table>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$( "#fx_profile_dashboard_widget #submit" ).attr( 'value', 'Update Profile' );
			});
		</script>

	<?php }

}