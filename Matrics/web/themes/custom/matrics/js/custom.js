/**
 * @file
 * This is an custom js.
 */

(function ($) {
   Drupal.behaviors.matrics_theme = {
     attach: function (context, settings) {
            var star_rating_width = jQuery('.fill-ratings span').width();
            console.log(star_rating_width);
            jQuery('.star-ratings').width(star_rating_width);
                
            $(document).ready(function(){
                // var dark_mode = window.localStorage.getItem('content');
                // if (dark_mode.length != 0) {
                //     //$('body').addClass('dark_mode');
                //     $('.changesite-theme input').prop('checked', true);
                // }
                $('.dark_mode .changesite-theme input').prop('checked', true);
                jQuery('#dark_mode').once().click(function(e) {
                    if ($(this).is(':checked')) {
                        $('body').addClass('dark_mode');
                        //window.localStorage.setItem('content', 'dark_mode');
                        Cookies.set('content', 'dark_mode');
                    }
                    else {
                        $('body').removeClass('dark_mode');
                        //window.localStorage.setItem('content', '');
                        Cookies.set('content', '');
                    }
                });
            });
            
            $('[data-toggle="tooltip"]').tooltip();
              
            var height = jQuery('.reports_left').height();
            jQuery('.report_listing').css('top', height);
            jQuery(".user-logged-in .region-content .messages__wrapper .close").once().click(function() {
                jQuery(this).parent().toggleClass('closePopup');
            });

            jQuery('.messages__wrapper .close').once().click(function(e) {
                jQuery('.messages__wrapper').addClass('closePopup');
            });
            //jQuery('.documents-upload').attr('disabled', true);

            jQuery('.faq_data h2').once().click(function(e) {
                jQuery(this).parent().toggleClass('open-faq');
            });

            var exp = jQuery('.expired').attr('width-percent');
            jQuery('.expired').css('width', exp + '%');
            
            var exp = jQuery('.one_month').attr('width-percent');
            jQuery('.one_month').css('width', exp + '%');
            
            // var exp = jQuery('.six_to_one').attr('width-percent');
            // jQuery('.six_to_one').css('width', exp + '%');
            
            var exp = jQuery('.three_to_one').attr('width-percent');
            jQuery('.three_to_one').css('width', exp + '%');
            
            var exp = jQuery('.six_to_three').attr('width-percent');
            jQuery('.six_to_three').css('width', exp + '%');
                 
            jQuery("#block-certificatecheck h2").once().click(function() {
                jQuery(this).parent().toggleClass("open-mapfield");
            });
            jQuery(".block-views-blockuser-blocks-block-1 h2").once().click(function() {
                jQuery(this).parent().toggleClass("open-contentfield");
            });
            
            jQuery(".block-views-blockuser-blocks-block-2 h2").once().click(function() {
                jQuery(this).parent().toggleClass("open-contentfield");
            });
            
            jQuery(".chart_toggle h2").once().click(function() {
                jQuery(this).parent().toggleClass("open-contentfield");
            });

            jQuery("#block-suggestedcoursed .content h2").once().click(function() {
                jQuery(this).parent().toggleClass("open-suggest");
            });

            setInterval(function () {
                jQuery(".region-content .messages__wrapper .close").parent().addClass('closePopup');
            }, 10000); 

        //     jQuery("#edit-field-start-date-value").change(function() {
        //         var val = jQuery(this).val();
        //         console.log(val);
        // jQuery("#edit-field-expiry-date-value").datepicker({
        //             minDate: val  
        //         });
        //     });
            // jQuery("#edit-field-start-date-value").datepicker({
            //     numberOfMonths: 1,
            //     onSelect: function (selected) {
            //         var dt = new Date(selected);
            //         console.log(selected);
            //         dt.setDate(dt.getDate() + 1);
            //         jQuery("#edit-field-expiry-date-value").datepicker("option", "minDate", dt);
            //     }
            // });
            // jQuery("#edit-field-expiry-date-value").datepicker({
            //     numberOfMonths: 1,
            //     onSelect: function (selected) {
            //         console.log(selected);
            //         var dt = new Date(selected);
            //         dt.setDate(dt.getDate() - 1);
            //         jQuery("#edit-field-start-date-value").datepicker("option", "maxDate", dt);
            //     }
            // });
            jQuery("input[name='field_start_date_value']").datepicker({
                numberOfMonths: 1,
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    console.log(selected);
                    dt.setDate(dt.getDate() + 1);
                    jQuery("input[name='field_expiry_date_value']").datepicker("option", "minDate", dt);
                }
            });
            jQuery("input[name='field_expiry_date_value']").datepicker({
                numberOfMonths: 1,
                onSelect: function (selected) {
                    console.log(selected);
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate() - 1);
                    jQuery("input[name='field_start_date_value']").datepicker("option", "maxDate", dt);
                }
            });
            var d = new Date();
            var month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;
            var date = year + "-" + month + "-" + day;
            $('#edit-field-course-date-0-value-date').attr('min', date);
            // $("#edit-field-start-date-value").on("blur", function(e) { $(this).datepicker("hide"); });
            // $("#edit-field-expiry-date-value").on("blur", function(e) { $(this).datepicker("hide"); });
            jQuery("#reset_form").click(function(e) {
                e.preventDefault();
                window.location.reload();
                // jQuery("#node-reports-form")[0].reset();
                // jQuery('.reports_right .form-wrapper').not('.reports_right #edit-title-wrapper').css('display', 'none');

            });
        }
    }
}(jQuery));
                 
