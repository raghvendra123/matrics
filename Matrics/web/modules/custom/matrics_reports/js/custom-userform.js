(function($, Drupal, drupalSettings) {
    
  /**
   * Set default values and check that the behavior is applied once
   */
  Drupal.behaviors.matrics_reports = {
    attach: function (context, settings) {
      $('#customer_list').once().change(function() {
          $value = $(this).val();
          console.log($value);
          if ($value!=undefined) {
              $.ajax({
                  url: "/get_asset_by_customer",
                  method: "post",
                  data: {'customer_id':$value},
                  success: function(result){
                  console.log(result);
                  $(".asset_list").html(result);
                  var job='<option value="_none" selected>- Any -</option>';
                  $(".job_list").html(job);
              }});
          }
      });
      $(document).ready(function() {
          $value = $('#customer_list').val();
          $assets = $('.asset_list').val();
          $job = $('.job_list').val();
          if ($value!=undefined && $value!='_none') {
              $.ajax({
                  url: "/get_asset_by_customer",
                  method: "post",
                  data: {'customer_id':$value},
                  success: function(result){
                  console.log(result);
                  $(".asset_list").html(result);
                  $(".job_list option[value=" + $job + "]").prop("selected",true);
                  $(".asset_list option[value='" + $assets + "']").prop("selected",true);
              }});
          }
          if ($assets!='_none') {
              $.ajax({
                  url: "/get_jobtitle_by_asset",
                  method: "post",
                  data: {'asset_id':$assets},
                  success: function(result){
                  console.log(result);
                  $(".job_list").html(result);
                  $(".job_list option[value=" + $job + "]").prop("selected",true);
              }});
          } else {
              var job='<option value="_none" selected>- Any -</option>';
              $(".job_list").html(job);
          }
      })
      
      $('.asset_list').change(function() {
          $assets = $(this).val();
          if ($assets!='_none') {
              $.ajax({
                  url: "/get_jobtitle_by_asset",
                  method: "post",
                  data: {'asset_id':$assets},
                  success: function(result){
                  $(".job_list").html(result);
              }});
          } else {
              var job='<option value="_none" selected>- Any -</option>';
              $(".job_list").html(job);
          }
      });
      
    //   function get_assets ($value, $assets =false, $job = false ) {
    //       $.ajax({
    //               url: "/get_asset_by_customer",
    //               method: "post",
    //               data: {'customer_id':$value},
    //               success: function(result){
    //               console.log(result);
    //               $(".asset_list").html(result);
    //               if ($assets==false && $job==false) {
    //                   var job='<option value="_none">- Any -</option>';
    //                   $(".job_list").html(job);
    //               } else {
    //                   $(".job_list option[value=" + $job + "]").prop("selected",true);
    //                   $(".asset_list option[value='" + $assets + "']").prop("selected",true);
    //               }
    //           }});
    //   }
    },
  };

  
}(jQuery, Drupal, drupalSettings));