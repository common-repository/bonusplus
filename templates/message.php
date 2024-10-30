<?php
/**
 * Message template
 *
 * @package Onepix\BonusPlus
 *
 * @var string $title popup title
 * @var string $message message for client
 * @var string $additional_message additional message for client
 * @var string $image image to show
 * @var string $privacy_policy privacy policy text
 */

?>

<div class="bonusPlusMessage bonusPlusMessage--hidden js-bonusplus-message">
	<div class="bonusPlusMessage__popup">
		<a class="bonusPlusMessage__close js-bonusplus-close" href="#"></a>
		<div class="bonusPlusMessage__image" style="background-image: url(<?php echo esc_url( $image ); ?>)"></div>
		<div class="bonusPlusMessage__content js-bonusplus-content">
			<h3 class="bonusPlusMessage__title"><?php echo wp_kses_post( $title ); ?></h3>
			<label class="bonusPlusMessage__text bonusPlusMessage__text--top bonusPlusMessage__text" for="bonusplus-tel"><?php echo wp_kses_post( $message ); ?></label>

			<p class="bonusPlusMessage__text bonusPlusMessage__text--bottom bonusPlusMessage__text--tiny"><?php echo wp_kses_post( $additional_message ); ?></p>

			<p class="bonusPlusMessage__text bonusPlusMessage__text--bottom bonusPlusMessage__text--small"><?php echo wp_kses_post( $privacy_policy ); ?></p>
		</div>
	</div>
	<div class="bonusPlusMessage__background js-bonusplus-background"></div>
</div>
