$( function() {

    var emiAmount = 100000, emiMonths = 12, emiBank = 1, emiPerMonth = 0;

    var interestRatePerMonthPerBank = {
        // kotak
        1: {
            3: 12, 
            6:12, 
            9: 14,
            12: 14,
            18: 15,
            24: 15 
        }, 
        // HDFC
        2: {
            3: 13,
            6: 13,
            9: 14,
            12: 14,
            18: 15,
            24: 15
        },
        //Axis Bank
        3: {
            3: 12,
            6: 12,
            9: 13,
            12: 13,
            18: 15,
            24: 15
        },
        //ICICI Bank
        4: {
            3: 13,
            6: 13,
            9: 13,
            12: 13
        },
        //IndusInd Bank
        5: {
            3: 13,
            6: 13,
            9: 13,
            12: 13,
            18: 15,
            24: 15
        },
        //RBL Bank
        6: {
            3: 13,
            6: 13,
            9: 13,
            12: 13,
            18: 13,
            24: 13
        },
        //Standard Chartered Bank
        7: {
            3: 13,
            6: 13,
            9: 14,
            12: 14
        },
        //Yes Bank
        8: {
            3: 12,
            6: 12,
            9: 13,
            12: 13,
            18: 14,
            24: 15
        }
    };

    $('#total-amount').on("change paste keyup", function() {
        if(!$(this).val()) {
            emiAmount = 0;
        }
        else{
            emiAmount = $(this).val();
        }
        calculateEmiPerMonth();
    });

    $('#cd-dropdown').dropdown({
        gutter : 63,
        onOptionSelect: function (opt) {
            emiBank = opt.context.dataset.value;
            // clear all months
            $('#cd-dropdown-period').html('');
            $("#cd-dropdown-period option").remove();
            $("#cd-dropdown-period option").hide();
            $('#cd-dropdown-period').empty();

            for (i = 0; i < Object.keys(interestRatePerMonthPerBank[emiBank]).length; i++) {
                $('#cd-dropdown-period').append('<option value="' + i + '" class="icon-monkey">' + i + '3 Months</option>');
            }
            calculateEmiPerMonth();
        }
    });

    $( '#cd-dropdown-period' ).dropdown( {
        gutter : 63,
        onOptionSelect: function (opt) {
            console.log(opt.context.dataset.value);
            emiMonths = opt.context.dataset.value;
            calculateEmiPerMonth();
        }
    });

    function calculateEmiPerMonth(
        princ = emiAmount, 
        term = emiMonths, 
        intrest = interestRatePerMonthPerBank[emiBank][emiMonths]) {
        //console.log("emiAmount, emiMonths, emiBank, interestRatePerMonthPerBank");
        //console.log(emiAmount, emiMonths, emiBank, interestRatePerMonthPerBank[emiBank][emiMonths]);

        if(princ && term && intrest) {
            var intr = intrest / 1200;
            var emiAmount1 =Math.round(princ * intr / (1 - (Math.pow(1/(1 + intr), term))));
            $('#emi-amount').text(emiAmount1.toString());
        }
        else
            $('#emi-amount').text("0");


        if(emiAmount1 <= 0) {
            $('#emi-comparison').text('');    
        }
        else if(emiAmount1 > 0 && emiAmount1 <= 1000) {
            $('#emi-comparison').text("That's less than the cost of a coffee!");
        }
        else if(emiAmount1 > 1000 && emiAmount1 <= 2000) {
            $('#emi-comparison').text("That's less than the cost of a dinner!");
        }
        else if(emiAmount1 > 2000 && emiAmount1 <= 3500) {
            $('#emi-comparison').text("That's less than a monthly cab ride!");
        }
        else if(emiAmount1 > 3500 && emiAmount1 <= 10000) {
            $('#emi-comparison').text("That's less than a monthly phone bill!");
        }
        else if(emiAmount1 > 10000 && emiAmount1 <= 20000) {
            $('#emi-comparison').text("That's less than a monthly gym membership!");
        }
        else if(emiAmount1 > 20000 && emiAmount1 <= 50000) {
            $('#emi-comparison').text("That's less than the cost of a mobile phone!");
        }
        else if(emiAmount1 > 50000 && emiAmount1 <= 100000) {
            $('#emi-comparison').text("That's less than the cost of a TV!");
        }
        else {
            $('#emi-comparison').text("That's almost the cost of a scooty!");
        }
    }
});



