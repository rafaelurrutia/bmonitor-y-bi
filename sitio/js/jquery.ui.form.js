/*
 * Form
 *
 */
(function( $ ) {

$.widget("ui.form",{ 
  _init:function() {
    var object = this;
    var form = this.element;
    var inputs = form.find("input , select ,textarea");
 
    form.find("fieldset").addClass("ui-widget-content");
    form.find("legend").addClass("ui-widget-header ui-corner-all");
    form.addClass("ui-widget");
 
    $.each(inputs, function() {
      $(this).addClass('ui-state-default ui-corner-all');
      $(this).wrap("<label />");
 
      if ($(this).is(":reset ,:submit"))
        object.buttons(this);
      else if ($(this).is(":checkbox"))
        object.checkboxes(this);
      else if ($(this).is("input[type='text']") || $(this).is("textarea") || $(this).is("input[type='password']"))
        object.textelements(this);
      else if ($(this).is(":radio"))
        object.radio(this);
     /// else if ($(this).is("select"))
      //  object.selector(this);
 
      if ($(this).hasClass("date")) {
        $(this).datepicker();
      }
 
 
    });
    $(".hover").hover(function() {
      $(this).addClass("ui-state-hover");
    }, function() {
      $(this).removeClass("ui-state-hover");
    });
 
  },
  textelements:function(element) {
 
    $(element).bind({
 
      focusin: function() {
        $(this).toggleClass('ui-state-focus');
      },
      focusout: function() {
        $(this).toggleClass('ui-state-focus');
      }
    });
 
  },
  buttons:function(element) {
    if ($(element).is(":submit")) {
      $(element).addClass("ui-priority-primary ui-corner-all ui-state-disabled hover");
      $(element).bind("click", function(event) {
        event.preventDefault();
      });
    }
    else if ($(element).is(":reset"))
      $(element).addClass("ui-priority-secondary ui-corner-all hover");
    $(element).bind('mousedown mouseup', function() {
      $(this).toggleClass('ui-state-active');
    }
 
      );
  },
 
  checkboxes:function(element) {
    $(element).parent("label").after("<span />");
    var parent = $(element).parent("label").next();
    $(element).addClass("ui-helper-hidden");
    parent.css({width:16,height:16,display:"block"});
 
    parent.wrap("<span class='ui-state-default ui-corner-all' style='display:inline-block;width:16px;height:16px;margin-right:5px;'/>");
 
    parent.parent().addClass('hover');
 
 
    parent.parent("span").click(function(event) {
      $(this).toggleClass("ui-state-active");
      parent.toggleClass("ui-icon ui-icon-check");
      $(element).click();
 
    });
 
  },
  radio:function(element) {
    $(element).parent("label").after("<span />");
    var parent = $(element).parent("label").next();
    $(element).addClass("ui-helper-hidden");
    parent.addClass("ui-icon ui-icon-radio-off");
    parent.wrap("<span class='ui-state-default ui-corner-all' style='display:inline-block;width:16px;height:16px;margin-right:5px;'/>");
 
    parent.parent().addClass('hover');
 
 
    parent.parent("span").click(function(event) {
      $(this).toggleClass("ui-state-active");
      parent.toggleClass("ui-icon-radio-off ui-icon-bullet");
      $(element).click();
 
    });
  },
  selector:function(element) {
    var parent = $(element).parent().first();
    parent.css({"display":"block",width:140,height:21}).addClass("ui-state-default ui-corner-all");
    $(element).addClass("ui-helper-hidden");
    parent.append("<span style='float:left;'></span><span style='float:right;display:inline-block' class='ui-icon ui-icon-triangle-1-s' ></span>");
    parent.after("<ul class=' ui-helper-reset ui-widget-content ui-helper-hidden' style='position:inherit;z-index:50;width:140px;' ></ul>");
   
    //Allow the label elements to be interact the keyboard
    $(parent).attr('tabindex','1');
   
    $.each($(element).find("option"), function() {
 
      $(parent).next("ul").append("<li class='hover' style='cursor: pointer'>" + $(this).html() + "</li>");
 
    });
   
    //if there is a value set, make it the default for the selector
    if ($(element).val() != '') {
                $(parent).children('span').first().html($(element).children("option:selected").text());
        }
   
    $(parent).next("ul").find("li").click(function() {
      $(parent).children('span').first().html($(this).html());
      $(element).val($(this).html());
      $(parent).next().slideToggle('fast');
    });
 
    $(parent).click(function(event) {
      $(this).next().slideToggle('fast');
      event.preventDefault();
    }).keydown(function(event) {
        //if the selection list isnt showing, show it.
        if($(parent).next("ul").css("display") =="none"){
            $(parent).click();
        }
        //if the enter key is pressed to select an item
        if (event.keyCode == '13' && $(parent).next("ul").find("li").hasClass("ui-state-hover")) {
               
                  //Find the element we want to select and assign it to useElement
                  useElement =  $(parent).next("ul").find("li.ui-state-hover");
                 
                  //Update the good looking label
                  $(parent).children('span').first().html($(useElement).html());
                 
                  //Update the real element
                  $(element).val($(useElement).html());
                 
                  //Hide the select
                  $(parent).next().slideToggle('fast');
        }
       
        //if key press is right or down arrow
        if (event.keyCode == '39'||event.keyCode == '40') {
            //check if we are already hovering over an item
            if(!$(parent).next("ul").find("li").hasClass("ui-state-hover")){
                //No items are being hovered over so lets add the hover class
                $(parent).next("ul").children("li").first().addClass("ui-state-hover");
            }else{
               
                //An item is being hovered over, lets get that item assign it to a variable
                rMe = $(parent).next("ul").find("li.ui-state-hover");
               
                //Get the next element that we can select and add the hover class
                $(parent).next("ul").find("li.ui-state-hover").next().addClass("ui-state-hover");
               
                //Remove the hover class from the old element
                $(rMe).removeClass("ui-state-hover");
            }
           
            event.preventDefault();
        }
       
        //if key press is left or up arrow
        if (event.keyCode == '38'||event.keyCode == '37') {
            //check if we are already hovering over an item
            if(!$(parent).next("ul").find("li").hasClass("ui-state-hover")){
                //No items are being hovered over so lets add the hover class
                $(parent).next("ul").children("li").first().addClass("ui-state-hover");
            }else{
                //An item is being hovered over, lets get that item assign it to a variable
                rMe = $(parent).next("ul").find("li.ui-state-hover");
               
                //Get the previous element that we can select and add the hover class
                $(parent).next("ul").find("li.ui-state-hover").prev().addClass("ui-state-hover");
               
                //Remove the hover class from the old element
                $(rMe).removeClass("ui-state-hover");
            }
            event.preventDefault();
        }
    });
 
  }
});
  
}( jQuery ));
