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

    // Обработчик изменения состояния селекта 'theme'
    function toggleDescription() {
        var description = jQuery('p.description');
        var selectElement = jQuery('#woocommerce_cryptomus_theme');

        if (selectElement.val() === 'custom') {
            description.show();
            // Устанавливаем ширину дескрипшна равной ширине селекта
            description.width(selectElement.width());
        } else {
            description.hide();
        }
    }

    // Инициализация состояния описания при загрузке страницы
    toggleDescription();

    // Обработчик изменения состояния селекта 'theme'
    jQuery('#woocommerce_cryptomus_theme').change(function() {
        toggleDescription();
    });
});
