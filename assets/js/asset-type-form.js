const $ = require('jquery');

function addNewWidget($list) {
  let counter = $list.data('widget-counter') || $list.children().length;
  let newWidget = $list.attr('data-prototype');
  newWidget = newWidget.replace(/__name__/g, counter);

  counter++;
  $list.data('widget-counter', counter);

  const $newWidget = $(newWidget);
  $newWidget.find('legend').remove();
  addWidgetDeleteLink($newWidget);

  $newWidget.appendTo($list);
}

function addWidgetDeleteLink($item) {
  var $removeFormButton = $('<button type="button" class="btn btn-outline-danger float-right">Supprimer</button>');
  $item.append($removeFormButton);

  $removeFormButton.on('click', function () {
    $item.remove();
  });
}

$(document).ready(function () {
  $('.add-collection-widget').each(function () {
    const $list = $($(this).attr('data-list-selector'));

    $(this).on('click', () => addNewWidget($list));

    $list.find('.disable-on-edit').prop('disabled', true);
  });

  $('form#edit-asset-type-form').on('submit', function () {
    $(this).find('.disable-on-edit:disabled').prop('disabled', false);
  });
});
