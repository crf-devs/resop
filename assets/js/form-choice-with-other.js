const $ = require('jquery');

$(document).ready(function () {
  var $selectedChoice = $('.js-choice-with-other .form-check-input:checked');
  var $choiceWithOther = $('.js-choice-with-other');
  if ($selectedChoice.length > 0) {
    $choiceWithOther
      .find('.form-control')
      .parent()
      .toggle('-' === $selectedChoice.val());
  }
  $('.js-choice-with-other .form-check-input').change(function () {
    if ('-' === $(this).val()) {
      $choiceWithOther.find('.form-control').parent().show();
      $choiceWithOther.find('.form-control').focus();
    } else {
      $choiceWithOther.find('.form-control').val('').parent().hide();
    }
  });
});
