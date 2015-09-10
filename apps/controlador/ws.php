<?php

class ws extends Control
{

    public function get($comany, $ip, $type = TRUE)
    {

        if ($ip == 'none') {
            $ip = $this->logs->getIP();
        }

        if ($type === 'xml') {
            $typeResult = 'xml';
        } elseif ($type === true) {
            $typeResult = 'json';
        } else {
            $typeResult = false;
        }

        if (($comany == 1) || ($comany == 'movistar')) {

            $url_base = 'https://pcba.telefonicachile.cl/';
            //$url_base = 'http://apimovistar.baking.cl';

            $url = $url_base . "/dhc/retrieveCustomer.php?wsdl";
            $location = $url_base . "/dhc/retrieveCustomer.php";
        } else {
            if ($typeResult == 'json') {
                echo "company error";
                exit ;
            } else {
                return false;
            }
        }

        try {

            $client = @new SoapClient($url, array(
                "connection_timeout" => 25,
                "soap_version" => SOAP_1_1,
                "exceptions" => 1,
                "location" => $location,
                "trace" => 1
            ));

        } catch(SoapFault $e) {
            $this->logs->error("Error API de movistar: ", $e->faultstring, 'logs_api');
            return false;
        }

        $param = array("request" => array("In" => array(
                    "Account" => array(
                        'User' => 'dhc',
                        'Password' => 'ws_475'
                    ),
                    "Ip" => $ip
                )));

        $result = $client->__call("getDatIp", $param);

        if (is_soap_fault($result)) {
            trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
        }

        $Out['ip'] = $result->Out->Ip;
        $Out['PhoneNumber'] = $result->Out->PhoneNumber;

        $this->logs->debug("Maldito numero:", $Out, 'logs_api');

        if ($typeResult == 'xml') {
            $result_xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><retrieveCustomer></retrieveCustomer>');
            $result_xml->addChild('ip', $result->Out->Ip);
            $result_xml->addChild('PhoneNumber', $result->Out->PhoneNumber);
            header("Content-Type:text/xml");
            echo $result_xml->asXML();
            exit ;
        } elseif ($typeResult == 'json') {
            echo json_encode($Out);
            exit ;
        } else {
            return (object)$Out;
        }
    }


    public function action()
    {
        $server = new SoapServer("wsActionsCMAyudaVTR", array('actor' => "http://".URL_BASE_FULL."/ws/action"));
    }

}
?>