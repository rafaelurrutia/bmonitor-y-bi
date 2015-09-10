<script type="text/javascript">
    table_auditoria = $("#table_auditoria");
    table_auditoria.flexigrid({
        url: '/admin/getTableAuditoria',
        title: '{AUDIT}',
        dataType: 'json',
        colModel : [
            {display: '{USER}', name : 'usuario'  , width : '104'  , sortable : true , align: 'left'},
            {display: '{DATE}', name : 'fechahora'  , width : '111'  , sortable : true , align: 'left'},
            {display: '{ACTION}', name : 'action'  , width : '160'  , sortable : true , align: 'left'},
            {display: '{DETAIL}', name : 'details'  , width : '400'  , sortable : true , align: 'left'}
        ],
        usepager: true,
        useRp: true,
        striped:false,
        rp: 30, 
        resizable: true,
        width: 'auto',
        height: 'auto'
    });
</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<table id="table_auditoria" class="table_auditoria"></table>