<?php $params = get_query_var('params') ?>
<?php get_header() ?>

<?php if ($params['theme'] === 'dark') { ?>
	<!-- dark style -->
	<style>
		.cryptomus-flex-column {
			font-family: 'Roboto', sans-serif;
			background-color: #1c1c1c; /* Темный фон */
			color: #fff; /* Белый текст */
			margin: 0;
			padding: 20px;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		.cryptomus-payment-form {
			background-color: black; /* Цвет формы */
			padding: 20px;
			border-radius: 15px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			width: 524px;
			height: 400px;
			font-size: 14px;
		}

		.cryptomus-form-element {
			margin-bottom: 20px;
		}

		.cryptomus-payment-form label {
			font-weight: 600;
			margin-right: 40px;
			margin-left: 40px;
			margin-bottom: 7px;
			display: block;
		}

		.cryptomus-payment-form p {
			font-weight: 600;
			margin-top: 20px;
			margin-right: 40px;
			margin-left: 40px;
			margin-bottom: -20px;
		}

		.cryptomus-payment-form h1 {
			font-size: 36px;
			margin-right: 40px;
			margin-left: 40px;
		}

		.cryptomus-payment-form select {
			width: 444px;
			margin-right: 40px;
			margin-left: 40px;
			padding: 12px;
			margin-top: 5px;
			border-radius: 10px;
			font-size: 16px;
			background-color: black; /* Цвет выпадающего списка */
			color: #fff; /* Белый текст в выпадающем списке */
		}

		.cryptomus-payment-form button {
			width: 444px;
			margin-right: 40px;
			margin-left: 40px;
			margin-top: 14px;
			padding: 10px 0;
			background-color: white; /* Цвет кнопки */
			color: black;
			font-weight: 500;
			border: none;
			border-radius: 10px;
			font-size: 16px;
			cursor: pointer;
		}

		.cryptomus-payment-form button:hover {
			background-color: #282828; /* Цвет кнопки при наведении */
		}
	</style>

<?php } else { ?>
	<!-- light style -->

	<style>
		.cryptomus-flex-column {
			font-family: 'Roboto', sans-serif;
			background-color: #f4f4f4;
			margin: 0;
			padding: 20px;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		.cryptomus-payment-form {
			background-color: #fff;
			padding: 20px;
			border-radius: 15px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			width: 524px;
			height: 400px;
			font-size: 14px;
		}

		.cryptomus-form-element {
			margin-bottom: 20px;
		}

		.cryptomus-payment-form label {
			font-weight: 600;
			margin-right: 40px;
			margin-left: 40px;
			margin-bottom: 7px;
			display: block;
		}

		.cryptomus-payment-form p {
			font-weight: 600;
			margin-top: 20px;
			margin-right: 40px;
			margin-left: 40px;
			margin-bottom: -20px;
		}

		.cryptomus-payment-form h1 {
			font-size: 36px;
			margin-right: 40px;
			margin-left: 40px;
		}

		.cryptomus-payment-form select {
			width: 444px;
			margin-right: 40px;
			margin-left: 40px;
			padding: 12px;
			margin-top: 5px;
			border-radius: 10px;
			font-size: 16px;
		}

		.cryptomus-payment-form button {
			width: 444px;
			margin-right: 40px;
			margin-left: 40px;
			margin-top: 14px;
			padding: 10px 0;
			background-color: #0A0A0A;
			color: white;
			font-weight: 500;
			border: none;
			border-radius: 10px;
			font-size: 16px;
			cursor: pointer;
		}

		.cryptomus-payment-form button:hover {
			background-color: #282828;
		}
	</style>
<?php } ?>

<div class="cryptomus-flex-column">
	<div class="cryptomus-payment-form">
		<div class="cryptomus-form-element">
			<p> Amount </p>
			<h1><span><?= $params['order_amount'] ?> <?= $params['order_currency'] ?></span></h1>
		</div>
		<form action="/cryptomus-pay/" method="get">
			<input name="order_id" value="<?=$params['order_id']?>" type="hidden" />
			<input name="step_id" value="2" type="hidden" />

			<div class="cryptomus-form-element">
				<label for="to_currency">Coin</label>
				<select id="to_currency" name="to_currency">
					<?php foreach ($params['unique_coins'] as $currency): ?>
						<option value="<?= $currency ?>"><?= $currency ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="cryptomus-form-element">
				<label for="network">Network</label>
				<select id="network" name="network">
				<?php foreach ($params['currencies'] as $currency): ?>
					<option value="<?= $currency['network'] ?>" data-coin="<?= $currency['currency'] ?>"><?= $currency['network'] ?></option>
				<?php endforeach; ?>
		</select>
			</div>
			<div class="cryptomus-form-element">
				<button type="submit">Pay</button>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
	var networkSelect = document.getElementById('network');
	var currencySelect = document.getElementById('to_currency');

	function updateNetworks() {
		var selectedCoin = currencySelect.value;
		var options = networkSelect.options;
		for (var i = 0; i < options.length; i++) {
			var option = options[i];
			var coin = option.getAttribute('data-coin');
			option.style.display = coin === selectedCoin ? 'block' : 'none';
		}
		for (var i = 0; i < options.length; i++) {
			if (options[i].style.display !== 'none') {
				networkSelect.selectedIndex = i;
				break;
			}
		}
	}
	currencySelect.addEventListener('change', updateNetworks);
	updateNetworks();
});

</script>
<?php get_footer() ?>
