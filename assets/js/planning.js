const $ = require('jquery');

function initDatesRange($picker, $from, $to, withTime) {
  function displayDate() {
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
      firstDay: 1,
    },
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

  $picker.on('cancel.daterangepicker', function () {
    $picker.val('');
    $from.val('');
    $to.val('');
  });
}

function hideUselessFilters() {
  $('.search [data-hide="users"]').css('visibility', $('#hideUsers').prop('checked') ? 'hidden' : 'visible');
  $('.search [data-hide="assets"]').css('visibility', $('#hideAssets').prop('checked') ? 'hidden' : 'visible');
}

function toggleMoreInfos() {
  $('.planning').find('.item-data').toggle($('#display-more').prop('checked'));
}

function dateSortPlanning($clickedTh, $planning) {
  let day = $clickedTh.data('day');

  $clickedTh.siblings('th[data-day]').removeClass('loading').removeClass('sorted');
  $clickedTh.removeClass('sorted').addClass('loading');

  // We need to wait for the adding of loading class before doing this udge operation
  setTimeout(function () {
    $planning.find('tbody.item-rows').each(function () {
      var $tbody = $(this);

      $tbody
        .find('tr')
        .sort(function (a, b) {
          var $a = $(a);
          var $b = $(b);

          var aCount = $a.find('td[data-status="available"][data-day="' + day + '"]').length;
          var bCount = $b.find('td[data-status="available"][data-day="' + day + '"]').length;

          return aCount > bCount ? -1 : 1;
        })
        .appendTo($tbody);

      $clickedTh.removeClass('loading').addClass('sorted');
    });
  });
}

$(document).ready(function () {
  var $planning = $('.planning');

  // Datepickers
  initDatesRange($('#fromToRange'), $('#from'), $('#to'));
  initDatesRange($('#availableRange'), $('#availableFrom'), $('#availableTo'), true);

  hideUselessFilters();

  $('#hideUsers').on('change', hideUselessFilters);
  $('#hideAssets').on('change', hideUselessFilters);

  $('#display-more').on('change', toggleMoreInfos);

  $planning.on('click', 'thead tr.days th[data-day]', function () {
    dateSortPlanning($(this), $planning);
  });

  $('#loader').hide();
});
