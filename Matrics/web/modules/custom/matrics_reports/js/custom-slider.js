(function($, Drupal, drupalSettings) {
    
  /**
   * Set default values and check that the behavior is applied once
   */
  Drupal.behaviors.matrics_reports = {
    attach: function (context, settings) {
      $('#edit-compliance-score-min').on('input', function() {
        var min = $(this).attr('min');
        var max = $(this).attr('max');
        var thisvalue = $(this).val();
        var this_value = Math.min(thisvalue,this.parentNode.childNodes[22].value - 1);
        var value = (100 / (parseInt(max - parseInt(min))) * parseInt(this_value) - (100 / (parseInt(max) - parseInt(min))) * parseInt(min));
        var children = this.parentNode.childNodes[0].childNodes;
        children[1].style.width = value + '%';
        children[5].style.left = value + '%';
        children[7].style.left = value + '%';
        children[11].style.left = value + '%';
        children[11].childNodes[1].innerHTML = this_value + '%';
      });
      
      $('#edit-compliance-score-max').on('input', function() {
        var min = $(this).attr('min');
        var max = $(this).attr('max');
        var thisvalue = $(this).val();
        var this_value = Math.max(this.value,this.parentNode.childNodes[10].value - (-1));
        var value = (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this_value) - (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this.min);
        var children = this.parentNode.childNodes[0].childNodes;
        children[3].style.width = (100 - value) + '%';
        children[5].style.right = (100 - value) + '%';
        children[9].style.left = value + '%';
        children[13].style.left = value + '%';
        children[13].childNodes[1].innerHTML = this_value + '%';
      });
      
    },
  };

  
}(jQuery, Drupal, drupalSettings));