<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
        <title>bMonitor</title>
        <meta name="description" content="bMonitor Suite es un avanzado sistema de que permite medir o estimar una serie de parámetros relevantes en diferentes puntos de interés dentro de la red IP de un operador cualquiera">
        <meta name="author" content="Baking">

        <link rel="stylesheet" type="text/css" href="/sitio/css/layout.css">
        <link rel="stylesheet" type="text/css" href="/sitio/css/index.css">
        
        <!--[if IE 7]>
        <link rel="stylesheet" href="/sitio/assets/css/font-awesome-ie7.min.css">
        <![endif]-->
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="/sitio/assets/js/vendor/html5shiv.js" type="text/javascript"></script>
        <script src="/sitio/assets/js/vendor/respond.min.js" type="text/javascript"></script>
        <![endif]-->
        <!--<script type="text/javascript" src="/sitio/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="/sitio/js/jquery-ui-1.10.3.custom.min.js"></script> 
        <script type="text/javascript" src="/sitio/js/jquery.themeswitcher.min.js"></script>-->
        <script type="text/javascript" src="/sitio/js/libsHeader.js"></script><style type="text/css"></style>
    <link rel="stylesheet" type="text/css" href="/sitio/css/flexigrid.theme.css?v=1">
    <script type="text/javascript" src="/sitio/js/flexigrid.theme.js?v=4"></script>
    <script type="text/javascript" src="/sitio/js/form_option.js"></script>
    <link rel="stylesheet" type="text/css" href="/sitio/css/form.css">
    <script>
    $(function() {
        var tabs_configuracion = $("#tabs").tabs({
        beforeLoad: function (event, ui) {
            
            //$('.flexigrid').remove();
            
            if (ui.tab.data("loaded")) {
                //event.preventDefault();
                return;
            }
            
            $('a', ui.tab).click(function() {
                    $(ui.panel).load(this.href);
                    return true;
            });
                
    
            ui.jqXHR.success(function () {
                ui.tab.data("loaded", false);
            });
    
            ui.jqXHR.error(function () {
                ui.panel.html(
                    "Problemas tecnicos. " +
                    "Por favor , reportar a baking.");
            });
        }
        });
        
        
    });
    </script>
<link type="text/css" rel="stylesheet" href="/sitio/css/themes/1.10.3/cupertino/jquery-ui.css"></head>

<body style="cursor: default;">
    <div class="container">
        <div id="header">
    <div id="toppanel">
        
        <div id="tabs_menu" style="height: 35px" class="tabs_menu-bottom ui-tabs ui-widget ui-widget-content ui-corner-all">
            
            <ul style="float: left" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-corner-bottom">
                <li><a class="ui-state-default ui-corner-all " href="/">Monitoring</a></li><li><a class="ui-state-default ui-corner-all " href="/inventario">Inventory</a></li><li><a class="ui-state-default ui-corner-all " href="/qos">QoS</a></li><li><a class="ui-state-default ui-corner-all " href="/neutralidad">Neutrality</a></li><li><a class="ui-state-default ui-corner-all  ui-state-active" href="/config">Configuration</a></li><li><a class="ui-state-default ui-corner-all " href="/admin">Admistration</a></li>
            </ul>
            
            <ul style="float: right" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix  ui-corner-all">
                <li style="float: right;">
                    <a class="ui-state-default ui-corner-all" href="#" onclick="logoutClick()" id="logout">Exit</a>                    
                </li>
                <li style="float: right;">
                    <a class="ui-state-default ui-corner-all" href="/login/perfil">Profile</a>
                </li>
                <li style="float: right;">
                    <a class="ui-state-default ui-corner-all" href="#">Welcome Administrator</a>
                </li>
            </ul>
            
        </div>
        
    </div>
