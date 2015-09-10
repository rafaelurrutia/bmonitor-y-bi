<?php
class Openwrt
{
    private function show($var)
    {
        $out = array();
        exec("uci show $var", $out);

        if ($out == 'uci: Entry not found') {
            return false;
        }
        return $out;
    }

    public function getUci($value = '')
    {
        $outUCI = $this -> show($value);
        $result = array();

        if ($outUCI) {
            foreach ($outUCI as $key => $value) {
                $valores = explode('=', $value, 2);

                $valor = $valores[1];

                $variables = explode('.', $valores[0]);

                $id = $variables[1];

                if ((strpos($id, "@")) !== false) {
                    if ($c = preg_match_all("/.*?((?:[a-z][a-z0-9_]*)).*?(\\d+)/is", $id, $matches)) {
                        $var = $matches[1][0];
                        $int = $matches[2][0];
                        $method = true;
                    }
                } else {
                    $method = false;
                }

                $namevariable = @$variables[2];

                if ((empty($namevariable)) || ($namevariable == "")) {
                    if ($method) {
                        $result[$var][$int]["id"] = $int;
                    } else {
                        $result[$id]["id"] = $id;
                    }
                } else {
                    if ($method) {
                        $result[$var][$int][$namevariable] = $valor;
                    } else {
                        $result[$id][$namevariable] = $valor;
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function commit($value = '')
    {
        exec("uci show $value", $out);

        if ($out == 'uci: Entry not found') {
            return false;
        } elseif ($out == 'uci: I/O error') {
            return false;
        } else {
            return $out;
        }
    }

    public function set($name, $value = '')
    {
        exec("uci set $name=$value", $out);

        if ($out == 'uci: Entry not found') {
            return false;
        } elseif ($out == 'uci: I/O error') {
            return false;
        } else {
            return $out;
        }
    }

    public function add($name, $value)
    {
        exec("uci add $name $value", $out);

        if ($out == 'uci: Entry not found') {
            return false;
        } elseif ($out == 'uci: I/O error') {
            return false;
        } else {
            return $out;
        }
    }

    public function setArray($array, $name)
    {
        var_dump($array);
        $result = '';
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                foreach ($value as $keyValue => $valueUCI) {
                    $result .= "uci set $name.$key.$keyValue='$valueUCI' \n";
                }
            } else {
                foreach ($value as $keyValue => $valueUCI) {
                    foreach ($valueUCI as $keyArray3 => $valueArray3) {
                        $result .= "uci set $name.@" . $key . '[' . $keyValue . "].$keyArray3='$valueArray3' \n";
                    }

                }
            }
        }
        return $result;
    }

}
?>