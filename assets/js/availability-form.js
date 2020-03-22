const $ = require('jquery');

function colorTable () {
  $('.availability-table').find('input:checkbox:checked').closest('.clickable-table-box').toggleClass('checked', true);
  $('.availability-table').find('input:checkbox:not(:checked)').closest('.clickable-table-box').toggleClass('checked', false);
  $('.availability-table').find('input:checkbox:disabled').closest('.clickable-table-box').toggleClass('disabled', true);
}

function colorTableBox ($tableBox) {
  $tableBox.toggleClass('checked', $tableBox.find('input:checkbox').prop('checked'));
}

function selectTableBox ($tableBox, checked) {
  if (!$tableBox) {
    return;
  }

  var $checkbox = $tableBox.find('input:checkbox');
  if ($checkbox.prop('disabled')) {
      return;
  }

  $checkbox.prop('checked', !$checkbox.prop('checked'));
  colorTableBox($tableBox);
}

$(document).ready(function () {
  colorTable();

  $('.availability-table').on('click', '.clickable-table-box input:checkbox', function (e) {
    e.stopImmediatePropagation();
    colorTableBox($(this).closest('.clickable-table-box'));
  });

  $('.availability-table').on('click', '.clickable-table-box', function () {
    selectTableBox($(this));
  });
});