</div>

        <div id="content">
            <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all"><ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist"><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-1" aria-labelledby="ui-id-1" aria-selected="false"><a href="/config/cfgSondas" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-1">Agents</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-2" aria-labelledby="ui-id-2" aria-selected="false"><a href="/config/cfgMonitores" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-2">Monitors</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-3" aria-labelledby="ui-id-3" aria-selected="false"><a href="/config/cfgGraph" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-3">Charts</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-4" aria-labelledby="ui-id-4" aria-selected="false"><a href="/configScreen/cfgPantallas" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-4">Screens</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-5" aria-labelledby="ui-id-5" aria-selected="false"><a href="/config/cfgPlanes" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-5">Plan</a></li><li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="ui-tabs-6" aria-labelledby="ui-id-6" aria-selected="true"><a href="/config/cfgProfiles" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-6">Profile</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-7" aria-labelledby="ui-id-7" aria-selected="false"><a href="/config/cfgGrupos" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-7">Groups</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-8" aria-labelledby="ui-id-8" aria-selected="false"><a href="/config/cfgUbicacion" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-8">Location</a></li><li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="ui-tabs-9" aria-labelledby="ui-id-9" aria-selected="false"><a href="/config/getMaps" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-9">Mapa</a></li></ul><div id="ui-tabs-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"><link rel="stylesheet" type="text/css" href="/sitio/css/jquery.tagit.css">
<script type="text/javascript" src="/sitio/js/ui.selectmenu.js"></script>
<script type="text/javascript" src="/sitio/js/tag-it.js"></script>
<script type="text/javascript" src="/sitio/js/jquery.ui.form.js"></script>
<script type="text/javascript" src="/sitio/js/view.config_equipos.js"></script>
<script type="text/javascript">
jQuery(function($){
    //Tabla 
    $(".sondas").flexigrid({
        url: '/config/getSondas',
        title: '',
        dataType: 'json',
        colModel : [
            {display: 'Agent', name : 'host' , width : '200'  , sortable : true, align: 'left'},{display: 'DNS', name : 'dns' , width : '150'  , sortable : true, align: 'left'},{display: 'IP', name : 'ip_wan' , width : '100'  , sortable : true, align: 'left'},{display: 'Group', name : 'grupo' , width : '100'  , sortable : true, align: 'left'},{display: 'Status', name : 'estado' , width : '30'  , sortable : false, align: 'left'},{display: 'Availability', name : 'availability' , width : '100'  , sortable : false, align: 'left'},{display: 'Options', name : 'opciones' , width : '256'  , sortable : true, align: 'left'}
        ],
        buttons : [
            {name: 'New', bclass: 'add', onpress : toolboxSonda},
            {name: 'Delete', bclass: 'delete', onpress : toolboxSonda},
            {separator: true},
            {name: 'Configurar', bclass: 'config', onpress : toolboxSonda},
            {separator: true}
        ],
        usepager: true,
        useRp: true,
        rp: 40,
        sortname: "id_host",
        sortorder: "asc",
        showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            $('.sondas').flexOptions({params: [{name:'callId', value:'sondas'}].concat($('#sondasFilter').serializeArray())});
            return true;
        },
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    });

    //Filtros de la Tabla

    $("#fm_sondas_Grupo").change(function(){
        $(".sondas").flexReload();
    }); 
});

function toolboxSonda(com, grid) {
    if (com == 'New') {
        $( "#modal_sonda_form" ).dialog( "open" );
        $( "#tabs_form_new_sonda" ).tabs( "option", "active", 0 );
    } else if (com == 'Delete') {
        lengthSelect = $('.trSelected', grid).length;
        if(lengthSelect > 0) {
            $( "#sondas-delete" ).dialog( "open" );
        }
    } else if (com == 'Configurar') {
        lengthSelect = $('.trSelected', grid).length;
        if(lengthSelect > 0) {
            $( "#sonda-config" ).dialog( "open" );
        }
    }
}

</script>






<div id="modal_sonda_edit" title="Edit agent">
</div>



<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading">
</div> 
    
<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<form id="sondasFilter">
    <fieldset>
        <label for="fm_sondas_Grupo" accesskey="g">Group</label>
        <select name="fm_sondas_Grupo" id="fm_sondas_Grupo">
            <option value="1">NEUTRALITY</option><option value="20">QoE</option><option value="21">QoE Mobile</option><option value="0">All</option>
        </select>
    </fieldset>
