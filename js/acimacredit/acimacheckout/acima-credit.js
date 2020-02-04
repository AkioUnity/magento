var AcimaCredit = {
    MESSAGE_TYPE_READY: 'ACIMA_ECOM_IFRAME_READY',
    MESSAGE_TYPE_INITIALIZE: 'ACIMA_ECOM_IFRAME_INITIALIZE',
    MESSAGE_TYPE_CLOSE: 'ACIMA_ECOM_IFRAME_CLOSE',
    MESSAGE_TYPE_ERROR: 'ACIMA_ECOM_IFRAME_ERROR',
    MESSAGE_TYPE_CHECKOUT_SUCCESSFUL: 'ACIMA_ECOM_IFRAME_CHECKOUT_SUCCESSFUL',
    MESSAGE_MODE_PRE_APPROVAL: 'Preapproval',
    MESSAGE_MODE_CHECKOUT: 'Checkout',
    isReady: false
};

AcimaCredit.onReceiveMessage = function (event) {
    if (event && event.data) {
        try {
            var data = JSON.parse(event.data);
            var origin = event.origin;

            AcimaCredit.parseMessage(data);
        } catch (e) {
            AcimaCredit.log(e);
        }    
    }    
};

AcimaCredit.parseMessage = function (message) {
    AcimaCredit.log(message);
    switch (message.type) {
        case AcimaCredit.MESSAGE_TYPE_READY:
            AcimaCredit.ready();
            break;
        case AcimaCredit.MESSAGE_TYPE_CLOSE:
            AcimaCredit.close();
            break;
        case AcimaCredit.MESSAGE_TYPE_ERROR:
            AcimaCredit.error(message);
            break;
        case AcimaCredit.MESSAGE_TYPE_CHECKOUT_SUCCESSFUL:
            AcimaCreditCheckout.success(message);
            break;
    }
};

AcimaCredit.ready = function () {
    AcimaCredit.isReady = true;
    AcimaCredit.log('AcimaCredit.ready');
    if (AcimaCreditCheckout.waiting) {
        AcimaCredit.log('AcimaCreditCheckout.waiting is true');
        AcimaCreditCheckout.init();
    } else if (AcimaCreditPreApproval.waiting) {
        AcimaCredit.log('AcimaCreditPreApproval.waiting is true');
        AcimaCreditPreApproval.init();
    }
};

AcimaCredit.close = function () {
    if (AcimaCreditCheckout.waiting) {
        AcimaCreditCheckout.redirect = true;
        AcimaCreditCheckout.redirectToThankYouPage();
    } else if (AcimaCreditCheckout.initied) {
        AcimaCreditCheckout.redirectToFailurePage();
    }
    
    AcimaCredit.remove();
};

AcimaCredit.remove = function () {
    var elements = document.getElementsByClassName('acima-credit-iframe-container');
    
    for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.classList.add('closed');
    }

    var body = document.getElementsByTagName('body')[0];    
    body.classList.remove('frozen');
}

AcimaCredit.error = function (message) {
    AcimaCredit.log(message);
};

AcimaCredit.postMessage = function (message, iframeElement) {
    var str = JSON.stringify(message)
    return iframeElement.contentWindow.postMessage(str, '*');
};

AcimaCredit.log = function (str) {
    console.log('*** ACIMA CREDIT');
    console.log(str);
};

window.addEventListener('message', AcimaCredit.onReceiveMessage, false);