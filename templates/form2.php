<? $params = get_query_var('params') ?>
<? get_header(); ?>

<div style="display:block; width: 500px; margin: auto; text-align: center;" class="theme-<?= $params['theme'] ?>">
	<input type="hidden" id="orderIdInput" value="<?= $params['payment']['order_id'] ?>" />
	<h1>Cryptomus Payment Gateway</h1>
	<div>
		<p>Amount:</p>
		<p><?= $params['payment']['payer_amount'] ?> <?= $params['payment']['payer_currency'] ?></p>
	</div>
	<div>
		<p>Coin</p>
		<p><?= $params['payment']['payer_currency'] ?></p>
	</div>
	<div>
		<p>Network</p>
		<p><?= $params['payment']['network'] ?></p>
	</div>
	<div>
		<p>Status</p>
		<p><?= $params['payment']['status'] ?></p>
	</div>
	<div>
		<p>Invoice expiring at</p>
		<p id="timerDisplay"></p>
	</div>
	<div>
		<p>Address</p>
		<p><?= $params['payment']['address'] ?></p>
	</div>

	<img src="<?= $params['payment']['address_qr_code'] ?>" />
	<br/>
	<input type="button" value="Check Status Manually" id="checkStatusButton"/>
	<br/><br/>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	var orderId = document.getElementById('orderIdInput').value;
	var expiredAtUnix = <?= $params['payment']['expired_at'] ?>; // Получаем время в формате Unix из PHP
	var timerDisplay = document.getElementById('timerDisplay');

	function formatTime(seconds) {
		var hours = Math.floor(seconds / 3600);
		var minutes = Math.floor((seconds % 3600) / 60);
		var remainingSeconds = seconds % 60;

		return hours.toString().padStart(2, '0') + ':' +
			   minutes.toString().padStart(2, '0') + ':' +
			   remainingSeconds.toString().padStart(2, '0');
	}

	function updateTimer() {
		var currentTimeUnix = Math.floor(Date.now() / 1000); // Получаем текущее время в формате Unix
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
				alert('Status of the order: ' + data.status);
			} else {
				alert('Error: ' + data.error);
			}
		})
		.catch(error => console.error('Error:', error));
	}

	checkOrderStatus();

	var intervalId = setInterval(checkOrderStatus, 60 * 1000);

	document.getElementById('checkStatusButton').addEventListener('click', function() {
		checkOrderStatus();
	});

	window.addEventListener('beforeunload', function() {
		clearInterval(intervalId);
	});
});

</script>
<? get_footer(); ?>