</form>
</div>
<div class="flexigrid"><div class="nBtn" title="Hide/Show Columns" style="display: none;"><div></div></div><div class="nDiv" style="margin-bottom: -200px; display: none; top: 52px; height: auto; width: auto;"><table cellpadding="0" cellspacing="0"><tbody><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="0"></td><td class="ndcol2">Agent</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="1"></td><td class="ndcol2">DNS</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="2"></td><td class="ndcol2">IP</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="3"></td><td class="ndcol2">Group</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="4"></td><td class="ndcol2">Status</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="5"></td><td class="ndcol2">Availability</td></tr><tr><td class="ndcol1"><input type="checkbox" checked="checked" class="togCol" value="6"></td><td class="ndcol2">Options</td></tr></tbody></table></div><div class="tDiv ui-state-default"><div class="tDiv2"><button class="fbuttonTheme ui-button ui-widget ui-state-default ui-corner-left ui-button-text-icon-primary" name="New"><span class="ui-button-icon-primary ui-icon ui-icon-document"></span><span class="ui-button-text">New</span></button><button class="fbuttonTheme ui-button ui-widget ui-state-default  ui-button-text-icon-primary" name="Delete"><span class="ui-button-icon-primary ui-icon ui-icon-trash"></span><span class="ui-button-text">Delete</span></button><button class="fbuttonTheme ui-button ui-widget ui-state-default  ui-button-text-icon-primary" name="Configurar"><span class="ui-button-icon-primary ui-icon config"></span><span class="ui-button-text">Configurar</span></button></div><div style="clear:both"></div></div><div class="hDiv"><div class="hDivBox"><table cellpadding="0" cellspacing="0"><thead><tr><th axis="col0" abbr="host" align="left"><div style="text-align: left; width: 200px;">Agent</div></th><th axis="col1" abbr="dns" align="left"><div style="text-align: left; width: 150px;">DNS</div></th><th axis="col2" abbr="ip_wan" align="left"><div style="text-align: left; width: 100px;">IP</div></th><th axis="col3" abbr="grupo" align="left"><div style="text-align: left; width: 100px;">Group</div></th><th axis="col4" align="left"><div style="text-align: left; width: 30px;">Status</div></th><th axis="col5" align="left"><div style="text-align: left; width: 100px;">Availability</div></th><th axis="col6" abbr="opciones" align="left"><div style="text-align: left; width: 256px;">Options</div></th></tr></thead></table></div></div><div class="cDrag" style="top: 28px;"><div style="height: 224px; display: block; left: 210px;"></div><div style="height: 224px; display: block; left: 371px;"></div><div style="height: 224px; display: block; left: 482px;"></div><div style="height: 224px; display: block; left: 593px;"></div><div style="height: 224px; display: block; left: 634px;"></div><div style="height: 224px; display: block; left: 745px;"></div><div style="height: 224px; display: block; left: 1012px;"></div></div><div class="bDiv" style="height: 200px;"><table class="sondas" cellpadding="0" cellspacing="0" border="0"><tbody></tbody></table><div class="iDiv" style="display: none;"></div></div><div class="pDiv fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix"><div class="pDiv2"><div class="pGroup"><select name="rp"><option value="10">10&nbsp;&nbsp;</option><option value="15">15&nbsp;&nbsp;</option><option value="20">20&nbsp;&nbsp;</option><option value="30">30&nbsp;&nbsp;</option><option value="50">50&nbsp;&nbsp;</option></select></div> <div class="btnseparator"></div> <div class="pGroup"> <button id="beginning" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only pFirst pButton" role="button" aria-disabled="false" title="go to beginning"><span class="ui-button-icon-primary ui-icon ui-icon-seek-start"></span><span class="ui-button-text">go to beginning</span></button><button id="rewind" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only pPrev pButton" role="button" aria-disabled="false" title="rewind"><span class="ui-button-icon-primary ui-icon ui-icon-seek-prev"></span><span class="ui-button-text">rewind</span></button> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pcontrol">Page <input type="text" size="4" value="1"> of <span>0</span></span></div> <div class="btnseparator"></div> <div class="pGroup"> <button id="forward" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only pNext pButton" role="button" aria-disabled="false" title="fast forward"><span class="ui-button-icon-primary ui-icon ui-icon-seek-next"></span><span class="ui-button-text">fast forward</span></button><button id="end" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only pLast pButton" role="button" aria-disabled="false" title="go to end"><span class="ui-button-icon-primary ui-icon ui-icon-seek-end"></span><span class="ui-button-text">go to end</span></button> </div> <div class="btnseparator"></div> <div class="pGroup"> <button id="refresh" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only pReload pButton" role="button" aria-disabled="false" title="refresh"><span class="ui-button-icon-primary ui-icon ui-icon-refresh"></span><span class="ui-button-text">refresh</span></button> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pPageStat">Displaying 1 to 0 of 0 items</span></div></div><div style="clear:both"></div></div><div class="vGrip"><span></span></div></div></div><div id="ui-tabs-2" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-2" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-3" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-3" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-4" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-4" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-5" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-5" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-6" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-6" role="tabpanel" aria-expanded="true" aria-hidden="false" style="display: block;"><link rel="stylesheet" type="text/css" href="/sitio/css/jquery.uix.multiselect.css">
<script type="text/javascript" src="/sitio/js/view.config_profiles2.js"></script> 
<script type="text/javascript" src="/sitio/js/jquery.uix.multiselect.min.js"></script>
<script type="text/javascript">
                
    $("#tableProfile").flexigrid({
        url: '/profiles/getTable',
        title: 'Profile',
        dataType: 'json',
        idFlexigrid: 'tableProfile',
        colModel : [
            {display: 'Name', name : 'name'  , width : '100'  , sortable : false , align: 'left'}
,{display: 'Group', name : 'grupo'  , width : '100'  , sortable : false , align: 'left'}
,{display: 'Action', name : 'action'  , width : '380'  , sortable : false , align: 'left'}

        ],
        buttons : [
            buttons : [  {name: 'New', bclass: 'add', onpress : toolboxProfiles},
    {name: 'Delete', bclass: 'delete', onpress : toolboxProfiles} ],
        ],
        
        usepager: true,
        useRp: true,
        rp: 15,
         showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            $('.tableProfile').flexOptions({params: [{
                       name:'callId', 
                       value:'tableProfile'
            }].concat($('#tableProfileFilter').serializeArray())});
            return true;
        },
        
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    }); 

        function toolboxProfiles(com, grid) {
            if (com == 'New') {
                modalProfileNew.dialog("open");
            } else if (com == 'Delete') {
                lengthSelect = $('.trSelected', grid).length;
                if (lengthSelect > 0) {
                    modalProfileDelete.dialog("open");
                }
            }
        }