jQuery(document).ready(function() {
    jQuery(".hamburger-icon").click(function() {
        jQuery("#dash_wrapper").toggleClass("sidebar_closed");
    });
    // jQuery(".form-item .form-text").focus(function() {
    //     jQuery(this).parent().addClass('focused_input');
    //     //return false;
    // });
    // jQuery('.form-item .form-text').blur(function() {
    //     if (!jQuery(this).val()) {
    //         jQuery(this).parent().removeClass('focused_input');
    //     }
    // });
    /// search form 
    jQuery(".profile-detail").click(function() {
        jQuery(this).next().toggle();
    });
    jQuery(".profile-notification").click(function() {
        jQuery('.notify').toggle();
        jQuery('.suggested_count').hide();

        jQuery.ajax({
            url: "/notification_read",
            type: "post",
            success: function (response) {
            },
        });
    });
    jQuery(".search__form .search-icon").click(function() {
        jQuery(".search__form").toggleClass("open-search");
    });
    jQuery(".anonymous .region-content .messages__wrapper .close").click(function() {
        jQuery(this).parent().toggleClass('closePopup');
    });
    jQuery(".profile .field.field--name-field-certificates > .field__label").click(function() {
        jQuery(this).parent().toggleClass("open-otherfield");
    });
     jQuery(".filter-mobile-head").click(function() {
        jQuery(this).toggleClass("open-filter-head");
        jQuery(".page-topForm-mobile").slideToggle().toggleClass("open-filter");
    });
    jQuery(".training_matrix_filter").click(function() {
        jQuery(this).toggleClass("open-filter-head-tmatrics");
        jQuery("#training-matrix-form > .js-form-item , #training-matrix-form > #asset-fieldset-container").toggleClass("open-filter-tmatrics");
    });
    // datepicker start
    jQuery(function () {
    // jQuery("#edit-start-date").datepicker({
    //     numberOfMonths: 1,
    //     onSelect: function (selected) {
    //         var dt = new Date(selected);
    //         dt.setDate(dt.getDate() + 1);
    //         console.log(dt);
    //         jQuery("#edit-end-date").datepicker("option", "minDate", dt);
    //     }
    // });
    // jQuery("#edit-end-date").datepicker({
    //     numberOfMonths: 1,
    //     onSelect: function (selected) {
    //         var dt = new Date(selected);
    //         dt.setDate(dt.getDate() - 1);
    //         console.log(dt);
    //         jQuery("#edit-start-date").datepicker("option", "maxDate", dt);
    //     }
    // });
    });
    // datepicker end
 
});

jQuery(document).click(function(e) {
    const date_div = document.getElementById("ui-datepicker-div");
    if (e.target==date_div) {
        jQuery(".hasDatepicker").datepicker("hide"); 
    }
});
