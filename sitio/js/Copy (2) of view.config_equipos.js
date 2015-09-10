jQuery(function($) {

    
   function Equipo(formid) {
        
        this.form = $('#'+formid);
          
        this.value = function(secc) { 
             return this.form.find("#"+secc).val();
        }
        
        this.get = function(secc) { 
             return this.form.find("#"+secc);
        }
        
        this.getAll = function(secc) { 
             return this.form.find(":input");
        }
         
    }
    
    var formNew = new Equipo('form_new_sonda');
    var formEdit = new Equipo('form_edit_sonda');
    
    
    
    // Formularios
    
    var form_new = "form_new_sonda";
    var form_edit = "form_edit_sonda";

    var url_get_sonda_form = "/config/getSondaForm", 
        url_create_sonda = "/config/createSonda", 
        url_edit_sonda = "/config/editSonda";

    //Form new

    var sonda_name_new = "#" + form_new + " #sonda_name", 
        sonda_dns_new = "#" + form_new + " #sonda_dns", 
        sonda_group_new = "#" + form_new + " #sonda_group", 
        sonda_plan_new = "#" + form_new + " #sonda_plan", 
        sonda_ip_wan_new = "#" + form_new + " #sonda_ip_wan", 
        sonda_mac_wan_new = "#" + form_new + " #sonda_mac_wan", 
        sonda_group_new = "#" + form_new + " #sonda_group", ç
        sonda_mac_wan_new = "#" + form_new + " #sonda_mac_wan", 
        sonda_mac_lan_new = "#" + form_new + " #sonda_mac_lan", 
        sonda_ip_lan_new = "#" + form_new + " #sonda_ip_lan", 
        sonda_netmask_lan_new = "#" + form_new + " #sonda_netmask_lan", 
        sonda_ip_wan_new = "#" + form_new + " #sonda_ip_wan", 
        sonda_tabs_new = "#tabs_" + form_new, 
        allFields_new = $([]).add($(sonda_name_new)).add($(sonda_dns_new)).add($(sonda_ip_wan_new)).add($(sonda_mac_wan_new)), 
        tips_new = $("#" + form_new + "_validateTips"),
        sonda_secc_identificator_new = "#" + form_new + " #secc_identificator",
        sonda_secc_plan_new = "#" + form_new + " #secc_plan",
        sonda_secc_profile_edit= "#" + form_new + " #secc_profile";

    //Form edit

    var sonda_name_edit = "#" + form_edit + " #sonda_name", 
        sonda_dns_edit = "#" + form_edit + " #sonda_dns", 
        sonda_group_edit = "#" + form_edit + " #sonda_group", 
        sonda_plan_edit = "#" + form_edit + " #sonda_plan", 
        sonda_ip_wan_edit = "#" + form_edit + " #sonda_ip_wan", 
        sonda_mac_wan_edit = "#" + form_edit + " #sonda_mac_wan", 
        sonda_group_edit = "#" + form_edit + " #sonda_group", 
        sonda_mac_wan_edit = "#" + form_edit + " #sonda_mac_wan", 
        sonda_mac_lan_edit = "#" + form_edit + " #sonda_mac_lan", 
        sonda_ip_lan_edit = "#" + form_edit + " #sonda_ip_lan", 
        sonda_netmask_lan_edit = "#" + form_edit + " #sonda_netmask_lan", 
        sonda_ip_wan_edit = "#" + form_edit + " #sonda_ip_wan", 
        sonda_tabs_edit = "#tabs_" + form_edit, 
        allFields_edit = $([]).add($(sonda_name_edit)).add($(sonda_dns_edit)).add($(sonda_ip_wan_edit)).add($(sonda_mac_wan_edit)), 
        tips_edit = "#" + form_edit + "_validateTips",
        sonda_secc_identificator_edit= "#" + form_edit + " #secc_identificator",
        sonda_secc_plan_edit = "#" + form_edit + " #secc_plan",
        sonda_secc_profile_edit= "#" + form_edit + " #secc_profile";
        
    // Param Global
    
    var profile_status = 0;
    var plan_status = 0;
    var auth_metod = 'MAC';
    
    // Validadores NEW
    /*
    $(sonda_mac_wan_new).mask("**:**:**:**:**:**");
    $(sonda_mac_lan_new).mask("**:**:**:**:**:**");
    $(sonda_ip_lan_new).ipAddress();
    $(sonda_netmask_lan_new).ipAddress();
    $(sonda_ip_wan_new).ipAddress();*/
    
    formNew.get('sonda_mac_wan').mask("**:**:**:**:**:**");
    formNew.get('sonda_mac_lan').mask("**:**:**:**:**:**");
    formNew.get('sonda_ip_lan').ipAddress();
    formNew.get('sonda_netmask_lan').ipAddress();
    formNew.get('sonda_ip_wan').ipAddress();
    
     
    

    $(sonda_group_new).change(function() {

        equipo.type = 'new';
        
        var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();
        var panelId = tab.attr("aria-controls");
        $("#" + panelId).remove();
        $("tabs").tabs("refresh");
        //TabsSonda_new.tabs( "remove", 2 );
        var tabsSelect = $(sonda_group_new);
        var tabsSelectText = $(sonda_group_new + " option:selected").text();
        var tab_title_input = tabsSelectText;

        TabsSonda_new.find(".ui-tabs-nav").append("<li><a href='#tabs-3'>" + tab_title_input + "</a></li>");

        var groupid_select = $(sonda_group_new);

        var tabContentHtml;

        $.ajax({
            type : "POST",
            url : "/config/getFormFeature",
            data : "groupid=" + groupid_select.val(),
            dataType : "json",
            success : function(data) {
                if (data.status) {
                    profile_status
                    $('#form_new_sonda').append("<div id='tabs-3' style='display: none;'><p>" + data.datos + "</p></div>");
                } else {
                    $('#form_new_sonda').append("<div id='tabs-3' style='display: none;'><p>" + data.error + "</p></div>");
                }
            },
            error : function() {
                $('#form_new_sonda').append("<div id='tabs-3' style='display: none;'><p>Error de conexion</p></div>");
            }
        });

        TabsSonda_new.tabs("refresh");

        //TabsSonda_new.tabs( "add", "#tabs-3", tab_title_input );

        if (tabsSelect.val() > 0) {
            if (typeSonda == "1") {
                $.ajax({
                    type : "POST",
                    url : "/config/getPlanOption",
                    data : {
                        groupid : tabsSelect.val()
                    },
                    dataType : "json",
                    success : function(data) {
                        if (data.status) {
                            $(sonda_plan_new).html(data.datos);
                        } else {
                            alert("Error en el servidor");
                        }
                    },
                    error : function() {
                        alert("Error de conexion");
                    }
                });
            } else {
                return true;
            }
        } else {
            $(sonda_plan_new).html('<option selected value="0">Seleccione un grupo</option>');
        }
    });

    $("#dialog:ui-dialog").dialog("destroy");

    var TabsSonda_new = $(sonda_tabs_new).tabs();

    $("#modal_sonda_form").dialog({
        autoOpen : false,
        resizable : true,
        height : 'auto',
        width : 600,
        modal : true,
        zIndex : 20000,
        closeOnEscape : true,
        draggable : true,
        open : function() {
            $(this).find('.ui-dialog-titlebar-close').blur();
        },
        buttons : {
            "Crear sonda" : function() {
                var bValid = true;

                var typeSonda = $("#sonda_type").val();

                allFields_new.removeClass("ui-state-error");
                allFields_new.css("color", "#5A698B");

                bValid = bValid && $.checkLength(tips_new, $(sonda_name_new), "Nombre", 3, 200);
                bValid = bValid && $.checkSelect(tips_new, $(sonda_group_new), "Grupo", 0);
                if (typeSonda == "1") {
                    bValid = bValid && $.checkSelect(tips_new, $(sonda_plan_new), "Plan", 0);
                }
                bValid = bValid && $.checkLength(tips_new, $(sonda_dns_new), "Nombre DNS", 15, 15);
                if (typeSonda == "1") {
                    bValid = bValid && $.checkLength(tips_new, $(sonda_ip_wan_new), "Dirección IP WAN", 6, 15);
                    bValid = bValid && $.checkLength(tips_new, $(sonda_mac_wan_new), "Mac WAN", 17, 17);
                }

                if (bValid) {

                    jQuery('#loading').show();
                    jQuery('#imgLoading').show();

                    cadena = $("#" + form_new).serialize();

                    $.ajax({
                        type : "POST",
                        url : url_create_sonda,
                        data : cadena,
                        dataType : "json",
                        success : function(data) {
                            if (data.status) {
                                $("#modal_sonda_form").dialog("close");
                                $("#fmGrupo").val($(sonda_group_new).val());
                                $(".sondas").flexReload();
                            } else {
                                $.updateTips(tips_new, data.msg);
                            }
                        },
                        error : function() {
                            $.updateTips(tips_new, "Error de conexion con el servidor");
                        }
                    });

                    jQuery('#loading').hide();
                    jQuery('#imgLoading').hide();

                }
            },
            Cancel : function() {
                $(this).dialog("close");
            }
        },
        close : function() {
            tips_new.text('Todo los elementos del formulario son requeridos');
            allFields_new.val("").removeClass("ui-state-error");
            allFields_new.css("color", "#5A698B");
            $(sonda_group_new).val(0);
            $(sonda_plan_new).val(0);

            var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();
            var panelId = tab.attr("aria-controls");
            $("#" + panelId).remove();
            $("tabs").tabs("refresh");
            //TabsSonda_new.tabs( "remove", 2 );
        }
    });

    $("#sondas-delete").dialog({
        autoOpen : false,
        resizable : false,
        height : 150,
        width : 350,
        modal : true,
        zIndex : 20000,
        buttons : {
            "Borrar las sondas seleccionadas" : function() {

                jQuery('#loading').show();
                jQuery('#imgLoading').show();

                var sondasSelect = new Array();
                count = 1;
                grid = $('.sondas');
                $('.trSelected', grid).each(function() {
                    var id = $(this).attr('id');
                    id = id.substring(id.lastIndexOf('row') + 3);
                    if ($.isNumeric(id)) {
                        sondasSelect[count] = id;
                        count = count + 1;
                    }
                });

                $.ajax({
                    type : "POST",
                    url : "/config/deleteSonda",
                    data : {
                        idSonda : sondasSelect
                    },
                    dataType : "json",
                    success : function(data) {
                        if (data.status) {
                            $(".sondas").flexReload();
                        } else {
                            alert("Acceso Denegado");
                        }
                    },
                    error : function() {
                        alert("Error de conexion");
                        $("#sondas-delete").dialog("close");
                    }
                });
                jQuery('#loading').hide();
                jQuery('#imgLoading').hide();
                $("#sondas-delete").dialog("close");
            },
            Cancel : function() {
                $("#sondas-delete").dialog("close");
            }
        }
    });

    $("#sonda-config").dialog({
        autoOpen : false,
        resizable : false,
        height : 150,
        width : 400,
        zIndex : 20000,
        modal : true,
        buttons : {
            "Configurar las sondas seleccionadas" : function() {

                jQuery('#loading').show();
                jQuery('#imgLoading').show();

                var sondasSelect = new Array();
                count = 1;
                grid = $('.sondas');
                $('.trSelected', grid).each(function() {
                    var id = $(this).attr('id');
                    id = id.substring(id.lastIndexOf('row') + 3);
                    if ($.isNumeric(id)) {
                        sondasSelect[count] = id;
                        count = count + 1;
                    }
                });

                $.ajax({
                    type : "POST",
                    url : "/config/setConfigSonda",
                    data : {
                        idSonda : sondasSelect
                    },
                    dataType : "json",
                    success : function(data) {
                        if (data.status) {
                            $(".sondas").flexReload();
                        } else {
                            alert("Acceso Denegado");
                        }
                    },
                    error : function() {
                        alert("Error de conexion");
                        $("#sondas-delete").dialog("close");
                    }
                });
                jQuery('#loading').hide();
                jQuery('#imgLoading').hide();
                $("#sonda-config").dialog("close");
            },
            Cancel : function() {
                $("#sonda-config").dialog("close");
            }
        }
    });

    $.editarSonda = function(id, clonar) {

        var dialog = $('<div style="display:none" id="editSondaDialog" class="loading"></div>').appendTo('body');

        if (clonar) {
            var id_set = "clone";
            var type = 'clone';
            var url_send = url_create_sonda;
        } else {
            var type = 'edit';
            var id_set = id;
            var url_send = url_edit_sonda;
        }

        $("#modal_sonda_edit").load(url_get_sonda_form, {
            sondaID : id,
            type : type
        }, function(responseText, textStatus, XMLHttpRequest) {
            if (clonar) {
                var name1 = $(sonda_name_edit).val();
                $(sonda_name_edit).val(name1 + '_1');
            }
            $("#modal_sonda_edit").removeClass('loading');

            $(sonda_mac_wan_edit).mask("**:**:**:**:**:**");
            $(sonda_mac_lan_edit).mask("**:**:**:**:**:**");
            $(sonda_ip_lan_edit).ipAddress();
            $(sonda_netmask_lan_edit).ipAddress();
            $(sonda_ip_wan_edit).ipAddress();

            var TabsSonda_edit = $(sonda_tabs_edit).tabs({
                add : function(event, ui) {

                    var groupid_select_edit = $(sonda_group_edit);

                    var tab_content;

                    $.ajax({
                        type : "POST",
                        url : "/config/getFormFeature",
                        data : {
                            groupid : groupid_select_edit.val(),
                            id : id_set
                        },
                        dataType : "json",
                        success : function(data) {
                            if (data.status) {
                                $(ui.panel).append(data.datos);
                            } else {
                                $(ui.panel).append('Error en el servidor');
                            }
                        },
                        error : function() {
                            $(ui.panel).append('Error de conexion');
                        }
                    });
                    //var tab_content = $( "#new_sonda_tabs_op_"+tabsSelect.val() ).html();
                }
            });

            var tab_title_input = $(sonda_group_edit + " option:selected").text();

            TabsSonda_edit.tabs("add", "#tabs-3", tab_title_input);

            $(sonda_group_edit).change(function() {

                var tab = TabsSonda_edit.find(".ui-tabs-nav li:eq(2)").remove();
                var panelId = tab.attr("aria-controls");
                $("#" + panelId).remove();
                $("tabs").tabs("refresh");

                //TabsSonda_edit.tabs( "remove", 2 );

                var tabsSelect = $(sonda_group_edit);
                var tabsSelectText = $(sonda_group_edit + " option:selected").text();
                tab_title_input = tabsSelectText;

                TabsSonda_edit.tabs("add", "#tabs-3", tab_title_input);

                if (tabsSelect.val() > 0) {
                    $.ajax({
                        type : "POST",
                        url : "/config/getPlanOption",
                        data : {
                            groupid : tabsSelect.val()
                        },
                        dataType : "json",
                        success : function(data) {
                            if (data.status) {
                                $(sonda_plan_edit).html(data.datos);
                            } else {
                                alert("Error en el servidor");
                            }
                        },
                        error : function() {
                            alert("Error de conexion");
                        }
                    });
                } else {
                    $(sonda_plan_edit).html('<option selected value="0">Seleccione un grupo</option>');
                }
            });
        });


        var typeSonda = $(sonda_type_edit).val();
        
        if (typeSonda == "1") {
            $(sonda_secc_plan_edit).show();
            $(sonda_secc_identificator_edit).hide();
        } else if (typeSonda == "2") {
            $(sonda_secc_plan_edit).hide();
            $(sonda_secc_identificator_edit).show();
        } else if (typeSonda == "3") {
            $(sonda_secc_plan_edit).hide();
            $(sonda_secc_identificator_edit).show();
        }

        $(sonda_type_edit).change(function() {
            console.log("asdsa");
            var typeSonda = $(sonda_type_edit).val();
            if (typeSonda == "1") {
                $(sonda_secc_plan_edit).show();
                $(sonda_secc_identificator_edit).hide();
            } else if (typeSonda == "2") {
                $(sonda_secc_plan_edit).hide();
                $(sonda_secc_identificator_edit).show();
            } else if (typeSonda == "3") {
                $(sonda_secc_plan_edit).hide();
                $(sonda_secc_identificator_edit).show();
            }
        });
   
        $("#modal_sonda_edit").dialog({
            resizable : false,
            height : 'auto',
            width : 650,
            zIndex : 20000,
            modal : true,
            cache : false,
            position : ['middle', 20],
            buttons : {
                "Guardar sonda" : function() {

                    var bValid = true;

                    jQuery('#loading').show();
                    jQuery('#imgLoading').show();

                    var typeSonda = $(sonda_type_edit).val();

                    allFields_new.removeClass("ui-state-error");
                    allFields_new.css("color", "#5A698B");

                    bValid = bValid && $.checkLength(tips_edit, $(sonda_name_edit), "Nombre", 3, 200);
                    bValid = bValid && $.checkSelect(tips_edit, $(sonda_group_edit), "Grupo", 0);
                    if (typeSonda == "1") {
                        bValid = bValid && $.checkSelect(tips_edit, $(sonda_plan_edit), "Plan", 0);
                    }
                    bValid = bValid && $.checkLength(tips_edit, $(sonda_dns_edit), "Nombre DNS", 15, 15);
                    if (typeSonda == "1") {
                        bValid = bValid && $.checkLength(tips_edit, $(sonda_ip_wan_edit), "Dirección IP WAN", 6, 15);
                        bValid = bValid && $.checkLength(tips_edit, $(sonda_mac_wan_edit), "Mac WAN", 17, 17);
                    }

                    if (bValid) {
                        $.ajax({
                            type : "POST",
                            url : url_send,
                            dataType : "json",
                            data : "id=" + id_set + "&" + $('#' + form_edit).serialize(),
                            success : function(data) {
                                if (data.status) {
                                    $("#modal_sonda_edit").dialog("close");
                                    $(".sondas").flexReload();
                                } else {
                                    $.updateTips($(tips_edit), data.msg);
                                }
                            },
                            error : function() {
                                $.updateTips($(tips_edit), "Error de conexion con el servidor");
                            }
                        });

                    }
                    jQuery('#loading').hide();
                    jQuery('#imgLoading').hide();
                },
                Cancel : function() {
                    $(this).dialog("close");
                }
            }
        });

    }
});

