<?php
/**
 * Customer login and registration.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$register_active      = $registration_enabled && isset( $_POST['register'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
$login_username       = isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$register_username    = isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$register_email       = isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
?>

<section class="blue-account-auth" aria-labelledby="blue-account-auth-title">
	<?php if ( $registration_enabled ) : ?>
		<div class="blue-auth-tabs" role="tablist" aria-label="<?php echo esc_attr( blue_text( 'Account access', 'الدخول إلى الحساب' ) ); ?>">
			<button class="blue-auth-tab<?php echo $register_active ? '' : ' is-active'; ?>" type="button" role="tab" id="blue-login-tab" aria-controls="blue-login-panel" aria-selected="<?php echo $register_active ? 'false' : 'true'; ?>" data-account-tab="login">
				<?php echo esc_html( blue_text( 'Sign in', 'تسجيل الدخول' ) ); ?>
			</button>
			<button class="blue-auth-tab<?php echo $register_active ? ' is-active' : ''; ?>" type="button" role="tab" id="blue-register-tab" aria-controls="blue-register-panel" aria-selected="<?php echo $register_active ? 'true' : 'false'; ?>" data-account-tab="register">
				<?php echo esc_html( blue_text( 'Sign up', 'إنشاء حساب' ) ); ?>
			</button>
		</div>
	<?php endif; ?>

	<div class="blue-auth-panel" id="blue-login-panel" role="tabpanel" aria-labelledby="blue-login-tab"<?php echo $register_active ? ' hidden' : ''; ?>>
		<header class="blue-auth-head">
			<span class="blue-auth-mark" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 14.2A8 8 0 1 1 9.8 4 6.4 6.4 0 0 0 20 14.2z"/></svg>
			</span>
			<h2 id="blue-account-auth-title"><?php echo esc_html( blue_text( 'Welcome back', 'مرحباً بعودتك' ) ); ?></h2>
			<p><?php echo esc_html( blue_text( 'Sign in to track your orders and manage your Blue account.', 'سجّل الدخول لمتابعة طلباتك وإدارة حساب بلو الخاص بك.' ) ); ?></p>
		</header>

		<form class="woocommerce-form woocommerce-form-login login blue-auth-form" method="post" novalidate>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php echo esc_html( blue_text( 'Email address or username', 'البريد الإلكتروني أو اسم المستخدم' ) ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo esc_attr( $login_username ); ?>" required aria-required="true">
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true">
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="blue-auth-options">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
					<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php echo esc_html( blue_text( 'Forgot password?', 'نسيت كلمة المرور؟' ) ); ?></a>
			</div>

			<p class="form-row blue-auth-submit-row">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit blue-auth-submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
					<?php echo esc_html( blue_text( 'Sign in', 'تسجيل الدخول' ) ); ?>
				</button>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</div>

	<?php if ( $registration_enabled ) : ?>
		<div class="blue-auth-panel" id="blue-register-panel" role="tabpanel" aria-labelledby="blue-register-tab"<?php echo $register_active ? '' : ' hidden'; ?>>
			<header class="blue-auth-head">
				<span class="blue-auth-mark" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.7-4 3-6 7-6s6.3 2 7 6"/></svg>
				</span>
				<h2><?php echo esc_html( blue_text( 'Create your account', 'أنشئ حسابك' ) ); ?></h2>
				<p><?php echo esc_html( blue_text( 'Save your details and keep every order in one calm place.', 'احفظ بياناتك وتابع جميع طلباتك بسهولة من مكان واحد.' ) ); ?></p>
			</header>

			<form method="post" class="woocommerce-form woocommerce-form-register register blue-auth-form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo esc_attr( $register_username ); ?>" required aria-required="true">
					</p>
				<?php endif; ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo esc_attr( $register_email ); ?>" required aria-required="true">
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true">
					</p>
				<?php else : ?>
					<p class="blue-auth-generated"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<p class="woocommerce-form-row form-row blue-auth-submit-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit blue-auth-submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
						<?php echo esc_html( blue_text( 'Create account', 'إنشاء الحساب' ) ); ?>
					</button>
				</p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		</div>
	<?php endif; ?>

	<p class="blue-auth-note">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2"/><rect x="5" y="10" width="14" height="11" rx="2"/></svg>
		<?php echo esc_html( blue_text( 'Your account is protected by WooCommerce security.', 'حسابك محمي بواسطة نظام أمان ووكومرس.' ) ); ?>
	</p>
</section>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
