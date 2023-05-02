$(document).ready(function() {
    $(".hamburger-icon").click(function() {
        $("#dash_wrapper").toggleClass("sidebar_closed");

    });
    $(".form-control").focus(function() {
        $(this).parent().addClass('focused_input');
        //return false;
    });
    $('.form-control').blur(function() {
        if (!$(this).val()) {
            $(this).parent().removeClass('focused_input');
        }
    });
    /// search form 
    $(".search__form .search-icon").click(function() {
        $(".search__form").toggleClass("open-search");
    });
});