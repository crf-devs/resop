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

function triggerUpdate(url, newStatus, $planning) {
  var payload = generatePayload($planning);
  $.ajax({
    contentType: 'application/json',
    method: 'POST',
    dataType: 'json',
    url: url,
    data: JSON.stringify(payload),
    success: () => {
      updatePlanningFromPayload($planning, newStatus, payload);
    },
    error: function(data) {
      window.alert('Une erreur est survenue, merci de vérifier vos paramètres.');
    }
  });
}

function updatePlanningFromPayload($planning, newStatus, payload) {
  ['users', 'assets'].forEach(ownerType => {
    var currentObjects = payload[ownerType] || {};
    Object.keys(currentObjects).forEach(objectId => {
        payload[ownerType][objectId].forEach(schedule => {
          var [from,to] = schedule;
          $td = $planning.find('tr[data-type="'+ownerType+'"][data-id="'+objectId+'"] td[data-from="'+from+'"][data-to="'+to+'"]');
          $td
            .removeClass($td.data('status'))
            .addClass(newStatus)
            .data('status', newStatus);
        });    
    });
  });
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

  $('.trigger-update').on('click', function () { 
    triggerUpdate($(this).data('href'), $(this).data('status'), $planning);
  });
});

