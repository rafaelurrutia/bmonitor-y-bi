<style>
.column { width: 270px; float: left; padding-bottom: 100px; }
.portlet { margin: 0 1em 1em 0; }
.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
.portlet-header .ui-icon { float: right; }
.portlet-content { padding: 0.4em; }
.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
.ui-sortable-placeholder * { visibility: hidden; }
</style>
<script language="JavaScript">

function saveOrder() { 
    $(".column").each(function(index, value){ 
        var colid = value.id; 
        var cookieName = "cookie-" + colid; 
        // Get the order for this column. 
        var order = $('#' + colid).sortable("toArray"); 
        // For each portlet in the column 
        for ( var i = 0, n = order.length; i < n; i++ ) { 
            // Determine if it is 'opened' or 'closed' 
            var v = $('#' + order[i] ).find('.portlet-content').is(':visible'); 
            // Modify the array we're saving to indicate what's open And 
            //  what's not. 
            order[i] = order[i] + ":" + v; 
        } 
        $.cookie(cookieName, order, { path: "/", expiry: new Date(2012, 1, 1)}); 
    }); 
} 

// function that restores the list order from a cookie 
function restoreOrder() { 
    $(".column").each(function(index, value) { 
        var colid = value.id; 
        var cookieName = "cookie-" + colid 
        var cookie = $.cookie(cookieName); 
        if ( cookie == null ) { return; } 
        var IDs = cookie.split(","); 
        for (var i = 0, n = IDs.length; i < n; i++ ) { 
            var toks = IDs[i].split(":"); 
            if ( toks.length != 2 ) { 
                continue; 
            } 
            var portletID = toks[0]; 
            var visible = toks[1] 
            var portlet = $(".column") 
                .find('#' + portletID) 
                .appendTo($('#' + colid)); 
            if (visible === 'false') { 
                portlet.find(".ui-icon").toggleClass("ui-icon-minus"); 
                portlet.find(".ui-icon").toggleClass("ui-icon-plus"); 
                portlet.find(".portlet-content").hide(); 
            } 
        } 
    }); 
} 
$(document).ready( function () { 
    $(".column").sortable({ 
        connectWith: ['.column']
    }); 

    $(".portlet") 
        .addClass("ui-widget ui-widget-content") 
        .addClass("ui-helper-clearfix ui-corner-all") 
        .find(".portlet-header") 
        .addClass("ui-widget-header ui-corner-all") 
        .prepend('<span class="ui-icon ui-icon-minus"></span>') 
        .end() 
        .find(".portlet-content"); 

    restoreOrder(); 

    $(".portlet-header .ui-icon").click(function() { 
        $(this).toggleClass("ui-icon-minus"); 
        $(this).toggleClass("ui-icon-plus"); 
        $(this).parents(".portlet:first").find(".portlet-content").toggle(); 
    }); 
    $(".portlet-header .ui-icon").hover( 
		function() {$(this).addClass("ui-icon-hover"); }, 
		function() {$(this).removeClass('ui-icon-hover'); } 
    ); 
}); 

$( "#perm_set" ).click(function() {
	$.post("/admin/setperm", $("#Permisos").serialize());
});

</script>
<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<button id="perm_set">Save</button>
</div>
				
<div class='container_columns' style="height: 1024px;"> 
	
<form id="Permissions" class="form_bsw">
	{columns}
</form>

</div>