</script>

<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading">
</div>















<div id="dialogGenerator">

</div>

<div id="tableProfile">
    <!-- Tabla de perfiles -->
</div>

<div id="containerProfile">

</div></div><div id="ui-tabs-7" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-7" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-8" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-8" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div><div id="ui-tabs-9" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="ui-id-9" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;"></div></div>
        </div>
        <div class="push"></div>
    </div>
    <div class="footer ui-widget-content ui-state-default">
    <a href="#">Copyright © Baking, 2013</a>
    <div class="logo">
                                            <img src="/sitio/img/logobmonitor2.png" alt="Baking" width="180">
                                        </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
$.themeSwitches();
});
</script>

<ul class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all" id="ui-id-12" tabindex="0" style="display: none;"></ul><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modal_sonda_form" style="display: none; position: absolute;" aria-labelledby="ui-id-13"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-13" class="ui-dialog-title">Provision a new agent</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modal_sonda_form" class="ui-dialog-content ui-widget-content">
    <style type="text/css">
    ul.ui-tabs-nav .ui-state-disabled {
        display: none; /* disabled tabs don't show up */
    }
</style>
<p id="form_new_sonda_validateTips" class="validateTips">
    All form elements are required.
</p>
<div id="tabs_form_new_sonda" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
    <form id="form_new_sonda" class="formUI">

        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">
            <li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="form_new_sonda_tabs_1" aria-labelledby="ui-id-10" aria-selected="true">
                <a href="#form_new_sonda_tabs_1" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-10">General</a>
            </li>
            <li class="ui-state-default ui-corner-top ui-state-disabled" role="tab" tabindex="-1" aria-controls="form_new_sonda_tabs_2" aria-labelledby="ui-id-11" aria-selected="false" aria-disabled="true">
                <a href="#form_new_sonda_tabs_2" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-11">Configuration</a>
            </li>
        </ul>

        <div id="form_new_sonda_tabs_1" aria-labelledby="ui-id-10" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">

            <fieldset class="ui-widget-content ui-corner-all">
                <div class="row">
                    <label for="sonda_name" class="col1">Name</label>
                    <span class="col2">
                        <input type="text" style="width: 90%;" name="sonda_name" id="sonda_name" value="" class="input ui-widget-content ui-corner-all">
                    </span>
                </div>
                <div id="secc_identificator" style="display: none" class="row">
                    <label for="sonda_identificator" class="col1">Identifier</label>
                    <span class="col2">
                        <input type="text" name="sonda_identificator" id="sonda_identificator" value="" class="input ui-widget-content ui-corner-all">
                    </span>
                </div>
                <div class="row" style="">
                    <label for="sonda_codigosonda" class="col1">Agent Code</label>
                    <span class="col2">
                        <input type="text" name="sonda_codigosonda" id="sonda_codigosonda" value="" class="input ui-widget-content ui-corner-all">
                    </span>
                </div>
                <div class="row">
                    <label for="sonda_group" class="col1">Group</label>
                    <span class="col2">
                        <select name="sonda_group" id="sonda_group" value="" class="select">
                            <option selected="" value="0">Select</option><option value="1">NEUTRALITY</option><option value="20">QoE</option><option value="21">QoE Mobile</option>
                        </select></span>
                </div>
                <div id="secc_profile" style="display: none" class="row">
                    <label for="sonda_profile" class="col1">Profile</label>
                    <span class="col2">
                        <select name="sonda_profile" id="sonda_profile" value="" class="select">
                            
                        </select></span>
                </div>
                <div id="secc_plan" style="display: none" class="row">
                    <label for="sonda_plan" class="col1">Plan</label>
                    <span class="col2">
                        <select name="sonda_plan" id="sonda_plan" class="select">
                            <option value="0">Select a group</option>
                        </select> </span>
                </div>
                <div id="secc_dns" class="row">
                    <label for="sonda_dns" class="col1">ID [BSW[DNS]]</label>
                    <span class="col2">
                        <input type="text" name="sonda_dns" id="sonda_dns" value="" class="input ui-widget-content ui-corner-all" disabled="">
                    </span>
                </div>
                <div id="secc_ip_wan" class="row">
                    <label for="sonda_ip_wan" class="col1">Address IP WAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_ip_wan" id="sonda_ip_wan" value="" class="input ui-widget-content ui-corner-all" maxlength="15" size="15">
                    </span>
                </div>
                <div id="secc_ma_wan" class="row">
                    <label for="sonda_mac_wan" class="col1">Mac WAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_mac_wan" id="sonda_mac_wan" style="text-transform:uppercase;" value="" class="input ui-widget-content ui-corner-all">
                    </span>
                </div>
                <div id="secc_lanconfig" class="row" style="display: none;">
                    <label for="sonda_ip_lan" class="col1">Address IP - LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_ip_lan" id="sonda_ip_lan" value="192.168.1.1" class="input ui-widget-content ui-corner-all" maxlength="15" size="15">
                    </span>
                </div>
                <div id="secc_lanconfig" class="row" style="display: none;">
                    <label for="sonda_netmask_lan" class="col1">Netmask - LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_netmask_lan" id="sonda_netmask_lan" value="255.255.255.0" class="input ui-widget-content ui-corner-all" maxlength="15" size="15">
                    </span>
                </div>
                <div id="secc_lanconfig" class="row" style="display: none;">
                    <label for="sonda_mac_lan" class="col1">Mac LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_mac_lan" id="sonda_mac_lan" value="" style="text-transform:uppercase;" class="input ui-widget-content ui-corner-all">
                    </span>
                </div>
                <div class="row">
                    <label for="sonda_tags" class="col1">Tags</label>
                    <span class="col2">
                        <input name="sonda_tags" id="sonda_tags" value="" style="display: none;"><ul class="tagit ui-widget ui-widget-content ui-corner-all"><li class="tagit-new"><span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span><input type="text" class="ui-widget-content ui-autocomplete-input" autocomplete="off"></li></ul>
                    </span>
                </div>
            </fieldset>

        </div>

        <div id="form_new_sonda_tabs_2" aria-labelledby="ui-id-11" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
            <fieldset class="ui-widget-content ui-corner-all">
                <div class="row"><label for="cfg_3" class="col1">WiFi</label><span class="col2"><input type="checkbox" class="checkbox" name="cfg_3" value="1"></span></div><div class="row"><label for="cfg_9" class="col1">Channel</label><span class="col2"><select name="cfg_9" id="cfg_9" class="select"><option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option selected="" value="6">6</option>
