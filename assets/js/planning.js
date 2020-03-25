const $ = require('jquery');

function initDatesRange ($picker, $from, $to, withTime) {
  function displayDate () {
    if (withTime) {
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
    $from.val(picker.startDate.format('YYYY-MM-DDTHH:mm'));
    $to.val(picker.endDate.format('YYYY-MM-DDTHH:mm'));
  });

  $picker.on('cancel.daterangepicker', function (ev, picker) {
    $picker.val('');
    $from.val('');
    $to.val('');
  });
}

function hideUselessFilters () {
  $('.search [data-hide="users"]').css('visibility', $('#hideUsers').prop('checked') ? 'hidden' : 'visible');
  $('.search [data-hide="assets"]').css('visibility', $('#hideAssets').prop('checked') ? 'hidden' : 'visible');
}

function toggleMoreInfos () {
  $('.planning').find('.item-data').toggle($('#display-more').prop('checked'));
}

$(document).ready(function () {
  // Datepickers
  initDatesRange($('#fromToRange'), $('#from'), $('#to'));
  initDatesRange($('#availableRange'), $('#availableFrom'), $('#availableTo'), true);

  hideUselessFilters();

  $('#hideUsers').on('change', hideUselessFilters);
  $('#hideAssets').on('change', hideUselessFilters);

  $('#display-more').on('change', toggleMoreInfos);

  $('#loader').hide();
});

