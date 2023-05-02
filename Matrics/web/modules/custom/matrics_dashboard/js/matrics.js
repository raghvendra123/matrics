(function ($, Drupal, window, document, drupalSettings) {
  'use strict';
  Drupal.behaviors.basic = {
   attach: function (context, settings) {
    $( ".row_position" ).sortable({
      delay: 150,
      stop: function() {
        var selectedData = new Array();
        $('.row_position>li').each(function() {
          selectedData.push($(this).attr("id"));
        });
        updateOrder(selectedData);
      }
    });
    
      
    function updateOrder(data) {
      $.ajax({
        url:"ajaxblock",
        type:'post',
        data:{position:data},
        success:function(){
          // alert('your change successfully saved');.
        }
      })
      }
      const allEqual = arr => arr.every(val => val === arr[0]);
      check_status();
      check_chart_status();
      
      // var sts_arr = [];
      // $('#tabledrag-test-table tr .status').each(function() {
      //     sts_arr.push($(this).attr('checked'));
      // });
      // const status_result = allEqual(sts_arr);
      // if (status_result==true) {
      //   if(sts_arr[0]==undefined){
      //      $('.toggle_status').removeAttr('checked');
      //   }else{
      //      $('.toggle_status').attr('checked', 'checked');
      //   }
      // }
      // var cht_arr = [];
      // $('#tabledrag-test-table tr .chart_status').each(function() {
      //     cht_arr.push($(this).attr('checked'));
      // });
      // const chart_result = allEqual(cht_arr);
      // if (chart_result==true) {
      //   if(cht_arr[0]==undefined){
      //     $('.toggle_chart').removeAttr('checked');
      //   }else{
      //     $('.toggle_chart').attr('checked', 'checked');
      //   }
      // }
      
      
      $('.toggle_status').click(function(){
        var status = $(this).is(':checked');
        if (status == true) {
          $('#tabledrag-test-table tr .status').each(function() {
            $(this).prop("checked", true); 
          });
        }
         else if (status == false) {
          $('#tabledrag-test-table tr .status').each(function() {
            $(this).prop("checked", false);
          });
        }
      });
      $('.toggle_chart').click(function(){
        var status = $(this).is(':checked');
        if (status==true) {
          $('#tabledrag-test-table tr .chart_status').each(function() {
            $(this).prop("checked", true); 
          });
        } else if (status==false) {
          $('#tabledrag-test-table tr .chart_status').each(function() {
            $(this).prop("checked", false);
          });
        }
      });
      
      $('.status').click(function(){
        var clicked = $(this).is(':checked');
        if (clicked==false) {
          $(this).removeAttr('checked');
        }else{
          $(this).attr('checked', 'checked');
        }
        check_status();
        // var sts_arr = [];
        // $('#tabledrag-test-table tr .status').each(function() {
        //     sts_arr.push($(this).prop('checked'));
        // });
        // console.log(sts_arr);
        // const status_result = allEqual(sts_arr);
        // if (status_result==true && sts_arr[0]==true) {
        //     $('.toggle_status').prop("checked", true);
        // }else if (status_result==true && sts_arr[0]==false) {
        //     $('.toggle_status').prop("checked", false);
        // } else{
        //     $('.toggle_status').prop("checked", false);
        // }
      });
      $('.chart_status').click(function(){
        var clicked = $(this).is(':checked');
        if (clicked==false) {
          $(this).removeAttr('checked');
        }else{
          $(this).attr('checked', 'checked');
        }
        check_chart_status();
        // var cht_arr = [];
        // $('#tabledrag-test-table tr .chart_status').each(function() {
        //     cht_arr.push($(this).prop('checked'));
        // });
        // const chart_result = allEqual(cht_arr);
        // if (chart_result==true && cht_arr[0]==true) {
        //     $('.toggle_chart').prop("checked", true);
        // }else if (chart_result==true && cht_arr[0]==false) {
        //     $('.toggle_chart').prop("checked", false);
        // }else{
        //     $('.toggle_chart').prop("checked", false);
        // }
      });
      
      function check_status() {
        var sts_arr = [];
        $('#tabledrag-test-table tr .status').each(function() {
          sts_arr.push($(this).prop('checked'));
        });
        console.log(sts_arr);
        const status_result = allEqual(sts_arr);
        if (status_result==true && sts_arr[0]==true) {
          $('.toggle_status').prop("checked", true);
        }else if (status_result==true && sts_arr[0]==false) {
          $('.toggle_status').prop("checked", false);
        } else{
          $('.toggle_status').prop("checked", false);
        }
      }
      function check_chart_status() {
        var cht_arr = [];
        $('#tabledrag-test-table tr .chart_status').each(function() {
          cht_arr.push($(this).prop('checked'));
        });
        const chart_result = allEqual(cht_arr);
        if (chart_result==true && cht_arr[0]==true) {
          $('.toggle_chart').prop("checked", true);
        }else if (chart_result==true && cht_arr[0]==false) {
          $('.toggle_chart').prop("checked", false);
        }else{
          $('.toggle_chart').prop("checked", false);
        }
      }
    }
  }

}(jQuery, Drupal, this, this.document, drupalSettings));
