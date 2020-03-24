const $ = require('jquery');

function colorTableBox ($tableBox) {
  var isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);
}

function selectTableBox ($tableBox) {
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

function generatePayload($planning) {
  var payload = {
    users: {},
    assets: {}
  };

  $planning.find('input[type=checkbox]:checked').each(function () {
    var $owner = $(this).closest('tr');
    var ownerId = $owner.data('id');
    var type = $owner.data('type');
    var $parent = $(this).closest('td');

    if(!payload[type][ownerId]) {
      payload[type][ownerId] = [];
    }
    payload[type][ownerId].push([$parent.data('from'), $parent.data('to')]);

  });

  return payload;
}

$(document).ready(function () {
  var $planning = $('.planning');

  $planning.on('click', '.slot-box input:checkbox', function (e) {
    e.stopImmediatePropagation();
    colorTableBox($(this).closest('.slot-box'));
  });

  $planning.on('click', '.slot-box', function () {
    selectTableBox($(this));
  });

  $('#triggerToRemove').on('click', function () { 
    console.log(JSON.stringify(generatePayload($planning)));
  });
});

