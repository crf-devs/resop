const $ = require('jquery');

function colorTable() {
  let $table = $('.availability-table');
  $table.find('input:checkbox:checked').closest('.clickable-table-box').toggleClass('checked', true);
  $table.find('input:checkbox:not(:checked)').closest('.clickable-table-box').toggleClass('checked', false);
  $table.find('input:checkbox:disabled').hide().closest('.clickable-table-box').toggleClass('disabled', true);
  $table.find('input:checkbox[data-status="booked"]').closest('.clickable-table-box').toggleClass('booked', true);
  $table.find('input:checkbox[data-status="locked"]').closest('.clickable-table-box').toggleClass('locked', true);
}

function colorTableBox($tableBox) {
  var isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);

  if (!isChecked) {
    var dayNumber = $tableBox.attr('data-day');
    $tableBox
      .closest('.availability-table')
      .find('.day-title[data-day=' + dayNumber + '] input:checkbox')
      .prop('checked', false);
  }
}

function selectDay($dayTitle) {
  var dayNumber = $dayTitle.attr('data-day');
  $dayTitle
    .closest('.availability-table')
    .find('.clickable-table-box[data-day=' + dayNumber + '] input:checkbox:not(:disabled)')
    .prop('checked', $dayTitle.find('input:checkbox').prop('checked'));
  colorTable();
}

function selectAll($table) {
  $table.find('.clickable-table-box input:checkbox:not(:disabled)').prop('checked', true);
  $table.find('.day-title input:checkbox').prop('checked', true);
  colorTable();
}

function selectTableBox($tableBox) {
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

  let $table = $('.availability-table');
  let $actions = $('.availability-actions');

  $table.on('click', '.day-title input:checkbox', function () {
    selectDay($(this).closest('.day-title'));
  });

  $table.on('click', '.clickable-table-box input:checkbox', function (e) {
    e.stopImmediatePropagation();
    colorTableBox($(this).closest('.clickable-table-box'));
  });

  $table.on('click', '.clickable-table-box', function () {
    selectTableBox($(this));
  });

  $actions.on('click', 'button.select-all', function () {
    selectAll($table);
  });

  $actions.on('click', '.pagination a', function () {
    let uncheckedCount = $table.find('.clickable-table-box input:checkbox[data-status="available"]:not(:checked)').length;
    let checkedCount = $table.find('.clickable-table-box input:checkbox[data-status="unknown"]:checked').length;

    if (uncheckedCount + checkedCount > 0) {
      $('#modal-confirm').modal('show');
      return false;
    }
  });
});
