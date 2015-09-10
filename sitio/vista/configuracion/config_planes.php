<script type="text/javascript">

    lang = "{lang}";
    
    completeForm =  {DISPLAYCOMPLETEFORM};
    
    tablePlanes = $("#tablePlanes");
    
    var filterPlans = new $.formVar( 'tablePlanesFilter' );

    tablePlanes.flexigrid({
        url: '/config/getTablePlanes',
        title: '{PLANS}',
        dataType: 'json',
        colModel : [
            {display: '{GROUP}', name : 'groupname'  , width : '100'  , sortable : false , align: 'left'},
            {display: 'Plan', name : 'plan'  , width : '105'  , sortable : false , align: 'center'},
            {display: '{OPTIONS}', name : 'option'  , width : '183'  , sortable : false , align: 'center'}
        ],
        {button}       
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            tablePlanes.flexOptions({params: [{
                       name:'callId', 
                       value:'tablePlanes'
            }].concat(filterPlans.form.serializeArray())});
            return true;
        },
        height: 'auto',
        onSuccess:  function(){
             $( "#tablePlanes #toolbarSet" ).buttonset();
        }
    }); 
    
    if(completeForm){
        $("#config_planes_form_new #otherDisplay").show();
    }
    
    filterPlans.get('groupid').change(function( ) {
        tablePlanes.flexReload();
    });
    
    function toolboxPlanes(com, grid) {
        if (com == '{NEW}') {
            $( "#modal_plan_new" ).dialog( "open" );
        } else if (com == '{DELETE}') {
            lengthSelect = $('.trSelected', grid).length;
            if(lengthSelect > 0) {
                $( "#modal_plan_delete" ).dialog( "open" );
            }
        }
    }
    
</script>
<script type="text/javascript" src="{url_base}sitio/js/view.config_plan2.js"></script>
<style type="text/css">
   .multiselect  {
        width: 605px;
        height: 200px;
   } 
   
   #otherDisplay {
       display: none;
   }
</style>

<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_plan_new" title="{TITLE_NEW_PLAN}">
    {form_new_plan}
</div>

<div id="modal_plan_delete" title="{TITLE_DELETE_PLAN}">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>
</div>

<div id="modal_plan_edit" title="{TITLE_EDIT_PLAN}"></div>


<div class="paneles">
<form id="tablePlanesFilter">
    <fieldset>
        <div id="row">
            <label for="groupid" accesskey="g">{GROUP}</label>
            <select name="groupid" id="groupid">
                {combobox_groups}
            </select>
        </div>
    </fieldset>
</form>
</div>

<table id="tablePlanes" class="tablePlanes"></table>