<?php
get_header();  // Добавляет хидер темы
// Получение данных, например, из GET-параметров или из сессии
echo '<div style="display:block; width: 500px; margin: auto;">';
echo '<h1>Cryptomus Payment Gateway</h1>';
$order_id = get_query_var('order_id');
$currencies = get_query_var('currencies');
$uniqueNetworks = get_query_var('uniqueNetworks');
?>
<form action="/cryptomus-pay/?order_id=<?=$order_id?>&step_id=2" method="post">
    <label for="network">Choose a network:</label>
    <select name="network" id="network">
        <?php foreach ($uniqueNetworks as $network): ?>
            <option value="<?= $network ?>"><?= $network ?></option>
        <?php endforeach; ?>
    </select>
    <label for="to_currency">Choose a currency:</label>
    <select name="to_currency" id="to_currency">
        <?php foreach ($currencies as $currency): ?>
            <?php if ($currency['is_available']): ?>
                <option value="<?= $currency['currency'] ?>" data-network="<?= $currency['network'] ?>"><?= $currency['currency'] ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <br/><br/>
    <button type="submit">Pay Now</button>
</form>
<br/><br/>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var networkSelect = document.getElementById('network');
    var currencySelect = document.getElementById('currency');

    function updateCurrencies() {
        var selectedNetwork = networkSelect.value;
        var options = currencySelect.options;

        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var currencyNetwork = option.dataset.network;
            option.style.display = currencyNetwork === selectedNetwork ? 'block' : 'none';
        }
        // Automatically select the first available option
        for (var i = 0; i < options.length; i++) {
            if (options[i].style.display !== 'none') {
                currencySelect.selectedIndex = i;
                break;
            }
        }
    }

    networkSelect.addEventListener('change', updateCurrencies);

    // Initialize on page load
    updateCurrencies();
});
</script>
<?php
echo '</div>';
// echo 'CURRENCIES: <pre>' . print_r($currencies, true) . '</pre>';
// Форма оплаты
get_footer();
?>
