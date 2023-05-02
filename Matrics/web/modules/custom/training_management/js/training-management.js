(function ($) {

	Drupal.behaviors.tmanage = {
    	attach: function(context, settings) {
			
        	$('a[id^="tmanage-"]').click(function(){ 
        	    $('a[id^="tmanage-"]').removeClass('active');
        	    $("html, body").animate({ scrollTop: $('#training_output').offset().top }, 1000);
        	    $(this).addClass('active');
        	   
        	    	        var val = $(this).attr("id");
        
        	   var status =  $('#'+val+' h3').attr('rel');
        	   console.log(status);
                $('#edit-certificate-count').val(status).trigger('change');
            });
            if(jQuery('#box-contain').text().length > 31) {
                $(document).ajaxComplete(function (event, xhr, settings) {
                    $('.training_filter').css('display', 'block');
                });
            }
            
            function getPagination(table, pagination) {
              var lastPage = 1;
            
            
                 lastPage = 1;
                  jQuery(pagination)
                    .find('li')
                    .slice(1, -1)
                    .remove();
                  var trnum = 0; // reset tr counter
                  var maxRows = 10; // get Max Rows from select option
                  var totalRows = jQuery(table + ' tbody tr.parent-row').length; // numbers of rows
                  if (maxRows == 5000 || totalRows <= maxRows) {
                    jQuery(pagination).hide();
                  } else {
                    jQuery(pagination).show();
                  }
            
                  var totalRows = jQuery(table + ' tbody tr.parent-row').length; // numbers of rows
                  jQuery(table + ' tr.parent-row:gt(0)').each(function() {
                    // each TR in  table and not the header
                    trnum++; // Start Counter
                    if (trnum > maxRows) {
                      // if tr number gt maxRows
            
                      jQuery(this).hide(); // fade it out
                    }
                    if (trnum <= maxRows) {
                      jQuery(this).show();
                    } // else fade in Important in case if it ..
                  }); //  was fade out to fade it in
                  if (totalRows > maxRows) {
                    // if tr total rows gt max rows option
                    var pagenum = Math.ceil(totalRows / maxRows); // ceil total(rows/maxrows) to get ..
                    //	numbers of pages
                    for (var i = 1; i <= pagenum; ) {
                      // for each page append pagination li
                      jQuery(pagination + ' #prev')
                        .before(
                          '<li data-page="' +
                            i +
                            '">\
            								  <span>' +
                            i++ +
                            '<span class="sr-only">(current)</span></span>\
            								</li>'
                        )
                        .show();
                    } // end for i
                  } // end if row count > max rows
                  jQuery(pagination + ' [data-page="1"]').addClass('active'); // add active class to the first li
                  jQuery(pagination + ' li').on('click', function(evt) {
                    // on click each page
                    evt.stopImmediatePropagation();
                    evt.preventDefault();
                    var pageNum = jQuery(this).attr('data-page'); // get it's number
            
                    var maxRows = 10; // get Max Rows from select option
            
                    if (pageNum == 'prev') {
                      if (lastPage == 1) {
                        return;
                      }
                      pageNum = --lastPage;
                    }
                    if (pageNum == 'next') {
                      if (lastPage == jQuery(pagination + ' li').length - 2) {
                        return;
                      }
                      pageNum = ++lastPage;
                    }
            
                    lastPage = pageNum;
                    var trIndex = 0; // reset tr counter
                    jQuery(pagination + ' li').removeClass('active'); // remove active class from all li
                    jQuery(pagination + ' [data-page="' + lastPage + '"]').addClass('active'); // add active class to the clicked
                    // $(this).addClass('active');					// add active class to the clicked
            	  	limitPagging();
                    jQuery(table + ' tr.parent-row:gt(0)').each(function() {
                      // each tr in table not the header
                      trIndex++; // tr index counter
                      // if tr index gt maxRows*pageNum or lt maxRows*pageNum-maxRows fade if out
                      if (
                        trIndex > maxRows * pageNum ||
                        trIndex <= maxRows * pageNum - maxRows
                      ) {
                        jQuery(this).hide();
                      } else {
                        jQuery(this).show();
                      } //else fade in
                    }); // end of for each tr in table
                  }); // end of on click pagination list
            	  limitPagging(pagination);
            
              // end of on select change
            
              // END OF PAGINATION
            }
            
            function limitPagging(pagination){
            	// alert($('.pagination li').length)
            
            	if(jQuery(pagination + ' li').length > 7 ){
            			if( jQuery(pagination + ' li.active').attr('data-page') <= 3 ){
            			jQuery(pagination + ' li:gt(5)').hide();
            			jQuery(pagination + ' li:lt(5)').show();
            			jQuery(pagination + ' [data-page="next"]').show();
            		}if (jQuery(pagination + ' li.active').attr('data-page') > 3){
            			jQuery(pagination + ' li:gt(0)').hide();
            			jQuery(pagination + ' [data-page="next"]').show();
            			for( let i = ( parseInt(jQuery(pagination + ' li.active').attr('data-page'))  -2 )  ; i <= ( parseInt(jQuery(pagination + ' li.active').attr('data-page'))  + 2 ) ; i++ ){
            				jQuery(pagination + ' [data-page="'+i+'"]').show();
            
            			}
            
            		}
            	}
            }
            getPagination('#myTable', '.training_pagination');
            getPagination('#courseTable', '.course_pagination');
     	}
	};
}(jQuery));
function sortTable(n) {
              var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
              table = document.getElementById("myTable");
              switching = true;
              // Set the sorting direction to ascending:
              dir = "asc";
              
              /* Make a loop that will continue until
              no switching has been done: */
              //console.log(table.rows);
              while (switching) {
                // Start by saying: no switching is done:
                switching = false;
                rows = table.rows;
                /* Loop through all table rows (except the
                first, which contains table headers): */
                for (i = 1; i < (rows.length - 1); i++) {
                  // Start by saying there should be no switching:
                  shouldSwitch = false;
                  /* Get the two elements you want to compare,
                  one from current row and one from the next: */
                  x = rows[i].getElementsByTagName("TD")[n];
                  y = rows[i + 1].getElementsByTagName("TD")[n];
                  /* Check if the two rows should switch place,
                  based on the direction, asc or desc: */
                  if (dir == "asc") {
                     // console.log(dir);
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                      // If so, mark as a switch and break the loop:zzz
                      shouldSwitch = true;
                      break;
                    }
                  } else if (dir == "desc") {
                     
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                      // If so, mark as a switch and break the loop:
                      shouldSwitch = true;
                      break;
                    }
                  }
                  
                }
                // console.log(shouldSwitch);console.log(switchcount);
                if (shouldSwitch) {
                  /* If a switch has been marked, make the switch
                  and mark that a switch has been done: */
                  rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                  switching = true;
                  
                  // Each time a switch is done, increase this count by 1:
                  switchcount ++;
                } else {
                    console.log(switchcount);
                  /* If no switching has been done AND the direction is "asc",
                  set the direction to "desc" and run the while loop again. */
                  if (switchcount == 0 && dir == "asc") {
                      
                    dir = "desc";
                    switching = true;
                  }
                }
              }
                childRowsAppend();
            }
//           const elements = document.querySelectorAll('.parent-row');
// elements.forEach(element => {
//   console.log();
//   var id= element.id;
//   var child_element = document.getElementById("#child-"+id);
//   console.log(child_element);
//   insertAfter(child_element, element)
//         // jQuery("#child-"+id).insertAfter("#"+id);
// }); 
        //     function insertAfter(newNode, existingNode) {
        //     existingNode.parentNode.insertBefore(newNode, existingNode.nextSibling);
        // }
function childRowsAppend() {
    jQuery('.parent-row').each(function(i, obj) {
       // console.log(jQuery(this))
        var id= jQuery(this).attr('id');
        jQuery("#child-"+id).insertAfter("#"+id);
        //jQuery("#child-"+id).appendTo("#"+id);
        //test
    });
}
