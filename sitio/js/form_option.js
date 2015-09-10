function isNumber(event){
    var codigo = 0;
    if (event.keyCode == 0) {
        try {
            codigo = event.charCode;
        } catch (ex) { }
    } else {
        codigo = event.keyCode;
    }
    if((codigo == 8) || (codigo == 127) || (codigo == 9) || ((codigo >= 48)  && (codigo <= 57))) {
        //event.returnValue = true;
        //event.preventDefault();
    } else {
        event.returnValue = false;
        event.preventDefault();         
    }
}

function formVar(formid,grid) {

    grid = typeof grid !== 'undefined' ? grid : false;
    
    if(grid !== false) {
        this.form = $('#' + formid,grid);
    } else {
        this.form = $('#' + formid);
    }

    this.formid = formid;

    this.getVar = [];

    this.all = this.form.find(":input");

    this.value = function(secc) {
        return this.form.find("#" + secc).val();
    };

    this.get = function(secc) {
        return this.form.find("#" + secc);
    };
    
    this.getLabel = function(secc) {
        return this.form.find('label[for='+secc+']').text();
    };

    this.getAll = function() {
        return this.form.find(":input");
    };

    this.setVar = function(name, value) {
        this.getVar[name] = value;
    };

    this.refresh = function() {
         if(grid !== false) {
            this.form = $('#' + this.formid,grid);
            this.tips = $('#' + formid + "_validateTips",grid);               
         } else {
            this.form = $('#' + this.formid);
            this.tips = $('#' + formid + "_validateTips");             
         }
    };
    if(grid !== false) {
        this.tips = $('#' + formid + '_validateTips', grid);   
    } else {
        this.tips = $('#' + formid + '_validateTips'); 
    };
    
    this.checkLength = function(o, n, min, max) {
        o = this.get(o);
        if ( typeof o.val() == "undefined" || o.val().length > max || o.val().length < min) {
            o.addClass("ui-state-error");
            o.css("color", "#FFFFFF");
            
            if(n == 'auto'){
                n = this.getLabel();
            } 
            
            this.tipsBox("El largo del campo " + n + " debe estar entre " + min + " y " + max + "." , "alert");
            return false;
        } else {
            return true;
        }        
    }

    this.tipsBox = function(t, type) {
        
        if(type == 'alert') {
            $( "div" , this.tips).removeClass( "ui-state-highlight" ).addClass( "ui-state-error" );
            $( "div" , this.tips).html('<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alerta!</strong> '+t+'.</p>');
        } else {
            $( "div" , this.tips).removeClass( "ui-state-error" ).addClass( "ui-state-highlight" );
            $( "div" , this.tips).html('<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span><strong>Mensaje!</strong> '+t+'.</p>');
        }
        
        var options = {};
        
        $( "div" , this.tips).show( "shake", options, 500, this.callback );
    }
    
    this.callback = function(t, type) { 
        setTimeout(function() {
            $('#' + formid + '_validateTips div:visible').removeAttr( "style" ).fadeOut();
            $( "input" , this.form).removeClass("ui-state-error").css("color", "#5A698B");
        }, 5000);
    };
    
};