<option value="7">7</option>
<option value="8">8</option>
<option value="9">9</option>
<option value="10">10</option>
<option value="11">11</option>
</select></span></div><div class="row"><label for="cfg_4" class="col1">SSID</label><span class="col2"><input type="text" name="cfg_4" id="cfg_4" value="enlaces" class="input ui-widget-content ui-corner-all"></span></div><div class="row"><label for="cfg_11" class="col1">WiFi KEY</label><span class="col2"><input type="text" name="cfg_11" id="cfg_11" value="enlacesbsw" class="input ui-widget-content ui-corner-all"></span></div><div class="rowC"><label for="cfg_10" class="col1">Block IP</label><span class="col2"><textarea class="textarea ui-widget-content ui-corner-all" name="cfg_10" cols="50" rows="7"></textarea></span></div><div class="row"><label for="cfg_5" class="col1">Control P.</label><span class="col2"><input type="checkbox" class="checkbox" name="cfg_5" value="1"></span></div><div class="row"><label for="cfg_12" class="col1">Time Zone</label><span class="col2"><select name="cfg_12" id="cfg_12" class="select"><option value="GMT-11">GMT-11</option>
<option value="GMT-10">GMT-10</option>
<option value="GMT-9">GMT-9</option>
<option value="GMT-8">GMT-8</option>
<option value="GMT-7">GMT-7</option>
<option value="GMT-6">GMT-6</option>
<option value="GMT-5">GMT-5</option>
<option value="GMT-4">GMT-4</option>
<option selected="" value="GMT-3">GMT-3</option>
<option value="GMT-2">GMT-2</option>
<option value="GMT-1">GMT-1</option>
<option value="GMT">GMT</option>
<option value="GMT1">GMT1</option>
<option value="GMT2">GMT2</option>
<option value="GMT3">GMT3</option>
<option value="GMT4">GMT4</option>
<option value="GMT5">GMT5</option>
<option value="GMT6">GMT6</option>
<option value="GMT7">GMT7</option>
<option value="GMT8">GMT8</option>
<option value="GMT9">GMT9</option>
<option value="GMT10">GMT10</option>
<option value="GMT11">GMT11</option>
</select></span></div><input type="hidden" name="cfg_13" id="cfg_13" value="mixed-psk+tkip+aes" class="input ui-widget-content ui-corner-all"><input type="hidden" name="cfg_8" id="cfg_8" value="ap" class="input ui-widget-content ui-corner-all">
            </fieldset>
        </div>

    </form>
