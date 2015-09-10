<?php

class section extends Control
{
    private $theme = 'ui-switcher';

    public function menu($active = 'Inicio', $empty = false)
    {

        if ($empty) {
            $menu = array();
        } else {

            $menu[] = array(
                'title' => $this->language->MENU_MONITORING,
                'urlBase' => true,
                'href' => '',
                'protec' => 'INDEX'
            );

            $menu[] = array(
                'title' => $this->language->MENU_INVENTORY,
                'urlBase' => true,
                'href' => 'inventario',
                'protec' => 'INVENTARIO'
            );
            /*
            $menu[] = array(
                'title' => 'FDT',
                'urlBase' => true,
                'href' => 'fdt',
                'protec' => 'FDT'
            );*/

            $menu[] = array(
                'title' => 'QoE',
                'urlBase' => true,
                'href' => 'qoe',
                'protec' => 'QOE'
            );

            $menu[] = array(
                'title' => "QoS",
                'urlBase' => true,
                'href' => 'neutralidad',
                'protec' => 'NEUTRALIDAD'
            );
            $menu[] = array(
                'title' => $this->language->MENU_CONFIGURATION,
                'urlBase' => true,
                'href' => 'config',
                'protec' => 'CONFIGURACION'
            );

            $menu[] = array(
                'title' => $this->language->MENU_ADMINISTRATION,
                'urlBase' => true,
                'href' => 'admin',
                'protec' => 'ADMINISTRACION'
            );
        }

        return $this->generate->getMenu($menu, $active);
    }

    public function header()
    {
        $this->plantilla->load("basic/header");
        $vars['title'] = $this->parametro->get('SITE', 'bMonitor');

        $theme = 'ui-switcher';

        if ($theme == 'ui-lightness') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>' . "\n";
        } elseif ($theme == 'ui-darkness') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/css/ui-darkness/jquery-ui-1.10.3.custom.min.css"/>' . "\n";
        } elseif ($theme == 'rocket') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="http://cdn.wijmo.com/themes/rocket/jquery-wijmo.css"/>' . "\n";
        } elseif ($theme == 'aristo') {
            $style = '<link rel="stylesheet" type="text/css" href="http://cdn.wijmo.com/themes/aristo/jquery-wijmo.css"/>' . "\n";
        } elseif ($theme == 'cobalt') {
            $style = '<link rel="stylesheet" type="text/css" href="http://cdn.wijmo.com/themes/cobalt/jquery-wijmo.css"/>' . "\n";
        } elseif ($theme == 'Bootstrap-wijmo') {
            $style = '<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">' . "\n";
            $style .= '<link href="http://cdn.wijmo.com/jquery.wijmo-pro.all.3.20133.18.min.css" rel="stylesheet" type="text/css" />' . "\n";
            $style .= '<link href="http://cdn.wijmo.com/interop/bootstrap-wijmo.css" rel="stylesheet" type="text/css" />' . "\n";
        } elseif ($theme == 'sterling') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="http://cdn.wijmo.com/themes/sterling/jquery-wijmo.css"/>' . "\n";
        } elseif ($theme == 'flick') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/css/flick/jquery-ui-1.10.3.custom.min.css"/>' . "\n";
        } elseif ($theme == 'Delta') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/themes/Delta/jquery-ui.css"/>' . "\n";
        } elseif ($theme == 'Bootstrap') {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/assets/css/bootstrap.min.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/css/jquery-ui-bootstrap/jquery-ui-1.10.0.custom.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/assets/css/font-awesome.min.css"/>' . "\n";
            $style .= '<!--[if IE 7]>
            <link rel="stylesheet" href="/sitio/assets/css/font-awesome-ie7.min.css">
            <![endif]-->
            <!--[if lt IE 9]>
            <link rel="stylesheet" type="text/css" href="/sitio/css/jquery-ui-bootstrap/jquery.ui.1.10.0.ie.css"/>
            <![endif]-->' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/assets/css/docs.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/assets/js/google-code-prettify/prettify.css"/>' . "\n";
        } elseif ($theme == 'ui-switcher') {
            $style = '';
        } else {
            $style = '<link rel="stylesheet" type="text/css" href="/sitio/css/normalize.css"/>' . "\n";
            $style .= '<link rel="stylesheet" type="text/css" href="/sitio/css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>' . "\n";
        }

        $vars['theme'] = $style;
        $vars['language'] = $this->protect->getLang();

        $this->plantilla->set($vars);
        return $this->plantilla->get();
    }

    public function footer($param = false, $logo = true, $themeSwitches = true)
    {
        $this->plantilla->load("basic/footer");

        if ($this->theme == 'ui-switcher' && $themeSwitches == false) {
            $vars['jscript'] = '<script type="text/javascript" src="/sitio/js/jquery.themeswitcher.min.js"></script><script type="text/javascript">' . "\n" . '$(document).ready(function(){' . "\n" . '$("#switcher").themeswitcher({
                imgpath: "/sitio/img/",
                loadTheme: "cupertino"
            });' . "\n" . '});' . "\n" . '</script>';
        } else {
            $vars['jscript'] = '<script type="text/javascript">' . "\n" . '$(document).ready(function(){' . "\n" . '$.themeSwitches();' . "\n" . '});' . "\n" . '</script>';            
        }

        $vars['footer'] = $this->parametro->get('FOOTER_WEB', 'Copyright © Baking, 2013');
        if ($logo) {
            $vars['footer_logo'] = '<div class="logo"><img src="/sitio/img/main-logo.png" alt="Baking" width="170"/></div>';
        }
        $this->plantilla->set($vars);
        return $this->plantilla->get();
    }

}
?>