export function initDatesRange($picker, $from, $to, withTime) {
  console.log($picker, $from, $to, withTime);
  if (!$picker.length) {
    return;
  }

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
    $from.val(picker.startDate.format('YYYY-MM-DDTHH:mm')).trigger('change');
    $to.val(picker.endDate.format('YYYY-MM-DDTHH:mm'));
  });

  $picker.on('cancel.daterangepicker', function () {
    $picker.val('');
    $from.val('').trigger('change');
    $to.val('');
  });
}
