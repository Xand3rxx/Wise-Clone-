/**
 * @description Trigger modal and render trnsaftion details
 * @param string $route
 *
 * @returns modal view
 */
$(document).on("click", "#transfer-details-modal", function (event) {
    event.preventDefault();
    $route = $(this).attr("data-url");

    $.ajax({
        url: $route,
        beforeSend: function () {
            $("#modal-body").html(
                '<div class="pr-calculator-fee-container d-flex justify-content-center align-items-center"><div class="tw-loader tw-loader--sm"><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div></div></div>'
            );
        },
        // return the result
        success: function (result) {
            setTimeout(() => {
                // Clear modal body before appending transaction data
                $("#modal-body").html("");

                // Append transaction data
                $("#modal-body").html(result);
            }, 800);
        },
        complete: function () {
            $("#spinner-icon").hide();
        },
        error: function (jqXHR, testStatus, error) {
            displayMessage(
                "An error occured while trying to retrieve transaction details.",
                "error"
            );
            $("#spinner-icon").hide();
        },
        timeout: 8000,
    });
});

/**
 * @description Close bootstrap modal backdrop on click
 */
$('.close').click(function() {
    $(".modal-backdrop").remove();
});


/**
 * @description Execute conversion from source amount on keyup
 */
$(document).keyup('#source-amount', function(){
    // Variable declaration
    $sourceCurrency = $('#source-amount');
    $currentValue = removeComma($sourceCurrency.val());
    $maxAmount = $sourceCurrency.data('max');
    $currencySymbol = $sourceCurrency.data('symbol');
    $sourceCurrencyId = $.trim($('#source-currency-id').children("option:selected").val());
    $targetCurrencyId = $.trim($('#target-currency-id').children("option:selected").val());
    $route = $("#source-url").attr("data-source-url");

    // Validate ajax request parameters
    validateRequirements($currentValue, $sourceCurrencyId, $targetCurrencyId, $maxAmount, $currencySymbol, $route);
});

/**
 * @description Execute conversion from source/target currency on select dropdown change
 */
$("#source-currency-id, #target-currency-id").change(function (event) {
    event.preventDefault();

    $sourceCurrencyId = $.trim($("#source-currency-id").children("option:selected").val());
    $targetCurrencyId = $.trim($('#target-currency-id').children("option:selected").val());
    $route = $("#source-url").attr("data-source-url");

    // Get each source currency balance
    if($(this).attr('name') === 'source_currency_id'){
        currencyBalance($sourceCurrencyId);
    }

    setTimeout(function(){
        $sourceCurrency = $('#source-amount');
        $currentValue = removeComma($sourceCurrency.val());
        $maxAmount = $sourceCurrency.attr('data-max');
        $currencySymbol = $sourceCurrency.attr('data-symbol');

        // Validate ajax request parameters
        validateRequirements($currentValue, $sourceCurrencyId, $targetCurrencyId, $maxAmount, $currencySymbol, $route);
    }, 1000);
});

function currencyBalance($sourceCurrencyId)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: $("#source-url").attr("data-currency-balance-url"),
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            'source_currency_id': $sourceCurrencyId,
        },

        // return the result
        success: function (result) {
            $sourceCurrency = $('#source-amount');
            setTimeout(() => {
                $sourceCurrency.val(result.sourceCurrencyBalance);
                $sourceCurrency.attr({
                    'data-max':result.sourceCurrencyBalance,
                    'value':result.sourceCurrencyBalance,
                    'max':result.sourceCurrencyBalance,
                    'data-symbol':result.sourceCurrency.symbol
                });
            }, 500);
        },
        complete: function () {
            $("#spinner-icon").hide();
        },
        error: function (jqXHR, testStatus, error) {
            displayMessage(error, "error");
            $("#spinner-icon").hide();
        },
        timeout: 8000,
    });
}

/**
 * @description Validate inpul fields
 * @param float $currentValue
 * @param int $sourceCurrencyId
 * @param int $targetCurrencyId
 * @param float $maxAmount
 * @param string $currencySymbol
 *
 * @returns value
 */
function validateRequirements($currentValue, $sourceCurrencyId, $targetCurrencyId, $maxAmount, $currencySymbol, $route){

    // If source currency is not selected
    if($sourceCurrencyId == ''){
        displayMessage("Please select a source currency","error" );
        return;
    }

    // If target currency is not selected
    if($targetCurrencyId == ''){
        displayMessage("Please select a target currency","error" );
        return;
    }

     // Default back to currency balance, if current value in input field  is greater than the currency balance
     if(parseFloat($currentValue) > parseFloat($maxAmount)){
        displayMessage("Amount cannot be greater than "+ $currencySymbol+ $maxAmount,"error" );
        $sourceCurrency.val($maxAmount);
        ajaxConverter(parseFloat($maxAmount), $sourceCurrencyId, $targetCurrencyId, $route);
        return;
    }

    // If current value input field is 0 or is NaN, default to 1
    if(parseFloat($currentValue) == 0 || isNaN($currentValue)){
        displayMessage("Amount to be sent cannot be less than "+ $currencySymbol+ "1","error" );
        // $sourceCurrency.val(0);
        ajaxConverter(parseFloat($currentValue), $sourceCurrencyId, $targetCurrencyId, $route);

        // currencyBalance($sourceCurrencyId);
        return;
    }else{
        ajaxConverter(parseFloat($currentValue), $sourceCurrencyId, $targetCurrencyId, $route);
    }
}

/**
 * @description Remove comma from value
 * @param string value
 *
 * @returns value
 */
function removeComma(value) {
    return parseFloat($.trim(value).replace(/,/g, ""));
}

/**
 * @description Execute Ajax request
 *
 * @param float $currentValue
 * @param int $sourceCurrencyId
 * @param int $targetCurrencyId
 * @param string $route
 *
 * @returns string HTML
 */
function ajaxConverter($currentValue, $sourceCurrencyId, $targetCurrencyId, $route)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: $route,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            'source_amount': $currentValue,
            'source_currency_id': $sourceCurrencyId,
            'target_currency_id': $targetCurrencyId
        },
        beforeSend: function () {
            $("#transaction-breakdown").html(
                '<div class="pr-calculator-fee-container d-flex justify-content-center align-items-center"><div class="tw-loader tw-loader--sm"><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div><div class="tw-loader__stripe"></div></div></div>'
            );
        },
        // return the result
        success: function (result) {
            setTimeout(() => {
                $("#transaction-breakdown").html("");
                $("#transaction-breakdown").html(result);
                formatAmount();
            }, 1000);
        },
        complete: function () {
            $("#spinner-icon").hide();
        },
        error: function (jqXHR, testStatus, error) {
            displayMessage(error, "error");
            $("#spinner-icon").hide();
        },
        timeout: 8000,
    });
}

function formatAmount()
{
    // Numeral Formatting
    const sourceAmount = new Cleave('#source-amount', {
        numeral: true,
        numeralThousandsGroupStyle: 'hundred'
    });

    const targetAmount = new Cleave('#target-amount', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
}
/**
 * @description Display session message with Sweet Alert
 * @param string message
 * @param string type
 *
 * @returns toast
 */
//Feedback from session message to be displayed with Sweet Alert
function displayMessage(message, type) {
    const Toast = swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 8000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });
    Toast.fire({
        icon: type,
        //   type: 'success',
        title: message,
    });
}
