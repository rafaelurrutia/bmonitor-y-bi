<style type="text/css">
    ul.ui-tabs-nav .ui-state-disabled {
        display: none; /* disabled tabs don't show up */
    }
</style>
    <div id="{form_id}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
           {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>

<div id="tabs_{form_id}">
    <form id="{form_id}" class="formUI">

        <ul>
            <li>
                <a href="#{form_id}_tabs_1">{MENU_CONFIGURATION}</a>
            </li>
            <li>
                <a href="#{form_id}_tabs_2">{GENERAL}</a>
            </li>
        </ul>

        <div id="{form_id}_tabs_1">

            <fieldset class="ui-widget-content ui-corner-all">
                <div class="row">
                    <label for="sonda_name" class="col1">{NAME}</label>
                    <span class="col2">
                        <input type="text" style="width: 90%;" name="sonda_name" id="sonda_name" value="{sonda_host}" class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_identificator" style="display: none" class="row">
                    <label for="sonda_identificator" class="col1">{IDENTIFIER}</label>
                    <span class="col2">
                        <input type="text" name="sonda_identificator" id="sonda_identificator" value="{sonda_identificator}"  class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div class="row" {sti_codigosonda}>
                    <label for="sonda_codigosonda" class="col1">{AGENT_CODE}</label>
                    <span class="col2">
                        <input type="text" name="sonda_codigosonda" id="sonda_codigosonda" value="{sonda_codigosonda}" class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div class="row">
                    <label for="sonda_group" class="col1">{GROUP}</label>
                    <span class="col2">
                        <select name="sonda_group" id="sonda_group" value="{sonda_group}" class="select">
                            {option_group}
                        </select></span>
                </div>
                <div id="secc_profile" style="display: none" class="row">
                    <label for="sonda_profile" class="col1">{PROFILE}</label>
                    <span class="col2">
                        <select name="sonda_profile" id="sonda_profile" value="{sonda_profile}" class="select">
                            {option_profile}
                        </select></span>
                </div>
                <div id="secc_plan" style="display: none" class="row">
                    <label for="sonda_plan" class="col1">{PLAN}</label>
                    <span class="col2">
                        <select name="sonda_plan" id="sonda_plan" class="select">
                            {option_plan}
                        </select> </span>
                </div>
                <div id="secc_ip_wan" class="row">
                    <label for="sonda_ip_wan" class="col1">{ADDRESS} IP WAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_ip_wan" id="sonda_ip_wan" value="{sonda_ip_wan}"  class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_ma_wan" class="row">
                    <label for="sonda_mac_wan" class="col1">Mac WAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_mac_wan" id="sonda_mac_wan" style="text-transform:uppercase;" value="{sonda_mac_wan}" class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_lanconfig" class="row">
                    <label for="sonda_ip_lan" class="col1">{ADDRESS} IP - LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_ip_lan" id="sonda_ip_lan" value="{sonda_ip_lan}"  class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_lanconfig" class="row">
                    <label for="sonda_netmask_lan" class="col1">{NETMASK} - LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_netmask_lan" id="sonda_netmask_lan" value="{sonda_netmask_lan}" class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_lanconfig" class="row">
                    <label for="sonda_mac_lan" class="col1">Mac LAN</label>
                    <span class="col2">
                        <input type="text" name="sonda_mac_lan" id="sonda_mac_lan" value="{sonda_mac_lan}" style="text-transform:uppercase;" class="input ui-widget-content ui-corner-all" />
                    </span>
                </div>
                <div id="secc_dns" class="row">
                    <label for="sonda_dns" class="col1">ID [BSW[DNS]]</label>
                    <span class="col2">
                        <input type="text" name="sonda_dns" id="sonda_dns" value="{sonda_dns}"  class="input ui-widget-content ui-corner-all" disabled/>
                    </span>
                </div>
                <div class="row">
                    <label for="sonda_tags" class="col1">Tags</label>
                    <span class="col2">
                        <input name="sonda_tags" id="sonda_tags" value="{sonda_tags}">
                    </span>
                </div>
            </fieldset>

        </div>

        <div id="{form_id}_tabs_2">
            <fieldset class="ui-widget-content ui-corner-all">
                {input_config}
            </fieldset>
        </div>

    </form>
</div>