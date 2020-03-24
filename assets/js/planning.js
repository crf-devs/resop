const $ = require('jquery');
require('bootstrap');

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

function initDatesRange ($picker, $from, $to, withTime) {
  function displayDate () {
    if(withTime) {
      $picker.val($picker.data('daterangepicker').startDate.format('DD/MM/YYYY HH:mm') + ' à ' + $picker.data('daterangepicker').endDate.format('DD/MM/YYYY HH:mm'));
    } else {
      $picker.val($picker.data('daterangepicker').startDate.format('DD/MM/YYYY') + ' au ' + $picker.data('daterangepicker').endDate.format('DD/MM/YYYY'));
    }
  }

  $picker.daterangepicker({
    autoUpdateInput: false,
    showDropdowns: false,
    timePicker: !!withTime,
    timePicker24Hour: true,
    timePickerIncrement: 30,
    applyClass: 'btn-sm btn-primary',
    cancelClass: 'btn-sm btn-default',
    locale: {
      cancelLabel: 'Supprimer',
      format: 'DD/MM/YYYY HH:mm',
      separator: ' - ',
      applyLabel: 'Valider',
      fromLabel: 'De',
      toLabel: 'à',
      customRangeLabel: 'Custom',
      daysOfWeek: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
      monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      firstDay: 1
    }
  });

  if ($from.val() !== '' && $to.val() !== '') {
    $picker.data('daterangepicker').setStartDate(new Date($from.val()));
    $picker.data('daterangepicker').setEndDate(new Date($to.val()));
    displayDate();
  }

  $picker.on('apply.daterangepicker', function (ev, picker) {
    displayDate();
    $from.val(picker.startDate.format('YYYY-MM-DDThh:mm'));
    $to.val(picker.endDate.format('YYYY-MM-DDThh:mm'));
  });

  $picker.on('cancel.daterangepicker', function (ev, picker) {
    $picker.val('');
    $from.val('');
    $to.val('');
  });
}

function triggerUpdate (url, newStatus, $planning, $modal) {
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
    error: function (data) {
      window.alert('Une erreur est survenue, merci de vérifier vos paramètres.');
    }
  });
}


function updatePlanningFromPayload ($planning, newStatus, payload) {
  ['users', 'assets'].forEach(ownerType => {
    var currentObjects = payload[ownerType] || {};
    Object.keys(currentObjects).forEach(objectId => {
      payload[ownerType][objectId].forEach(schedule => {
        var [from, to] = schedule;
        $td = $planning.find('tr[data-type="' + ownerType + '"][data-id="' + objectId + '"] td[data-from="' + from + '"][data-to="' + to + '"]');
        $td
          .removeClass($td.data('status'))
          .addClass(newStatus)
          .data('status', newStatus);
      });
    });
  });

  $planning.find('.checked').removeClass('checked').find('input:checkbox').prop('checked', false);
}

function generatePayload ($planning) {
  var payload = {
    users: {},
    assets: {}
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

function hideUselessFilters() {
  $('.search [data-hide="users"]').css('visibility', $('#hideUsers').prop('checked') ? 'hidden' : 'visible');
  $('.search [data-hide="assets"]').css('visibility', $('#hideAssets').prop('checked') ? 'hidden' : 'visible');
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

  var $modalUpdate = $('#modal-update');
  $('.trigger-update').on('click', function () {
    var $this = $(this);
    triggerUpdate($this.data('href'), $this.data('status'), $planning, $modalUpdate);
  });
  $modalUpdate.on('hide.bs.modal', function (e) {
    $('.planning-actions-container .btn').prop('disabled', false);
  }).on('show.bs.modal', function (e) {
    $('.planning-actions-container .btn').prop('disabled', true);
  }).find('#confirm-update').on('click', function () {
    var $this = $(this);
    doUpdate($this.data('url'), $this.data('status'), $planning);

    $modalUpdate.modal('hide');
  });

  // Datepickers
  initDatesRange($('#fromToRange'), $('#from'), $('#to'));
  initDatesRange($('#availableRange'), $('#availableFrom'), $('#availableTo'), true);

  hideUselessFilters();

  $('#hideUsers').on('change', hideUselessFilters);
  $('#hideAssets').on('change', hideUselessFilters);

  $planning.find('input[type=checkbox]:checked').closest('.slot-box').addClass('checked');
  $('#loader').hide();
});