</div>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Crear sonda</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" tabindex="-1" role="dialog" aria-describedby="sondas-delete" aria-labelledby="ui-id-14" style="display: none;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-14" class="ui-dialog-title">Remove agente(s)</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="sondas-delete" class="ui-dialog-content ui-widget-content">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure?, Will delete the following items:</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">Selected: </p>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">OK</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" tabindex="-1" role="dialog" aria-describedby="sonda-config" aria-labelledby="ui-id-15" style="display: none;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-15" class="ui-dialog-title">Configure Agent(s)??</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="sonda-config" class="ui-dialog-content ui-widget-content">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Want to submit to configure the agent or agents. Are you sure?</p>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Configurar las sondas seleccionadas</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" tabindex="-1" role="dialog" aria-describedby="modal_sonda_trigger" aria-labelledby="ui-id-16" style="display: none;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-16" class="ui-dialog-title">Run trigger</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modal_sonda_trigger" class="ui-dialog-content ui-widget-content">
    <form id="config_equipos_trigger" class="form_sonda">   
    <p id="config_equipos_trigger_validateTips" class="validateTips">Todo los elementos del formulario son requeridos.</p>  
    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="trigger_responsable" class="col1">Responsable</label>
            <span class="col2"><input type="text" name="trigger_responsable" id="trigger_responsable" class="input ui-widget-content ui-corner-all"></span>
        </div>
        <div class="row">
            <label for="trigger_id" class="col1">Trigger</label>
            <span class="col2"><select id="trigger_id" name="trigger_id" class="input ui-widget-content ui-corner-all">
                <option value="0">Selecccionar</option>
                <option value="ssh">SSH Revesa (Install)</option>
                <option value="ssh_reverse">SSH Revesa (Iniciar)</option>
                <option value="reboot">Reiniciar</option>
                <option value="bamrescue">BAM Rescue</option>
                <option value="upgrade">Upgrade</option>
            </select></span>
        </div>
    </fieldset>
