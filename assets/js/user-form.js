
const $ = require('jquery');

$(document).ready(function() {
    $('.js-otherOccupation').parent().hide();
    $('.js-occupation .form-check-input').change(function() {
        if ('-' === $(this).val()) {
            $('.js-otherOccupation').parent().show();
            $('.js-otherOccupation').focus();
        } else {
            $('.js-otherOccupation').val('').parent().hide();
        }
    })
});
