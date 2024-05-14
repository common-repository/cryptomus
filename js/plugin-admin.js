jQuery(document).ready(function() {
    // Обработчик изменения состояния чекбокса 'h2h'
    jQuery('#woocommerce_cryptomus_h2h').change(function() {
        // Если 'h2h' выключен, скрываем опции
        var disabled = !jQuery(this).is(':checked');
        jQuery('#woocommerce_cryptomus_accepted_networks').closest('tr').toggle(!disabled);
        jQuery('#woocommerce_cryptomus_accepted_currencies').closest('tr').toggle(!disabled);
        jQuery('#woocommerce_cryptomus_theme').closest('tr').toggle(!disabled);
    });

    // Вызываем событие change для инициализации состояния опций при загрузке страницы
    jQuery('#woocommerce_cryptomus_h2h').change();
});
