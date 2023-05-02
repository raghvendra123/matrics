(function (Drupal, $) {

  Drupal.behaviors.viewsExpandableTable = {
    attach: function (context, settings) {
      // Find all the triggers, then locate the targets and bind to clicks.
      $('tr[data-views-expandable-table-trigger]', context).each(function () {
        var $trigger = $(this);
        var table = $trigger.parents('table')[0];
        var $target = $('tr[data-views-expandable-table-target="' + this.dataset.viewsExpandableTableTrigger + '"]', table);

        // Toggle when trigger clicked.
        $trigger.once().click(function () {
          toggleExpanded($trigger, $target);
        });
        // Allow some elements within trigger element to be clicked w/o toggle.
        $('a, btn, input', $trigger).click(function (event) {
            event.stopPropagation();
        });

        // Allow hover to apply to both rows.
        $trigger.hover(function () {
          toggleHover($trigger, $target);
        }, function () {
          toggleHover($trigger, $target);
        });
        $target.hover(function () {
          toggleHover($trigger, $target);
        }, function () {
          toggleHover($trigger, $target);
        });
      });
    }
  };

  // Detects if either element is being hovered.
  function isHover($trigger, $target) {
    return $trigger.is(":hover") || $target.is(":hover");
  }

  // Toggle the expanded class on both elements, based on status of trigger.
  function toggleExpanded($trigger, $target) {
    $trigger.toggleClass('expanded');
    $target.toggleClass('expanded', $trigger.hasClass('expanded'));
  }

  // Toggle hover class on hover for either element.
  function toggleHover($trigger, $target) {
    var hover = isHover($trigger, $target);
    $trigger.toggleClass('views-expandable-table-hover', hover);
    $target.toggleClass('views-expandable-table-hover', hover);
  }

})(Drupal, jQuery);
