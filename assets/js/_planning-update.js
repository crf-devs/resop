import { toggleBoxPopover } from './_planning';

const $ = require('jquery');
require('bootstrap');

function triggerUpdate(url, newStatus, withComment, $planning, $modal) {
  const payload = generatePayload($planning);

  const nbAssets = Object.keys(payload.assets).length;
  const nbUsers = Object.keys(payload.users).length;
  if (!nbAssets && !nbUsers) {
    return;
  }

  const updates = [];
  if (nbAssets) {
    updates.push(nbAssets + ' véhicule' + (nbAssets > 1 ? 's' : ''));
  }
  if (nbUsers) {
    updates.push(nbUsers + ' utilisateur' + (nbUsers > 1 ? 's' : ''));
  }

  $modal.find('#nb-assets-users').text(updates.join(' et '));
  $modal.find('#confirm-update').data('status', newStatus).data('url', url);
  $modal.find('#status-update-comment-container').toggle(withComment);
  $modal.modal('show');
}

function doUpdate(url, newStatus, $planning) {
  const payload = generatePayload($planning);
  $.ajax({
    contentType: 'application/json',
    method: 'POST',
    dataType: 'json',
    url: url,
    data: JSON.stringify(payload),
    success: () => {
      updatePlanningFromPayload($planning, newStatus, payload);
      checkLastUpdate(true);
    },
    error: function () {
      window.alert('Une erreur est survenue, merci de vérifier vos paramètres.');
    },
  });
}

function updatePlanningFromPayload($planning, newStatus, payload) {
  ['users', 'assets'].forEach((ownerType) => {
    const currentObjects = payload[ownerType] || {};
    Object.keys(currentObjects).forEach((objectId) => {
      payload[ownerType][objectId].forEach((schedule) => {
        let [from, to] = schedule;
        const $td = $planning.find('tr[data-type="' + ownerType + '"][data-id="' + objectId + '"] td[data-from="' + from + '"][data-to="' + to + '"]');
        $td.removeClass($td.data('status')).addClass(newStatus).data('status', newStatus).data('comment', payload.comment);

        toggleBoxPopover($td);
      });
    });
  });

  $planning.find('.checked').removeClass('checked').find('input:checkbox').prop('checked', false);

  $('#status-update-comment').val('');
}

function generatePayload($planning) {
  let payload = {
    users: {},
    assets: {},
    comment: null,
  };

  payload.comment = $('#status-update-comment').val();

  $planning.find('input[type=checkbox]:checked').each(function () {
    const $owner = $(this).closest('tr');
    const ownerId = $owner.data('id');
    const type = $owner.data('type');
    const $parent = $(this).closest('td');

    if (!payload[type][ownerId]) {
      payload[type][ownerId] = [];
    }
    payload[type][ownerId].push([$parent.data('from'), $parent.data('to')]);
  });

  return payload;
}

function checkLastUpdate(forceUpdate) {
  if (document.hidden || !$('#alert-last-update').hasClass('d-none')) {
    return;
  }

  const $form = $('#planning-form');

  $.ajax({
    contentType: 'application/json',
    method: 'GET',
    dataType: 'json',
    url: $form.data('last-update-href') + window.location.search,
    success: ({ lastUpdate, totalCount }) => {
      if (!!forceUpdate || !$form.data('loading-lastUpdate')) {
        $form.data('loading-lastUpdate', lastUpdate);
        $form.data('loading-totalCount', totalCount);
        return;
      }

      if (lastUpdate > $form.data('loading-lastUpdate') || totalCount < $form.data('loading-totalCount')) {
        $('#alert-last-update').removeClass('d-none');
      }
    },
  });
}

export function initUpdateEvents() {
  const $planning = $('.planning');

  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('scrollTop')) {
    $(window).scrollTop(urlParams.get('scrollTop'));
  }

  const $modalUpdate = $('#modal-update');

  $modalUpdate
    .on('hide.bs.modal', function () {
      $('.planning-actions-container .btn').prop('disabled', false);
    })
    .on('show.bs.modal', function () {
      $('.planning-actions-container .btn').prop('disabled', true);
    })
    .on('shown.bs.modal', function () {
      $modalUpdate.find('input:visible:first').focus();
    });

  $modalUpdate.find('#confirm-update').on('click', function () {
    const $this = $(this);
    doUpdate($this.data('url'), $this.data('status'), $planning);

    $modalUpdate.modal('hide');
  });

  $('.trigger-update').on('click', function () {
    const $this = $(this);
    triggerUpdate($this.data('href'), $this.data('status'), $this.attr('data-status-with-comment') !== undefined, $planning, $modalUpdate);
  });

  $planning.find('input[type=checkbox]:checked').closest('.slot-box').addClass('checked');

  $('#alert-last-update a').on('click', function (e) {
    e.preventDefault();

    let newUrlParams = new URLSearchParams(window.location.search);
    newUrlParams.set('scrollTop', $(document).scrollTop());

    window.location = window.location.origin + window.location.pathname + '?' + newUrlParams.toString();
  });

  setTimeout(function () {
    checkLastUpdate(true);
  }, 5000);
  setInterval(checkLastUpdate, 30 * 1000);
}
