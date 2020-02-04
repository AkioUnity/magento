var AcimaCreditCheckout = {
    iframe: null,
    waiting: false,
    initied: false,
    orderUpdated: false,
    redirect: false,
    transaction: null,
    customer: null,
};

AcimaCreditCheckout.onDOMContentLoaded = function () {
    if (document.getElementById('acima-credit-iframe-checkout')) {
        AcimaCreditCheckout.iframe = document.getElementById('acima-credit-iframe-checkout');
        AcimaCredit.log('onDOMContentLoaded');
        var body = document.getElementsByTagName('body')[0];
        body.classList.add('frozen');
        AcimaCreditCheckout.start();
    } else {
        AcimaCredit.log('onDOMContentLoaded acima-credit not set');
    }
};

AcimaCreditCheckout.start = function () {
    AcimaCredit.log('AcimaCreditCheckout waiting=true');
    if (AcimaCredit.isReady) {
        AcimaCreditCheckout.init();
    } else {
        AcimaCreditCheckout.waiting = true;
    }
}

AcimaCreditCheckout.getCustomerInformation = function () {
    AcimaCredit.log('AcimaCreditCheckout.getCustomerInformation()');
    /*AcimaCreditCheckout.customerInfo = {
        firstName: '',
        middleName: '',
        lastName: '',
        phone: '',
        address: {
            street1: '',
            street2: '',
            city: '',
            state: '',
            zipCode: ''
        },
        email: ''
    };*/
    AcimaCreditCheckout.customer = acima_credit_settings.customer;
};

AcimaCreditCheckout.getOrderInformation = function () {
    AcimaCredit.log('AcimaCreditCheckout.getOrderInformation()');
    /*AcimaCreditCheckout.orderInfo = {
        id: '',
        lineItems: [
            {
                productId: '',
                productName: '',
                quantity: 0,
                unitPrice: 0
            }
        ],
        cartTotal: 0,
        orderTotal: 0,
        salesTax: 0
    };*/
    AcimaCreditCheckout.transaction = acima_credit_settings.transaction;
};

AcimaCreditCheckout.onCollectInformation = function () {
    AcimaCredit.log('AcimaCreditCheckout.onLoadAjaxInformation()');
    
    if (AcimaCreditCheckout.transaction && AcimaCreditCheckout.customer) {
        var message = {
            type: AcimaCredit.MESSAGE_TYPE_INITIALIZE,
            mode: AcimaCredit.MESSAGE_MODE_CHECKOUT,
            transaction: AcimaCreditCheckout.transaction,
            customer: AcimaCreditCheckout.customer,
            merchantId: acima_credit_settings.merchant_id
        };
        AcimaCredit.log('postMessage');
        AcimaCredit.log(message);
        AcimaCredit.postMessage(message, AcimaCreditCheckout.iframe);
    };
};

AcimaCreditCheckout.init = function () {
    AcimaCredit.log('AcimaCreditCheckout.init()');
    AcimaCreditCheckout.waiting = false;
    AcimaCreditCheckout.initied = true;
    AcimaCreditCheckout.getCustomerInformation();
    AcimaCreditCheckout.getOrderInformation();
    AcimaCreditCheckout.onCollectInformation();
};

AcimaCreditCheckout.success = function (message) {
    AcimaCreditCheckout.waiting = true;

    var orderId = document.getElementById('orderId').value;
    var leaseId = message.leaseId;
    var checkoutToken = message.checkoutToken;

    document.getElementById('acimacredit_lease_id').value = leaseId;
    document.getElementById('acimacredit_checkout_token').value = checkoutToken;

    AcimaCreditCheckout.onUpdateOrder();
};

AcimaCreditCheckout.onUpdateOrder = function () {
    AcimaCreditCheckout.orderUpdated = true;

    AcimaCreditCheckout.redirectToThankYouPage();
};

AcimaCreditCheckout.redirectToThankYouPage = function () {
    if (AcimaCreditCheckout.orderUpdated && AcimaCreditCheckout.redirect) {
        document.getElementById('form_acimacheckout').submit();
    }
};

AcimaCreditCheckout.redirectToFailurePage = function () {
    window.location.href = document.getElementById('failure_page').value;
};

document.addEventListener('DOMContentLoaded', AcimaCreditCheckout.onDOMContentLoaded);