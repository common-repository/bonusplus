<?php
/**
 * Settings page template
 *
 * @package Onepix\BonusPlus
 */

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php bonus_plus()->pages( 'settings' )->print_tabs(); ?>

	<form class="settings-form js-settings-form" action="options.php" method="POST">
		<?php
		bonus_plus()->pages( 'settings' )->do_settings_section();
		submit_button();
		?>
	</form>
</div>
