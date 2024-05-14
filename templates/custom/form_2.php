<?php $params = get_query_var('params') ?>
<?php get_header() ?>

<style>
	.cryptomus-flex-column {
		font-family: 'Roboto', sans-serif;
		background-color: #f4f4f4;
		margin: 0;
		padding: 20px;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 900px;
	}

	.cryptomus-payment-form {
		background-color: #fff;
		padding: 20px;
		border-radius: 15px;
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
		width: 550px;
		height: 780px;
		font-size: 14px;
	}

	.cryptomus-form-information p{
		font-weight: 600;
		font-size: 20px;
		margin-top: 20px;
		margin-right: 40px;
		margin-left: 40px;
		margin-bottom: 0px;
		color: black;
	}

	.cryptomus-form-information h1 {
		font-size: 36px;
		margin-right: 40px;
		margin-left: 40px;
	}

	.cryptomus-form-element {
		width: 100%;
		margin-bottom: 20px;
		margin-left: 44px;
	}

	.cryptomus-form-element p {
		font-size: 14px;
		font-weight: 300;
		margin-bottom: 0px;
		color: #666666;
	}

	.cryptomus-form-element span {
		font-size: 16px;
		font-weight: 600;
		color: black;
	}

	.cryptomus-form-element div {
		width: 80px;
		height: 50px;
	}

	.cryptomus-form-address {
		margin-bottom: 40px;
		margin-left: 44px;
	}

	.cryptomus-form-address p {
		font-size: 14px;
		font-weight: 300;
		color: #666666;
		margin-bottom: -2px;
	}

	.cryptomus-form-address span {
		font-size: 16px;
		font-weight: 600;
		color: black;
	}

	.cryptomus-form-qr {
		width: 185px;
		height: 185px;
		border-radius: 25px;
		margin: auto;
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
	}

	.cryptomus-form-button button {
		width: 444px;
		margin-right: 40px;
		margin-left: 40px;
		margin-top: 25px;
		padding: 10px 0;
		background-color: #0A0A0A;
		color: white;
		border: none;
		border-radius: 10px;
		font-size: 16px;
		cursor: pointer;
	}

	.cryptomus-form-button button:hover {
		background-color: #282828;
	}
</style>

<div class="cryptomus-flex-column">
	<input type="hidden" id="orderIdInput" value="<?= $params['payment']['order_id'] ?>" />
	<div class="cryptomus-payment-form">
		<h3>CUSTOM TEMPLATE (REMOVE THIS LINE)</h3>
		<div class="cryptomus-form-information">
			<p> Amount </p>
			<h1><span><?= $params['payment']['payer_amount'] ?> <?= $params['payment']['payer_currency'] ?></span></h1>
		</div>
		<div class="cryptomus-form-element">
			<div style="display: inline-block; margin-right: 180px;">
				<div class="coin">
					<p>Coin</p>
					<p><span><?= $params['payment']['payer_currency'] ?></span></p>
				</div>
				<div class="Status">
					<p>Status</p>
					<p><span id="statusDisplay"><?= $params['payment']['status'] ?></span></p>
				</div>
			</div>

			<div style="display: inline-block; margin-right: 180px;">
				<div class="Network">
					<p>Network</p>
					<p><span><?= $params['payment']['network'] ?></span></p>
				</div>

				<div class="Time">
					<p>Time</p>
					<p><span id="timerDisplay"></span></p>
				</div>
			</div>
		</div>

		<div class="cryptomus-form-address">
			<div>
				<p>Address</p>
				<p><span><?= $params['payment']['address'] ?></span></p>

			</div>
		</div>
		<div class="cryptomus-form-qr">
			<span><img src="<?= $params['payment']['address_qr_code'] ?>" /></span>
		</div>
		<div class="cryptomus-form-button">
			<button type="submit" id="checkStatusButton">Check</button>
			<button type="submit" id="returnToOrder">Return</button>
		</div>
	</div>
</div>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	var orderId = document.getElementById('orderIdInput').value;
	var expiredAtUnix = <?= $params['payment']['expired_at'] ?>;
	var timerDisplay = document.getElementById('timerDisplay');
	var statusDisplay = document.getElementById('statusDisplay');

	function formatTime(seconds) {
		var hours = Math.floor(seconds / 3600);
		var minutes = Math.floor((seconds % 3600) / 60);
		var remainingSeconds = seconds % 60;

		return hours.toString().padStart(2, '0') + ':' +
			   minutes.toString().padStart(2, '0') + ':' +
			   remainingSeconds.toString().padStart(2, '0');
	}

	function updateTimer() {
		var currentTimeUnix = Math.floor(Date.now() / 1000);
		var remainingTime = expiredAtUnix - currentTimeUnix;
		if (remainingTime <= 0) {
			clearInterval(timerInterval);
			timerDisplay.textContent = 'Expired';
		} else {
			timerDisplay.textContent = formatTime(remainingTime);
		}
	}
	var timerInterval = setInterval(updateTimer, 1000);
	updateTimer();

	function checkOrderStatus() {
		fetch('/wp-json/cryptomus-pay/check-status', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ order_id: orderId })
		})
		.then(response => response.json())
		.then(data => {
			if (data.status) {
				statusDisplay.textContent = data.payment_status;
				if (["paid", "paid_over"].indexOf(data.payment_status) >= 0) {
					window.location.href = "<?= $params['success_url'] ?>";
				}
			} else {
				statusDisplay.textContent = 'Error: ' + data.error;
			}
		})
		.catch(error => console.error('Error:', error));
	}

	checkOrderStatus();

	var intervalId = setInterval(checkOrderStatus, 60 * 1000);

	document.getElementById('checkStatusButton').addEventListener('click', function() {
		checkOrderStatus();
	});

	document.getElementById('returnToOrder').addEventListener('click', function() {
		window.location.href = "<?= $params['return_url'] ?>";
	});

	window.addEventListener('beforeunload', function() {
		clearInterval(intervalId);
	});
});

</script>

<?php get_footer() ?>
