const $ = require('jquery');

function removePopovers($planning) {
  $planning.find('td[data-toggle="popover"]').popover('dispose').data('toggle', false);
}

export function addPopovers($planning) {
  removePopovers($planning);
  $planning.find('td[data-comment], td.mission').each((key, el) => toggleBoxPopover($(el)));
}

export function toggleBoxPopover($box) {
  $box.popover('dispose').data('toggle', false);

  let title = '';
  const texts = [];

  if ($box.data('comment')) {
    texts.push('<span class="badge badge-light" data-toggle="foo">Commentaire</span> ' + $('<em>').text($box.data('comment'))[0].outerHTML);
  }

  if ($box.data('mission-text')) {
    texts.push($box.data('mission-text'));
  }

  if (!texts.length) {
    return;
  }

  $box
    .data('toggle', 'popover')
    .popover({
      placement: 'auto',
      title: title,
      trigger: 'hover',
      delay: { show: 200, hide: 100 },
      content: function () {
        return texts.join('<br>');
      },
      html: true,
      sanitizeFn: function (content) {
        return content;
      },
    })
    .on('hide.bs.popover', function () {
      // Keep the popover displayed while the mouse is on it

      const $popover = $('.popover:hover');

      if (!$popover.length) {
        return true;
      }

      $popover.one('mouseleave', function () {
        $popover.popover('hide');
      });

      return false;
    });
}
