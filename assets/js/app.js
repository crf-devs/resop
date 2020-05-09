import '../css/app.scss';
import '../css/app-organization.scss';
import '../css/availability-form.scss';
import '../css/login.scss';
import '../css/planning.scss';
import '../css/availability-table.scss';
import './_delete-item-modal';

import { displayAjaxModal } from './_helpers';

const $ = require('jquery');
require('util');
require('popper.js');
require('bootstrap');
require('bootstrap-select');
require('daterangepicker');
const browserUpdate = require('browser-update');

$.fn.selectpicker.Constructor.DEFAULTS.noneSelectedText = '-';
$.fn.selectpicker.Constructor.DEFAULTS.noneResultsText = 'Aucun résultat pour {0}';
$.fn.selectpicker.Constructor.DEFAULTS.selectAllText = 'Tout sélectionner';
$.fn.selectpicker.Constructor.DEFAULTS.deselectAllText = 'Tout déselectionner';
$.fn.selectpicker.Constructor.DEFAULTS.doneButtonText = 'Fermer';
$.fn.selectpicker.Constructor.DEFAULTS.mobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);

browserUpdate({ required: { e: -2, f: -2, o: -2, s: -2, c: -10 }, insecure: true, unsupported: true, api: 2020.04 });

$(document).ready(function () {
  $(document).on('click', '[data-toggle="ajax-modal"]', function () {
    $('#modal-ajax').clone().attr('id', false).data('href', $(this).data('href')).appendTo('body').modal('show');
  });

  // Allow modals stacking
  $(document).on('show.bs.modal', '.modal', function () {
    const zIndex = 1040 + 10 * $('.modal:visible').length;
    $(this).css('z-index', zIndex);
    setTimeout(function () {
      $('.modal-backdrop')
        .not('.modal-stack')
        .css('z-index', zIndex - 1)
        .addClass('modal-stack');
    });
  });

  $(document)
    .on('show.bs.modal', '.ajax-modal', function (event) {
      const $modal = $(this);
      const url = $modal.data('href') || $(event.relatedTarget).data('href');

      if (!url) {
        return;
      }

      displayAjaxModal($modal, url);
    })
    .on('hidden.bs.modal', function () {
      $(this).remove();
    });
});
