$(function() {

    //responsive
    $('.iwBtnResponsive').click(function() {
        $('.iwMenuResponsive').toggle();
    });
    $('.iwMenuResponsive').toggle();

    //ocultar barra
    var sidebar = $('#sidebar-app-menu');
    $(".iwSlider").on("click", function(event){
        $(".iwSidebar").toggle();
    });

    $(".iwSlider").on("mouseover", function(event){
        if ($(this).css('display') == 'none') {
            $(this).css("cursor", "e-resize");
        } else {
            $(this).css("cursor", "w-resize");
        }

        $(this).css({ boxShadow: '3px 0px 3px rgba(130, 132, 132, 0.5)' });
    });

    $(".iwSlider").on("mouseout", function(event){
       $(this).css({ boxShadow: 'none' });
    });


});