jQuery(function($) {

    $.checkLength = function(tips, o, n, min, max) {

        if ( typeof o.val() == "undefined" || o.val().length > max || o.val().length < min) {
            o.addClass("ui-state-error");
            o.css("color", "#FFFFFF");
            $.updateTips(tips, "El largo del campo " + n + " debe estar entre " + min + " y " + max + ".");
            return false;
        } else {
            return true;
        }
    }
    
    $.checkNumeric = function(tips, o, n) {
        if ( typeof $(o).val() == "undefined" || isNaN($(o).val()) == true ) {
            $(o).addClass("ui-state-error");
            $(o).css("color", "#FFFFFF");
            $.updateTips(tips, "El campo  '" + n + "' no es numerico.");
            return false;
        } else {
            return true;
        }
       
    }

    $.checkInputFile = function(tips, o, n, array) {

        var ext = o.val().split('.').pop().toLowerCase();

        if ($.inArray(ext, [array]) == -1) {
            o.addClass("ui-state-error");
            o.css("color", "#FFFFFF");
            $.updateTips(tips, "El campo  " + n + " se enceuntra vacio o contiene un archivo con ext incorrecta");
            return false;
        } else {
            return true;
        }
    }

    $.checkLength2 = function(tips, o, n, min, max) {

        if ( typeof $(o).val() == "undefined" || $(o).val().length > max || $(o).val().length < min) {
            $(o).addClass("ui-state-error");
            $(o).css("color", "#FFFFFF");
            $.updateTips2(tips, "El largo del campo " + n + " debe estar entre " + min + " y " + max + ".");
            return false;
        } else {
            return true;
        }
    }

    $.updateTips = function(o, t) {
        o.text(t).addClass("ui-state-highlight");
        console.log("Valid Form: " + t);
        setTimeout(function() {
            o.removeClass("ui-state-highlight", 1500);
        }, 500);
    }
    
    $.tipsBox = function(o, t, type) {
        
        if(type == 'alert') {
            $( "div" , o).removeClass( "ui-state-highlight" ).addClass( "ui-state-error" );
            $( "div" , o).text('<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alerta!</strong>'+t+'.</p>');
        } else {
            $( "div" , o).removeClass( "ui-state-error" ).addClass( "ui-state-highlight" );
            $( "div" , o).text('<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span><strong>Mensaje!</strong>'+t+'.</p>');
        }
        var options = {};
         
        setTimeout(function() {
          o.hide( "clip" );
        }, 500);
    }

    $.updateTips2 = function(o, t) {
        $(o).text(t).addClass("ui-state-highlight");
        setTimeout(function() {
            $(o).removeClass("ui-state-highlight", 1500);
        }, 500);
    }

    $.checkSelect = function(tips, o, n, not) {
        if ((o.val() == not) || (o.val() == null)) {
            $.updateTips(tips, "Seleccione un valor en el campo " + n + ".");
            return false;
        } else {
            return true;
        }
    };

    $.checkSelect2 = function(tips, o, n, not) {
        if ($(o).val() == not) {
            $.updateTips2(tips, "Seleccione un valor en el campo " + n + ".");
            return false;
        } else {
            return true;
        }
    };

    $.checkPass = function(tips, val1, val2) {
        if (val1.val() != val2.val()) {
            val1.addClass("ui-state-error");
            val2.addClass("ui-state-error");
            $.updateTips(tips, "El campos Password es distinto al campo Confirmación");
            return false;
        } else {
            return true;
        }
    }

    $.checkPass2 = function(tips, val1, name1, val2, name2) {
        if (val1.val() != val2.val()) {
            val1.addClass("ui-state-error");
            val2.addClass("ui-state-error");
            $.updateTips(tips, "El campos '" + name1 + "' es distinto al campo '" + name2 + "'");
            return false;
        } else {
            return true;
        }
    }

    $.checkRegexp = function(tips, o, regexp, n) {
        if (!( regexp.test(o.val()) )) {
            o.addClass("ui-state-error");
            $.updateTips(tips, n);
            return false;
        } else {
            return true;
        }
    }
    var max = 100;

    $.deleteLista1 = function($item, $lista) {
        $item.fadeOut(function() {
            $($item).appendTo($lista).fadeIn();
            ;
        });
        $item.fadeIn();
    }

    $.deleteLista2 = function($item, $lista) {
        $item.fadeOut(function() {
            $item.appendTo($lista).fadeIn();
        });
    }

    $.passwordStrength = function(password) {
        var desc = new Array();
        desc[0] = "Very weak";
        desc[1] = "Weak";
        desc[2] = "Best";
        desc[3] = "Lot better";
        desc[4] = "Strong";
        desc[5] = "Very Strong";

        var score = 0;

        //if password bigger than 6 give 1 point
        if (password.length > 6)
            score++;

        //if password has both lower and uppercase characters give 1 point
        if (( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ))
            score++;

        //if password has at least one number give 1 point
        if (password.match(/\d+/))
            score++;

        //if password has at least one special caracther give 1 point
        if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
            score++;

        //if password bigger than 12 give another 1 point
        if (password.length > 12)
            score++;

        document.getElementById("passwordDescription").innerHTML = desc[score];
        document.getElementById("passwordStrength").className = "strength" + score;
    }
});

(function($) {
	
	$.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<span>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );
 
        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
      },
 
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
 
        this.input = $( "<input>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" )
          })
          .tooltip({
            tooltipClass: "ui-state-highlight"
          });
 
        this._on( this.input, {
          autocompleteselect: function( event, ui ) {
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },
 
          autocompletechange: "_removeIfInvalid"
        });
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $( "<a>" )
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .mousedown(function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .click(function() {
            input.focus();
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },
 
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _removeIfInvalid: function( event, ui ) {
 
        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }
 
        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });
 
        // Found a match, nothing to do
        if ( valid ) {
          return;
        }
 
        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.data( "ui-autocomplete" ).term = "";
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
})(jQuery); 