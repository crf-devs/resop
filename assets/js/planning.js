import { initDatesRange } from './_helpers';
import { fetchMissions, initMissionsEvents } from './_planning-missions';
import { addPopovers } from './_planning';
import { initUpdateEvents } from './_planning-update';

const $ = require('jquery');

function hideUselessFilters() {
  $('.search [data-hide="users"]').css('visibility', $('#hideUsers').prop('checked') ? 'hidden' : 'visible');
  $('.search [data-hide="assets"]').css('visibility', $('#hideAssets').prop('checked') ? 'hidden' : 'visible');
  $('.search [data-hide="available"]').css('visibility', !$('#availableFrom').val() ? 'hidden' : 'visible');
}

function toggleMoreInfos() {
  $('.planning').find('.item-data').toggle($('#display-more').prop('checked'));
}

function dateSortPlanning($clickedTh, $planning) {
  let day = $clickedTh.data('day');
  let $thWithSameDay = $('th[data-day="' + day + '"]');

  $thWithSameDay.siblings('th[data-day]').removeClass('loading').removeClass('sorted');
  $thWithSameDay.removeClass('sorted').addClass('loading');

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

      $thWithSameDay.removeClass('loading').addClass('sorted');
    });
  });
}

$(document).ready(function () {
  const $planning = $('.planning');

  // Datepickers
  initDatesRange($('#fromToRange'), $('#from'), $('#to'));
  initDatesRange($('#availableRange'), $('#availableFrom'), $('#availableTo'), true);

  hideUselessFilters();
  addPopovers($planning);

  initUpdateEvents();
  initMissionsEvents();
  fetchMissions('/organizations/missions/find' + window.location.search);

  $('#hideUsers').on('change', hideUselessFilters);
  $('#hideAssets').on('change', hideUselessFilters);
  $('#availableFrom').on('change', hideUselessFilters);

  $('#display-more').on('change', toggleMoreInfos);

  $planning.on('click', 'thead tr.days th[data-day]', function () {
    dateSortPlanning($(this), $planning);
  });

  // The table is hidden by default for performances reason
  $planning.css('display', 'table');
  $('#loader').hide();

  const actionContainer = document.querySelector('.planning-actions-container-wrapper');

  window.addEventListener('scroll', () => {
    actionContainer.style.transform = `translate3d(${document.scrollingElement.scrollLeft}px, 0, 0)`;
  });
});