function statusSonda(id) {

    var status = $('#statusSonda_span_' + id).html();

    if (status == 'Activo') {
        status_value = 0;
    } else {
        status_value = 1;
    }

    $.ajax({
        type : "POST",
        url : "/config/statusSonda",
        data : {
            status : status_value,
            id : id
        },
        dataType : "json",
        success : function(data) {
            if (data.status) {
                if (status == 'Activo') {
                    $('#statusSonda_span_' + id).html('Inactivo');
                    $('#statusSonda_span_' + id).removeClass("status_on").addClass("status_off");
                } else {
                    $('#statusSonda_span_' + id).html('Activo');
                    $('#statusSonda_span_' + id).removeClass("status_off").addClass("status_on");
                }
            } else {
                alert("Error interno");
            }
        },
        error : function() {
            alert("Error de conexion con el servidor");
        }
    });
}

function editSonda(id, clonar) {
    $.editarSonda(id, clonar);
    return false;
}

var sendIDButton = '';

$("#modal_sonda_trigger").dialog({
    autoOpen : false,
    resizable : false,
    height : 200,
    width : 450,
    modal : true,
    buttons : {
        "Iniciar Trigger" : function() {

            var bValid = true;

            jQuery('#loading').show();
            jQuery('#imgLoading').show();

            bValid = bValid && $.checkLength($('#config_equipos_trigger_validateTips'), $('#trigger_responsable'), "Responsable", 3, 40);
            bValid = bValid && $.checkSelect($('#config_equipos_trigger_validateTips'), $('#trigger_id'), "Trigger", 0);

            if (bValid) {
                $.ajax({
                    type : "POST",
                    url : "/config/setTriggerSonda",
                    dataType : "json",
                    data : "idSonda=" + sendIDButton + "&" + $('#config_equipos_trigger').serialize(),
                    success : function(data) {
                        if (data.status) {
                            $("#modal_sonda_trigger").dialog("close");
                        } else {
                            alert("Acceso Denegado");
                        }
                    },
                    error : function() {
                        alert("Error de conexion");
                    }
                });
            }
            jQuery('#loading').hide();
            jQuery('#imgLoading').hide();

        },
        Cancel : function() {
            $("#modal_sonda_trigger").dialog("close");
        }
    }

});

function triggerSonda(id) {
    sendIDButton = id;
    $("#modal_sonda_trigger").dialog("open");
}

function configSonda(id) {

    var sondasSelect = new Array();
    sondasSelect[0] = id;

    $.ajax({
        type : "POST",
        url : "/config/setConfigSonda",
        data : {
            idSonda : sondasSelect
        },
        dataType : "json",
        success : function(data) {
            if (data.status) {
                alert("Configuracion informada");
            } else {
                alert("Acceso Denegado");
            }
        },
        error : function() {
            alert("Error de conexion");
            $("#sondas-delete").dialog("close");
        }
    });
}