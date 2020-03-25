const $ = require('jquery');

$(document).ready(function () {
  var $selectedOccupation = $('.js-occupation .form-check-input:checked');
  var $occupationBlock = $('.js-occupation');
  if ($selectedOccupation.length > 0) {
    $occupationBlock
      .find('.form-control')
      .parent()
      .toggle('-' === $selectedOccupation.val());
  }
  $('.js-occupation .form-check-input').change(function () {
    if ('-' === $(this).val()) {
      $occupationBlock.find('.form-control').parent().show();
      $occupationBlock.find('.form-control').focus();
    } else {
      $occupationBlock.find('.form-control').val('').parent().hide();
    }
  });
});
