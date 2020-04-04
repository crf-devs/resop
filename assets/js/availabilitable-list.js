const $ = require('jquery');
require('bootstrap');

$(document).ready(function () {
  const $modalDelete = $('#modal-delete');

  $('.trigger-delete').on('click', function (e) {
    e.preventDefault();

    $('#to-delete-name').html($(this).data('display-name'));
    $('#confirm-update').data('url', $(this).data('href'));

    $modalDelete.modal('show');
  });

  $modalDelete.find('#confirm-update').on('click', function () {
    window.location = $(this).data('url');
    $modalDelete.modal('hide');
  });
});
