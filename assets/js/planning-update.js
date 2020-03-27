const $ = require('jquery');
require('bootstrap');

function colorTableBox($tableBox) {
  var isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);
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

function triggerUpdate(url, newStatus, $planning, $modal) {
  var payload = generatePayload($planning);

  var nbAssets = Object.keys(payload.assets).length;
  var nbUsers = Object.keys(payload.users).length;
  if (!nbAssets && !nbUsers) {
    return;
  }

  var updates = [];
  if (nbAssets) {
    updates.push(nbAssets + ' véhicule' + (nbAssets > 1 ? 's' : ''));
  }
  if (nbUsers) {
    updates.push(nbUsers + ' utilisateur' + (nbUsers > 1 ? 's' : ''));
  }

  $modal.find('#nb-assets-users').text(updates.join(' et '));
  $modal.find('#confirm-update').data('status', newStatus).data('url', url);
  $modal.modal('show');
}

function doUpdate(url, newStatus, $planning) {
  let payload = generatePayload($planning);
  $.ajax({
    contentType: 'application/json',
    method: 'POST',
    dataType: 'json',
    url: url,
    data: JSON.stringify(payload),
    success: ({ lastUpdate }) => {
      $('#planning-form').data('loading-timestamp', lastUpdate);
      updatePlanningFromPayload($planning, newStatus, payload);
    },
    error: function () {
      window.alert('Une erreur est survenue, merci de vérifier vos paramètres.');
    },
  });
}

function updatePlanningFromPayload($planning, newStatus, payload) {
  ['users', 'assets'].forEach((ownerType) => {
    var currentObjects = payload[ownerType] || {};
    Object.keys(currentObjects).forEach((objectId) => {
      payload[ownerType][objectId].forEach((schedule) => {
        var [from, to] = schedule;
        var $td = $planning.find('tr[data-type="' + ownerType + '"][data-id="' + objectId + '"] td[data-from="' + from + '"][data-to="' + to + '"]');
        $td.removeClass($td.data('status')).addClass(newStatus).data('status', newStatus);
      });
    });
  });

  $planning.find('.checked').removeClass('checked').find('input:checkbox').prop('checked', false);
}

function generatePayload($planning) {
  let payload = {
    users: {},
    assets: {},
  };

  $planning.find('input[type=checkbox]:checked').each(function () {
    var $owner = $(this).closest('tr');
    var ownerId = $owner.data('id');
    var type = $owner.data('type');
    var $parent = $(this).closest('td');

    if (!payload[type][ownerId]) {
      payload[type][ownerId] = [];
    }
    payload[type][ownerId].push([$parent.data('from'), $parent.data('to')]);
  });

  return payload;
}

function checkLastUpdate() {
  if (document.hidden || !$('#alert-last-update').hasClass('d-none')) {
    return;
  }

  $.ajax({
    contentType: 'application/json',
    method: 'GET',
    dataType: 'json',
    url: $('#planning-form').data('last-update-href') + window.location.search,
    success: ({ lastUpdate }) => {
      if (lastUpdate > $('#planning-form').data('loading-timestamp')) {
        $('#alert-last-update').removeClass('d-none');
      }
    },
  });
}

$(document).ready(function () {
  var $planning = $('.planning');

  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('scrollTop')) {
    $(window).scrollTop(urlParams.get('scrollTop'));
  }

  $planning.on('click', '.slot-box input:checkbox', function (e) {
    e.stopImmediatePropagation();
    colorTableBox($(this).closest('.slot-box'));
  });

  $planning.on('click', '.slot-box', function () {
    selectTableBox($(this));
  });

  var $modalUpdate = $('#modal-update');

  $('.trigger-update').on('click', function () {
    var $this = $(this);
    triggerUpdate($this.data('href'), $this.data('status'), $planning, $modalUpdate);
  });

  $modalUpdate
    .on('hide.bs.modal', function () {
      $('.planning-actions-container .btn').prop('disabled', false);
    })
    .on('show.bs.modal', function () {
      $('.planning-actions-container .btn').prop('disabled', true);
    });

  $modalUpdate.find('#confirm-update').on('click', function () {
    var $this = $(this);
    doUpdate($this.data('url'), $this.data('status'), $planning);

    $modalUpdate.modal('hide');
  });

  $planning.find('input[type=checkbox]:checked').closest('.slot-box').addClass('checked');

  $('#alert-last-update a').on('click', function (e) {
    e.preventDefault();

    let newUrlParams = new URLSearchParams(window.location.search);
    newUrlParams.set('scrollTop', $(document).scrollTop());

    window.location = window.location.origin + window.location.pathname + '?' + newUrlParams.toString();
  });

  setInterval(checkLastUpdate, 5000);
});
