AcimaCreditPreApproval = {
    iframe: null,
    waiting: false,
    initied: false,
    start: function () {
        AcimaCreditPreApproval.iframe = document.getElementById('acima-credit-iframe-pre-approval');
        AcimaCreditPreApproval.iframe.parentElement.parentElement.classList.remove('closed');

        var body = document.getElementsByTagName('body')[0];
        body.appendChild(AcimaCreditPreApproval.iframe.parentElement.parentElement);

        body.classList.add('frozen');

        AcimaCreditPreApproval.iframe.setAttribute('src', AcimaCreditPreApproval.iframe.dataset.src);
        AcimaCreditPreApproval.waiting = true;
    },

    init: function () {
        AcimaCreditPreApproval.waiting = false;
        AcimaCreditPreApproval.initied = true;
        var message = {
            type: AcimaCredit.MESSAGE_TYPE_INITIALIZE,
            mode: AcimaCredit.MESSAGE_MODE_PRE_APPROVAL,
            merchantId: acima_credit_settings.merchant_id
        };
        AcimaCredit.postMessage(message, AcimaCreditPreApproval.iframe);
    }
};