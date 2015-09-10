<script type="text/javascript" src="{url_base}sitio/js/view.graph_compare.js"></script>
<style>
    #targetLineBar {
        width: 99%;
        height: 30px;
        padding: 5px 0px 5px 15px;
        background-color: #f3f3f3;
        border-bottom: 1px solid #c3c3c3;
    }
    #targetContainer {
        float: left;
        height: 30px;
        width: 260px;
        padding: 0 0 0 0;
        margin: 0 0 0 0;
    }
    #targetLabel {
        font-family: Georgia;
        font-weight: bold;
        height: 100%;
        margin: 5px 5px 0 0;
        padding: 0 0 0 0;
        float: left;
    }
    #targetTextBox {
        width: 60px;
        padding: 0 0 0 3px;
        margin: 5px 0 0 0;
        height: 18px;
        float: left;
    }
    #targetButton {
        margin: 4px 0 0 5px;
        float: left;
        height: 22px;
    }

    #chkBoxContainer {
        float: left;
        height: 30px;
        padding: 0 10px 0 10px;
        margin: 0 0 0 0;
    }
    #avgChkBox {
        margin: 10px 0 0 0;
        padding: 0px 0 0 0;
    }
    #avgLabel {
        margin: 0 0 0 0;
        padding: 0 0 0 0;
        font-family: Arial;
        font-size: 10pt;
    }
    #details {
        padding: 5px 10px 0 40px;
    }
</style>

<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <label for="fm_graph_Grupo" accesskey="g">Grupo</label>
    <select name="fm_graph_Grupo" id="fm_graph_Grupo">
        {option_groups}
    </select>
    <label for="fm_graph_Host_1" accesskey="e">Equipo 1</label>
    <select style=" min-width: 404px; " accesskey="h" name="fm_graph_Host_1" id="fm_graph_Host_1">
        {option_equipos}
    </select>
    <label for="fm_graph_Host_2" accesskey="e">Equipo 2</label>
    <select style=" min-width: 404px; " accesskey="h" name="fm_graph_Host_2" id="fm_graph_Host_2">
        {option_equipos}
    </select>
</div>
<div class="paneles fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
    <form id="fm_graph_Filter">
        <fieldset>
            <label for="fm_graph_limit" accesskey="g">Limite</label>
            <select name="fm_graph_limit" id="fm_graph_limit">
                <option value="0">Por defecto</option>
                <option value="5000">1 Mes</option>
                <option value="15000">3 Mes</option>
                <option value="25000">6 Mes</option>
                <option value="60000">1 Año</option>
            </select>
            <label for="fm_graph_Graph" accesskey="a">Graficos</label>
            <select name="fm_graph_Graph" id="fm_graph_Graph">
                {option_graph}
            </select>
        </fieldset>
    </form>
</div>
<div style="height: 500px; min-width: 500px" id="graph_compare"></div>