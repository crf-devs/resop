const $ = require('jquery');

function colorTableBox($tableBox) {
  const isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);
}

function selectTableBox($tableBox) {
  if (!$tableBox) {
    return;
  }

  const $checkbox = $tableBox.find('input:checkbox');
  if ($checkbox.prop('disabled')) {
    return;
  }

  $checkbox.prop('checked', !$checkbox.prop('checked'));
  colorTableBox($tableBox);
}

function handleSlotBoxClick(event) {
  if ($(this).hasClass('disabled')) {
    return;
  }

  const $slotBox = event.target.type === 'checkbox' ? $(this).closest('.slot-box') : $(this);
  if (event.target.type === 'checkbox') {
    event.stopImmediatePropagation();
    colorTableBox($slotBox);
  } else {
    selectTableBox($slotBox);
  }

  const $table = $('.availability-table');
  const $lastClickedTd = $table.find('.prev-clicked');

  if ($lastClickedTd && event.shiftKey) {
    if ($table.data('availability-mode') === 'planning') {
      handlePlanningShiftClick($table, $slotBox, $lastClickedTd);
    } else {
      handleDefaultShiftClick($table, $slotBox, $lastClickedTd);
    }
  }

  if ($lastClickedTd) {
    $lastClickedTd.removeClass('prev-clicked');
  }
  $slotBox.addClass('prev-clicked');
}

function handleShiftInput(event) {
  if (!event.repeat) {
    $('.availability-table').toggleClass('shift-pressed', event.type === 'keydown');
  }
}

function handleDefaultShiftClick($table, $currentClickedTd, $prevClickedTd) {
  window.getSelection().removeAllRanges();

  const dayStart = Math.min($currentClickedTd.data('day'), $prevClickedTd.data('day'));
  const dayEnd = Math.max($currentClickedTd.data('day'), $prevClickedTd.data('day'));
  const hourStart = Math.min($currentClickedTd.data('from'), $prevClickedTd.data('from'));
  const hourEnd = Math.max($currentClickedTd.data('to'), $prevClickedTd.data('to'));

  $table
    .find('td.slot-box')
    .filter((i, td) => {
      let $td = $(td);

      return !$td.hasClass('disabled') && $td.data('day') >= dayStart && $td.data('day') <= dayEnd && $td.data('from') >= hourStart && $td.data('to') <= hourEnd;
    })
    .each((i, td) => {
      if ($(td).hasClass('checked') !== $currentClickedTd.hasClass('checked')) {
        selectTableBox($(td));
      }
    });
}

function handlePlanningShiftClick($planning, $currentClickedTd, $lastClickedTd) {
  window.getSelection().removeAllRanges();
  const isChecked = $currentClickedTd.hasClass('checked');

  const minTbodyIndex = Math.min($lastClickedTd.closest('tbody').index(), $currentClickedTd.closest('tbody').index());
  const maxTbodyIndex = Math.max($lastClickedTd.closest('tbody').index(), $currentClickedTd.closest('tbody').index());
  const tdFrom = Math.min(Date.parse($lastClickedTd.data('from')), Date.parse($currentClickedTd.data('from')));
  const tdTo = Math.max(Date.parse($lastClickedTd.data('to')), Date.parse($currentClickedTd.data('to')));

  // default case : maxTbodyIndex === minTbodyIndex
  let minTrIndex = Math.min($lastClickedTd.closest('tr').index(), $currentClickedTd.closest('tr').index());
  let maxTrIndex = Math.max($lastClickedTd.closest('tr').index(), $currentClickedTd.closest('tr').index());
  if (maxTbodyIndex !== minTbodyIndex) {
    if ($lastClickedTd.closest('tbody').index() === minTbodyIndex) {
      minTrIndex = $lastClickedTd.closest('tr').index();
    } else if ($currentClickedTd.closest('tbody').index() === minTbodyIndex) {
      minTrIndex = $currentClickedTd.closest('tr').index();
    }

    if ($lastClickedTd.closest('tbody').index() === maxTbodyIndex) {
      maxTrIndex = $lastClickedTd.closest('tr').index();
    } else if ($currentClickedTd.closest('tbody').index() === maxTbodyIndex) {
      maxTrIndex = $currentClickedTd.closest('tr').index();
    }
  }

  const handleTbody = function (i, tbody) {
    const currentTbodyIndex = $(tbody).index();

    $(tbody)
      .find('tr')
      .filter((i, tr) => {
        const trIndex = $(tr).index();
        if (minTbodyIndex !== maxTbodyIndex) {
          if (currentTbodyIndex === minTbodyIndex) {
            return trIndex >= minTrIndex;
          } else if (currentTbodyIndex === maxTbodyIndex) {
            return trIndex <= maxTrIndex;
          }

          return true;
        }

        return trIndex >= minTrIndex && trIndex <= maxTrIndex;
      })
      .each(handleTr);
  };

  const handleTr = function (i, tr) {
    $(tr)
      .find('td')
      .filter((i, td) => !$(td).hasClass('disabled') && Date.parse($(td).data('from')) >= tdFrom && Date.parse($(td).data('to')) <= tdTo)
      .each(function () {
        if (isChecked !== $(this).hasClass('checked')) {
          selectTableBox($(this));
        }
      });
  };

  $planning
    .find('tbody.item-rows')
    .filter((index, currentTbody) => $(currentTbody).index() >= minTbodyIndex && $(currentTbody).index() <= maxTbodyIndex)
    .each(handleTbody);
}

$(document).ready(function () {
  $('.availability-table').on('click', '.slot-box', handleSlotBoxClick);
  $(document).on('keydown keyup', handleShiftInput);
});
