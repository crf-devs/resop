import { addPopovers } from './_planning';
import { fetchMissions, initMissionsEvents } from './_planning-missions';

const $ = require('jquery');

function colorTable() {
  let $table = $('.availability-table');
  $table.find('input:checkbox:checked').closest('.slot-box').toggleClass('checked', true);
  $table.find('input:checkbox:not(:checked)').closest('.slot-box').toggleClass('checked', false);
  $table.find('input:checkbox:disabled').hide().closest('.slot-box').toggleClass('disabled', true);
  $table.find('input:checkbox[data-status="booked"]').closest('.slot-box').toggleClass('booked', true);
  $table.find('input:checkbox[data-status="locked"]').closest('.slot-box').toggleClass('locked', true);
}

function selectDay($dayTitle) {
  var dayNumber = $dayTitle.attr('data-day');
  $dayTitle
    .closest('.availability-table')
    .find('.slot-box[data-day=' + dayNumber + '] input:checkbox:not(:disabled)')
    .prop('checked', $dayTitle.find('input:checkbox').prop('checked'));
  colorTable();
}

function selectAll($table) {
  $table.find('.slot-box input:checkbox:not(:disabled)').prop('checked', true);
  $table.find('.day-title input:checkbox').prop('checked', true);
  colorTable();
}

$(document).ready(function () {
  colorTable();

  let $table = $('.availability-table');
  let $actions = $('.availability-actions');

  $table.on('click', '.day-title input:checkbox', function () {
    selectDay($(this).closest('.day-title'));
  });

  $actions.on('click', 'button.select-all', function () {
    selectAll($table);
  });

  $actions.on('click', '.pagination a', function () {
    let uncheckedCount = $table.find('.slot-box input:checkbox[data-status="available"]:not(:checked)').length;
    let checkedCount = $table.find('.slot-box input:checkbox[data-status="unknown"]:checked').length;

    if (uncheckedCount + checkedCount > 0) {
      $('#modal-confirm').modal('show');
      return false;
    }
  });

  // Send the data-comment from the checkboxes to the td
  $table.find('.slot-box input:checkbox[data-comment]').each(function (index, checkbox) {
    const $checkbox = $(checkbox);
    $checkbox.closest('td').attr('data-comment', $checkbox.data('comment'));
  });

  addPopovers($table);
  initMissionsEvents();
  fetchMissions(window.location.pathname + '/missions');
});
