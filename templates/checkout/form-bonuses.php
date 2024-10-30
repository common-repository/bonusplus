<?php
/**
 * Bonuses form in checkout
 *
 * @package Onepix\BonusPlus
 */

?>

<h4 class="bonusPlusBonuses__title">Списать бонусы</h4>

<div class="bonusPlusBonuses js-bonusplus-bonuses" style="display: none">
	<span class="bonusPlusBonuses__status js-bonusplus-status"></span>
	<span class="bonusPlusBonuses__status js-bonusplus-status-input"></span>
	<div class="bonusPlusBonuses__inputs bonusPlusBonuses__inputs--bonuses js-bonusplus-bonuses-inputs">
		<label for="bonusplus-bonuses-amount">Количество бонусов:</label>
		<input class="bonusPlusBonuses__input"
			   id="bonusplus-bonuses-amount"
			   type="number"
			   min="0"
			   max="999"
			   step="1"
			   name="bonus_plus_bonuses_amount">
		<button class="bonusPlusBonuses__button js-bonusplus-submit">Списать</button>
	</div>
	<div class="bonusPlusBonuses__inputs bonusPlusBonuses__inputs--code js-bonusplus-sms-inputs">
		<label for="bonusplus-bonuses-sms">Код из смс:</label>
		<input class="bonusPlusBonuses__input"
			   id="bonusplus-bonuses-sms"
			   type="text"
			   name="bonus_plus_code">
		<a href="#" class="bonusPlusBonuses__resend-code-link js-bonusplus-resend-sms">Отправить код повторно</a>
		<button class="bonusPlusBonuses__button js-bonusplus-check-sms">Подтвердить</button>
	</div>
</div>
