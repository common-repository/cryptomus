<? $params = get_query_var('params') ?>
<!-- <?= print_r($params, true) ?> -->
<? get_header() ?>

<div style="display:block; width: 500px; margin: auto; text-align: center;" class="theme-<?= $params['theme'] ?>">
	<h1>Cryptomus Payment Gateway</h1>
	<div>
		<p><span>Amount: </span><span><?= $params['order_amount'] ?> <?= $params['order_currency'] ?></span></p>
	</div>
	<form action="/cryptomus-pay/" method="get">
			<input name="order_id" value="<?=$params['order_id']?>" type="hidden" />
			<input name="step_id" value="2" type="hidden" 	/>
	    <label for="network">Choose a network:</label>
	    <select name="network" id="network">
	        <? foreach ($params['unique_networks'] as $network): ?>
	            <option value="<?= $network ?>"><?= $network ?></option>
	        <? endforeach; ?>
	    </select>
	    <label for="to_currency">Choose a currency:</label>
	    <select name="to_currency" id="to_currency">
	        <? foreach ($params['currencies'] as $currency): ?>
                <option value="<?= $currency['currency'] ?>" data-network="<?= $currency['network'] ?>"><?= $currency['currency'] ?></option>
	        <? endforeach ?>
	    </select>
	    <br/><br/>
	    <button type="submit">Pay Now</button>
    	<a href="<?= $params['return_url'] ?>">Return to order</a>
	</form>
	<br/><br/>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    var networkSelect = document.getElementById('network');
    var currencySelect = document.getElementById('to_currency');
    function updateCurrencies() {
        var selectedNetwork = networkSelect.value;
        var options = currencySelect.options;
        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var currencyNetwork = option.dataset.network;
            option.style.display = currencyNetwork === selectedNetwork ? 'block' : 'none';
        }
        for (var i = 0; i < options.length; i++) {
            if (options[i].style.display !== 'none') {
                currencySelect.selectedIndex = i;
                break;
            }
        }
    }
    networkSelect.addEventListener('change', updateCurrencies);
    updateCurrencies();
});
</script>

<? get_footer() ?>
