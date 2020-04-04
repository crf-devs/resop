const $ = require('jquery');
require('bootstrap');

function colorTableBox($tableBox) {
  const isChecked = $tableBox.find('input:checkbox').prop('checked');
  $tableBox.toggleClass('checked', isChecked);
}

function selectTableBox($tableBox) {
  if (!$tableBox) {
    return;
  }

  const $checkbox = $tableBox.find('input:checkbox');
  if ($checkbox.prop('disabled')) {
    return;
  }

  $checkbox.prop('checked', !$checkbox.prop('checked'));
  colorTableBox($tableBox);
}

function triggerUpdate(url, newStatus, $planning, $modal) {
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

function handleShiftClick($planning, $currentClickedTd, $lastClickedTd) {
  window.getSelection().removeAllRanges();
  const isChecked = $currentClickedTd.hasClass('checked');

  const minTbodyIndex = Math.min($lastClickedTd.closest('tbody').index(), $currentClickedTd.closest('tbody').index());
  const maxTbodyIndex = Math.max($lastClickedTd.closest('tbody').index(), $currentClickedTd.closest('tbody').index());
  const tdFrom = Math.min(Date.parse($lastClickedTd.data('from')), Date.parse($currentClickedTd.data('from')));
  const tdTo = Math.max(Date.parse($lastClickedTd.data('to')), Date.parse($currentClickedTd.data('to')));

  // default case : maxTbodyIndex === minTbodyIndex
  let minTrIndex = Math.min($lastClickedTd.closest('tr').index(), $currentClickedTd.closest('tr').index());
  let maxTrIndex = Math.max($lastClickedTd.closest('tr').index(), $currentClickedTd.closest('tr').index());
  if (maxTbodyIndex !== minTbodyIndex) {
    if ($lastClickedTd.closest('tbody').index() === minTbodyIndex) {
      minTrIndex = $lastClickedTd.closest('tr').index();
    } else if ($currentClickedTd.closest('tbody').index() === minTbodyIndex) {
      minTrIndex = $currentClickedTd.closest('tr').index();
    }

    if ($lastClickedTd.closest('tbody').index() === maxTbodyIndex) {
      maxTrIndex = $lastClickedTd.closest('tr').index();
    } else if ($currentClickedTd.closest('tbody').index() === maxTbodyIndex) {
      maxTrIndex = $currentClickedTd.closest('tr').index();
    }
  }

  const handleTbody = function (i, tbody) {
    const currentTbodyIndex = $(tbody).index();

    $(tbody)
      .find('tr')
      .filter((i, tr) => {
        const trIndex = $(tr).index();
        if (minTbodyIndex !== maxTbodyIndex) {
          if (currentTbodyIndex === minTbodyIndex) {
            return trIndex >= minTrIndex;
          } else if (currentTbodyIndex === maxTbodyIndex) {
            return trIndex <= maxTrIndex;
          }

          return true;
        }

        return trIndex >= minTrIndex && trIndex <= maxTrIndex;
      })
      .each(handleTr);
  };

  const handleTr = function (i, tr) {
    $(tr)
      .find('td')
      .filter((i, td) => Date.parse($(td).data('from')) >= tdFrom && Date.parse($(td).data('to')) <= tdTo)
      .each(function () {
        if (isChecked !== $(this).hasClass('checked')) {
          selectTableBox($(this));
        }
      });
  };

  $planning
    .find('tbody.item-rows')
    .filter((index, currentTbody) => $(currentTbody).index() >= minTbodyIndex && $(currentTbody).index() <= maxTbodyIndex)
    .each(handleTbody);
}

$(document).ready(function () {
  const $planning = $('.planning');
  let $lastClickedTd = null;

  $(document).on('keydown', function (e) {
    if (e.shiftKey && $lastClickedTd && !$lastClickedTd.hasClass('highlight')) {
      $planning.find('.highlight').removeClass('highlight');
      $lastClickedTd.addClass('highlight');
    }
  });

  $(document).on('keyup', function (e) {
    if (e.keyCode === 16) {
      $planning.find('.highlight').removeClass('highlight');
    }
  });

  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('scrollTop')) {
    $(window).scrollTop(urlParams.get('scrollTop'));
  }

  $planning.on('click', '.slot-box input:checkbox', function (e) {
    e.stopImmediatePropagation();

    colorTableBox($(this).closest('.slot-box'));
    if (e.shiftKey && null !== $lastClickedTd) {
      handleShiftClick($planning, $(this).closest('td'), $lastClickedTd);
    }
    $lastClickedTd = $(this).closest('td');
  });

  $planning.on('click', '.slot-box', function (e) {
    e.stopImmediatePropagation();
    selectTableBox($(this));

    if (e.shiftKey && null !== $lastClickedTd) {
      handleShiftClick($planning, $(this), $lastClickedTd);
    }
    $lastClickedTd = $(this);
  });

  const $modalUpdate = $('#modal-update');

  $('.trigger-update').on('click', function () {
    const $this = $(this);
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
    const $this = $(this);
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

  checkLastUpdate(true);
  setInterval(checkLastUpdate, 30 * 1000);
});
