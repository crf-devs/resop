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
  const $assetTypeForm = $('form#edit-asset-type-form');
  let persistedKeys = $assetTypeForm.data('persisted-keys').split(',');

  $('.add-collection-widget').each(function () {
    const $list = $($(this).attr('data-list-selector'));
    $(this).on('click', () => addNewWidget($list));
  });

  $('div#asset_type_properties fieldset').each(function () {
    const currentKey = $(this).find('input.key-input').val();
    if (persistedKeys.indexOf(currentKey) >= 0) {
      $(this).find('.disable-on-edit').prop('disabled', true);
      persistedKeys = persistedKeys.filter((value) => value !== currentKey);
    } else {
      addWidgetDeleteLink($(this));
    }
  });

  $assetTypeForm.on('submit', function () {
    $(this).find('.disable-on-edit:disabled').prop('disabled', false);
  });
});
