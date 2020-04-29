const $ = require('jquery');
require('bootstrap');

$(document).ready(function () {
  const $modal = $('#delete-item-modal');
  if (!$modal.length) {
    return;
  }

  $('.trigger-delete').on('click', function (e) {
    e.preventDefault();
    const $button = $(this);

    $('[data-role="name"]', $modal).text($button.data('display-name'));
    $('[data-role="message"]', $modal).text($button.data('message'));
    $('button[data-role="submit"]', $modal).data('url', $button.data('href'));

    $modal.modal('show');
  });

  $('button[data-role="submit"]', $modal).on('click', function () {
    window.location = $(this).data('url');
    $(this).closest('.modal').modal('hide');
  });
});
