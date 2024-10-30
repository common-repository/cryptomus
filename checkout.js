const settings = window.wc.wcSettings.getSetting( 'cryptomus_data', {} );
console.log('sergey',settings);
const Content = () => {
    return window.wp.htmlEntities.decodeEntities( settings.description || '' );
};

const icon = settings.icon;
const labelTitle = window.wp.htmlEntities.decodeEntities( settings.title );

const Block_Gateway = {
    name: 'cryptomus',
    label: window.wp.element.createElement(() =>
        window.wp.element.createElement(
            "span",
            {
                style: {
                    display: 'flex',
                    gap: '10px'
                },
            },
            window.wp.element.createElement("img", {
                src: icon,
                alt: labelTitle,
            }),
            labelTitle
        )
    ),
    content: Object( window.wp.element.createElement )( Content, null ),
    edit: Object( window.wp.element.createElement )( Content, null ),
    canMakePayment: () => true,
    ariaLabel: labelTitle,
    supports: {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( Block_Gateway );
