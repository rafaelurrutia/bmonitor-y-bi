<script language="JavaScript">
jQuery(function() {

	function genTableNeutralidad () {
		var pesos = $("#fmPeso_neutralidad").val();
		var escala = $("#fmEscala_neutralidad").val();
		var grupo = $("#fmGrupo_neutralidad").val();
		var meses = $("#fmMeses_neutralidad").val();
		var ano = $("#fmAno_neutralidad").val();
		
		$.ajax({
			type: "POST",
			url: "/neutralidad/getTableNeutralidad",
			dataType: "json",
			data: {
				pesos:pesos,
				escala:escala,
				grupo:grupo,
				meses:meses,
				ano:ano
			},
			success: function(data){
				if(data.status) {

					$("#container_tables_center_1").html(data.tablas);

				} else {
					alert("error");
				}
			},
			error: function() {
				alert("Error");
			}
		});
	}
	
	genTableNeutralidad();
	
        $(".neutralidadResumen").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_1',
                dataType: 'json',
                title: 'Resumen',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : '120'  , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : '40'  , sortable : false, align: 'center'},
                        {display: 'Disponibilidad', name : 'disponibilidad' , width : '72'  , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : '31'  , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : '43'  , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : '43'  , sortable : false, align: 'center'}
                ],
                width: 500,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidadResumen').flexOptions({params: [{name:'type', value:'resumen'},{name:'item', value:'neutralidadResumen'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
		});
        
		$(".neutralidad_1").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad NAC Bajada',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 47 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 48 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 49  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 48  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 51  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 68 , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100 , sortable : false, align: 'center'}
                ],
				width: 1040,
                height: 'auto',
                onSubmit : function(){
                        $('.neutralidad_1').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'NACbandwidthdown.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                },
                usepager: true,
                useRp: true,
				rp: 15,
				showTableToggleBtn: true
        });
       
        $(".neutralidad_2").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad NAC Subida',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
				width: 1040,
                height: 'auto',
                showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_2').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'NACbandwidthup.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });

        $(".neutralidad_3").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad LOC Bajada',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
				width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_3').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'LOCbandwidthdown.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
        $(".neutralidad_4").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad LOC Subida',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                width: 1040,
                height: 'auto',
                showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_4').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'LOCbandwidthup.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
		$(".neutralidad_5").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad INT Bajada',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
				width: 1040,
                height: 'auto',
                onSubmit : function(){
                        $('.neutralidad_5').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'INTbandwidthdown.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                },
                showTableToggleBtn: true
        });
       
        $(".neutralidad_6").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Velocidad INT Subida',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                 width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_6').flexOptions({params: [{name:'type', value:'vel'},{name:'item', value:'INTbandwidthup.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });

        $(".neutralidad_7").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Ping NAC',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                 width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_7').flexOptions({params: [{name:'type', value:'ping'},{name:'item', value:'NACping_avg.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
        $(".neutralidad_8").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Ping LOC',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                 width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_8').flexOptions({params: [{name:'type', value:'ping'},{name:'item', value:'LOCping_avg.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
        $(".neutralidad_9").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Ping INT',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                 width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_9').flexOptions({params: [{name:'type', value:'ping'},{name:'item', value:'INTping_avg.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
        $(".neutralidad_10").flexigrid({
                url: '{url_base}neutralidad/getTableNeutralidad_monitor',
                dataType: 'json',
                title: 'Login',
                colModel : [
                        {display: 'Plan', name : 'plan' , width : 120 , sortable : false, align: 'left'},
                        {display: 'Mes', name : 'mes' , width : 40 , sortable : false, align: 'center'},
                        {display: 'Q', name : 'com_q' , width : 31 , sortable : false, align: 'center'},
                        {display: 'Exitosos', name : 'exitosos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Fallidos', name : 'fallidos' , width : 43 , sortable : false, align: 'center'},
                        {display: 'Tasa </br> de </br> Fallas', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Error </br> de la </br> prueba', name : 'fallidos' , width : 45  , sortable : false, align: 'center'},
                        {display: 'Prom', name : 'fallidos' , width : 34 , sortable : false, align: 'center'},
                        {display: 'Mín', name : 'fallidos' , width : 31  , sortable : false, align: 'center'},
                        {display: 'Máx', name : 'fallidos' , width : 41 , sortable : false, align: 'center'},
                        {display: 'Desv.', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 5', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 80', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Per 95', name : 'fallidos' , width : 41  , sortable : false, align: 'center'},
                        {display: 'Confiabilidad </br> Medición', name : 'fallidos' , width : 66  , sortable : false, align: 'center'},
                        {display: 'Error </br> Medición(máx 5)', name : 'fallidos' , width : 100  , sortable : false, align: 'center'}
                ],
                width: 1040,
                height: 'auto',
				showTableToggleBtn: true,
                onSubmit : function(){
                        $('.neutralidad_10').flexOptions({params: [{name:'type', value:'login'},{name:'item', value:'ip_renew.sh'}].concat($('#fmFilter_neutralidad').serializeArray())});
                        return true;
                }
        });
        
        function refresh () {
        	genTableNeutralidad();
        	$(".neutralidadResumen").flexReload();
        	$(".neutralidad_1").flexReload();
        	$(".neutralidad_2").flexReload();
        	$(".neutralidad_3").flexReload();
        	$(".neutralidad_4").flexReload();
        	$(".neutralidad_5").flexReload();
        	$(".neutralidad_6").flexReload();
        	$(".neutralidad_7").flexReload();
        	$(".neutralidad_8").flexReload();
        	$(".neutralidad_9").flexReload();
        	$(".neutralidad_10").flexReload();
        }
        
        $("#fmPeso_neutralidad").change(function(){
			refresh();
        });

        $("#fmEscala_neutralidad").change(function(){
			refresh();
        });

        $("#fmGrupo_neutralidad").change(function(){
			refresh();
        });

        $("#fmMeses_neutralidad").change(function(){
			refresh();
        });

        $("#fmAno_neutralidad").change(function(){
			refresh();
        });
});
</script>
<div>
	<div id="loading"></div>
	<div id="imgLoading">
		<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
	</div>
	
	<div class="paneles">
	<form id="fmFilter_neutralidad">
	<label for="fmPeso_neutralidad" accesskey="g">Pesos</label>
	<select name="fmPeso_neutralidad" id="fmPeso_neutralidad">
	<option value='1' selected>Sin peso</option>
	<option value='2'>Con peso</option>
	</select>
	
	<label for="fmEscala_neutralidad" accesskey="g">Escala</label>
	<select name="fmEscala_neutralidad" id="fmEscala_neutralidad">
	{option_escalas}
	</select>
	
	<label for="fmGrupo_neutralidad" accesskey="g">Grupo</label>
	<select name="fmGrupo_neutralidad" id="fmGrupo_neutralidad">
	{option_groups}
	</select>
	
	<label for="fmMeses_neutralidad" accesskey="m">Mes</label>
	<select name="fmMeses_neutralidad" id="fmMeses_neutralidad">
	{option_meses}
	</select>
	
	<label for="fmAno_neutralidad" accesskey="a">Año</label>
	<select name="fmAno_neutralidad" id="fmAno_neutralidad">
	{option_anos}
	</select>
	</div>
	<div id='container_tables_center_1'></div>
	<div id='container_tables_center'>
		<table class="neutralidadResumen"></table>
		<table class="neutralidad_1"></table>
		<table class="neutralidad_2"></table>
		<table class="neutralidad_3"></table>
		<table class="neutralidad_4"></table>
		<table class="neutralidad_5"></table>
		<table class="neutralidad_6"></table>
		<table class="neutralidad_7"></table>
		<table class="neutralidad_8"></table>
		<table class="neutralidad_9"></table>
		<table class="neutralidad_10"></table>
	</div>
</div>