</form>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Iniciar Trigger</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" tabindex="-1" role="dialog" aria-describedby="modalProfileNew" style="display: none;" aria-labelledby="ui-id-17"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-17" class="ui-dialog-title">Nuevo perfil</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalProfileNew" class="ui-dialog-content ui-widget-content">
    <form id="formNewProfile" class="formUI">
    
    <div id="formNewProfile_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            Todo los elementos del formulario son requeridos.</p>
        </div>
    </div>


    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="name" class="col1">Nombre</label>
            <span class="col2 set pmedium">
                <input type="text" name="name" id="name" value="" class="input ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="groupid" class="col1">Grupos: (Arrastre los grupos a la izquierda para asignar)</label>     
        </div>
        <div class="row multiselectContainer ui-helper-clearfix">
            <select id="groupid" class="multiselect" multiple="multiple" name="groupid[]">
                <option value="1">NEUTRALITY</option><option value="20">QoE</option><option value="21">QoE Mobile</option>
            </select>
        </div>
    </fieldset>

</form>

</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Crear perfil</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" tabindex="-1" role="dialog" aria-describedby="modalProfileDelete" aria-labelledby="ui-id-18" style="display: none;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-18" class="ui-dialog-title">Borrar Perfil(es)?</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalProfileDelete" class="ui-dialog-content ui-widget-content">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El o los perfiles(s) seleccionado(s) se borrará(n). ¿Estás seguro?
    </p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">Seleccionadas: </p>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Borrar</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modalStructureNew" aria-labelledby="ui-id-19" style="display: none; position: absolute;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-19" class="ui-dialog-title">Nueva categoria</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalStructureNew" class="ui-dialog-content ui-widget-content">
    <form id="formStructureNew" class="formUI">
    <p id="formStructureNew_validateTips" class="validateTips">
        Todo los elementos del formulario son requeridos.
    </p>

    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="name" class="col1">Nombre</label>
            <span class="col2 short">
                <input type="text" name="name" id="name" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="class" class="col1">Clase</label>
            <span class="col2 short">
                <input type="text" name="class" id="class" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
    </fieldset>

