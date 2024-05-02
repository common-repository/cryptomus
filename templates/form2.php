<? $params = get_query_var('params') ?>
<? get_header(); ?>

<div style="display:block; width: 500px; margin: auto; text-align: center;">
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
<!-- 	<pre>
		<?= $params['network'] ?>
		<?= $params['to_currency'] ?>
		<?= "\n" ?>
		<?= print_r($params['payment'], true) ?>
	</pre>
 -->
</div>

<? get_footer(); ?>
