/*
 * jQuery 1.9 support. browser object has been removed in 1.9 
 */
var browser = $.browser

if (!browser) {
    function uaMatch( ua ) {
        ua = ua.toLowerCase();

        var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
            /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
            /(msie) ([\w.]+)/.exec( ua ) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
            [];

        return {
            browser: match[ 1 ] || "",
            version: match[ 2 ] || "0"
        };
    };

    var matched = uaMatch( navigator.userAgent );
    browser = {};

    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }
}

jQuery(function($){
	$.datepicker.regional['es'] = {
	closeText: 'Cerrar',
	prevText: '&#x3c;Ant',
	nextText: 'Sig&#x3e;',
	currentText: 'Hoy',
	monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
	'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
	'Jul','Ago','Sep','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
	dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
	dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['es']);
});

function logoutClick() {
	$.ajax({
		type: "POST",
		url: "/login/logout",
		dataType: "json",
		data: {
			is_ajax: 1
		} ,
		success: function(response)
		{
			if(response.status)
				self.location=response.redirect;
			else {
				alert(response.msg);
			}		
		}
	});
	return false;
}

jQuery(function($){
   $.formVar = function(formid,grid) {

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
            this.tips = $('#' + formid + "_validateTips",grid);   
        } else {
            this.tips = $('#' + formid + "_validateTips"); 
        }   
    };
    
    $.fn.styleTable = function (options) {
        var defaults = {
            css: 'ui-styled-table'
        };
        options = $.extend(defaults, options);

        return this.each(function () {
            $this = $(this);
            $this.addClass(options.css);

            $this.on('mouseover mouseout', 'tbody tr', function (event) {
                $(this).children().toggleClass("ui-state-hover", event.type == 'mouseover');
            });

            $this.find("th").addClass("ui-state-default");
            $this.find("td").addClass("ui-widget-content");
            $this.find("tr:last-child").addClass("last-child");
        });
    }; 
});