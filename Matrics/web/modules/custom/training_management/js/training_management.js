(function ($) {

	Drupal.behaviors.testProva3 = {
    	attach: function(context, settings) {
			
	$('#training-manage-form .card').click(function(){ 
	   var status =  $('#training-manage-form .card h3').html();
	     
    $('#edit-certificate-count').val(status).trigger('change');
})
     	}
	};
}(jQuery));