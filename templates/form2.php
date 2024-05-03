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
		<p><?= $params['payment']['expired_at'] ?></p>
	</div>
	<div>
		<p>Address</p>
		<p><?= $params['payment']['address'] ?></p>
	</div>

	<img src="<?= $params['payment']['address_qr_code'] ?>" />
	<input type="button" value="check status" id="checkStatusButton"/>
<!-- 	<pre>
		<?= $params['network'] ?>
		<?= $params['to_currency'] ?>
		<?= "\n" ?>
		<?= print_r($params['payment'], true) ?>
	</pre>
 -->
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('checkStatusButton').addEventListener('click', function() {
        var orderId = document.getElementById('orderIdInput').value;

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
    });
});
</script>
<? get_footer(); ?>