</form>

</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Crear perfil</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modalStructureDelete" aria-labelledby="ui-id-20" style="display: none; position: absolute;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-20" class="ui-dialog-title">Borrar Categoria(es)?</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalStructureDelete" class="ui-dialog-content ui-widget-content">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El o las categoria(s) seleccionada(s) se borrará(n). ¿Estás seguro?
    </p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">Seleccionadas: </p>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Borrar</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modalStructureItemNewParam" aria-labelledby="ui-id-21" style="display: none; position: absolute;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-21" class="ui-dialog-title">Nuevo Parametro</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalStructureItemNewParam" class="ui-dialog-content ui-widget-content">
    <form id="formNewItemParam" class="formUI">
    <p id="formNewItemParam_validateTips" class="validateTips">
        Todo los elementos del formulario son requeridos.
    </p>

    <fieldset class="ui-widget-content ui-corner-all">
        <input type="hidden" name="type" id="type" value="param">
        <div class="row">
            <label for="name" class="col1">Nombre</label>
            <span class="col2 short">
                <input type="text" name="name" id="name" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="display" class="col1">Display</label>
            <span class="col2 short">
                <input type="text" name="display" id="display" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="typeData" class="col1">Tipo dato</label>
            <span class="col2">
                <select name="typeData" id="typeData" class="select">
                    <option value="float">float</option><option selected="" value="string">string</option><option value="text">text</option>
                </select></span>
        </div>
        <div class="row">
            <label for="default" class="col1">Valor por defecto</label>
            <span class="col2 short">
                <input type="text" name="default" id="default" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
    </fieldset>

</form>

</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Crear perfil</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modalStructureItemNewMonitor" aria-labelledby="ui-id-22" style="display: none; position: absolute;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-22" class="ui-dialog-title">Nuevo Monitor</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalStructureItemNewMonitor" class="ui-dialog-content ui-widget-content">
    <form id="formNewItemMonitor" class="formUI">
    <p id="formNewItemMonitor_validateTips" class="validateTips">
        Todo los elementos del formulario son requeridos.
    </p>
    <fieldset class="ui-widget-content ui-corner-all">
        <input type="hidden" name="type" id="type" value="result">
        <div class="row">
            <label for="name" class="col1">Nombre</label>
            <span class="col2 short">
                <input type="text" name="name" id="name" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="display" class="col1">Display</label>
            <span class="col2 short">
                <input type="text" name="display" id="display" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="typeData" class="col1">Tipo dato</label>
            <span class="col2">
                <select name="typeData" id="typeData" class="select">
                    <option value="float">float</option><option selected="" value="string">string</option><option value="text">text</option>
                </select></span>
        </div>
        <div class="row">
            <label for="default" class="col1">Valor por defecto</label>
            <span class="col2 short">
                <input type="text" name="default" id="default" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="unit" class="col1">Unidad</label>
            <span class="col2 short">
                <input type="text" name="unit" id="unit" value="" class="input input-long ui-widget-content ui-corner-all">
            </span>
        </div>
        <div class="row">
            <label for="report" class="col1">Reporte QoS</label>
            <span class="col2 short reportCheck" style="padding: 4px;">
                    <input type="radio" id="report0" name="report" checked="checked" value="0">
                    <label for="report0">Excluir</label>
                    <input type="radio" id="report1" name="report" value="1">
                    <label for="report1">Incluir</label>
            </span>
        </div>        
    </fieldset>
</form>

</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Crear perfil</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div><div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="modalStructureItemDelete" aria-labelledby="ui-id-23" style="display: none; position: absolute;"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-23" class="ui-dialog-title">Borrar Item?</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">close</span></button></div><div id="modalStructureItemDelete" class="ui-dialog-content ui-widget-content">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El o las item(s) seleccionada(s) se borrará(n). ¿Estás seguro?
    </p>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Borrar</span></button><button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Cancel</span></button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div></body></html>