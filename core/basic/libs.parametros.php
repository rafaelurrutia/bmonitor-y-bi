<?php
if (!class_exists("Parametros")) {
    Class Parametros Implements ArrayAccess
    {

        private $vars = array();

        function set($key, $var)
        {
            if (isset($this -> vars[$key]) == true) {
                throw new Exception('Imposible asignar la variable `' . $key . '`. por que se asigno anteriormente');
            }
            $this -> vars[$key] = $var;
            return true;
        }

        function get($key, $default = false)
        {
            if (isset($this -> vars[$key]) == false) {
                if ($default != false) {
                    return $default;
                }
                return null;
            }

            return $this -> vars[$key];
        }

        function remove($key)
        {
            unset($this -> vars[$key]);
        }

        function offsetExists($offset)
        {
            return isset($this -> vars[$offset]);
        }

        function offsetGet($offset)
        {
            return $this -> get($offset);
        }

        function offsetSet($offset, $value)
        {
            $this -> set($offset, $value);
        }

        function offsetUnset($offset)
        {
            unset($this -> vars[$offset]);
        }

    }

}
?>