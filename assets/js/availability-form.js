const $ = require('jquery');

function colorTable () {
  $('.availability-table').find('input:checkbox:checked').closest('.clickable-table-box').toggleClass('checked', true);
  $('.availability-table').find('input:checkbox:not(:checked)').closest('.clickable-table-box').toggleClass('checked', false);
  $('.availability-table').find('input:checkbox:disabled').closest('.clickable-table-box').toggleClass('disabled', true);
}

function colorTableBox ($tableBox) {
  var isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);

  if(!isChecked) {
    var dayNumber = $tableBox.attr('data-day');
    $tableBox.closest('.availability-table').find('.day-title[data-day='+dayNumber+'] input:checkbox').prop('checked', false)
  }
}

function selectDay($dayTitle) {
  var dayNumber = $dayTitle.attr('data-day');
  $dayTitle.closest('.availability-table').find('.clickable-table-box[data-day='+dayNumber+'] input:checkbox:not(:disabled)').prop('checked', $dayTitle.find('input:checkbox').prop('checked'));
  colorTable();
}

function selectAll($button) {
  $button.closest('.availability-table').find('.clickable-table-box input:checkbox:not(:disabled)').prop('checked', true);
  $button.closest('.availability-table').find('.day-title input:checkbox').prop('checked', true);
  colorTable();
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

  $('.availability-table').on('click', '.day-title input:checkbox', function () {
    selectDay($(this).closest('.day-title'));
  });

  $('.availability-table').on('click', '.clickable-table-box input:checkbox', function (e) {
    e.stopImmediatePropagation();
    colorTableBox($(this).closest('.clickable-table-box'));
  });

  $('.availability-table').on('click', '.clickable-table-box', function () {
    selectTableBox($(this));
  });

  $('.availability-table').on('click', 'button.select-all', function () {
    selectAll($(this));
  });
});
