<?php
	/**
	 * Basic.
	 *
	 * PHP version 5
	 *
	 * @category  Core
	 * @package   KAIRO
	 * @author    Carlos Lazcano <carlos@clazcano.cl>
	 * @copyright 2013 Clazcano SPA
	 * @license   http://www.clacano.cl/licence BSD Licence
	 * @version   SVN: <13>
	 * @link      http://www.clazcano.cl
	 */

	/**
	 * Basic.
	 *
	 * The Kairo Basic Class.
	 *
	 * @category  Core
	 * @package   Core.Basic
	 * @author    Carlos Lazcano <carlos@clazcano.cl>
	 * @copyright 2013 Clazcano SPA
	 * @license   http://www.clazcano.cl/licence BSD Licence
	 * @version   Release: 1.1.0
	 * @link      http://www.clazcano.cl
	 */
	class Basic {
		protected $key = 'perro$$muerde&&al$$que33Roba';

		/**
		 * Contrunct functions, set param.
		 *
		 * @author Carlos Lazcano
		 *
		 * @param mixed[] $parametro Array structure
		 * @param mixed[] $conexion  Array structure
		 * @param mixed[] $logs      Array structure
		 * @param mixed[] $lang      Array structure*
		 *
		 * @return none
		 */

		public function __construct( $parametro, $conexion, $logs, $lang = false ) {
			$this->parametro = $parametro;
			$this->conexion = $conexion;
			$this->logs = $logs;
			if($lang !== false) {
				$this->lang = $lang;
			} else {
				$this->lang = (object) array(
					"SIN_COMUNA" => 'Sin comuna',
					"SIN_REGION" => 'Sin region',
					"SIN_PROVINCIA" => 'Sin provincia',
					"NEVER" => 'Nunca'
				);
			}
		}

		/**
		 * Descrypted text.
		 *
		 * @param string $encrypted String desencrypted
		 * @param string $key       The key with which the data was encrypted
		 *
		 * @return string
		 */

		public function descrypt( $encrypted, $key = false ) {
			if($key == false) {
				$key = $this->key;
			}
			if( ! function_exists('mcrypt_encrypt')) {
				return false;
			}
			// @formatter:off
			$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
			// @formatter:on
			return $decrypted;
		}

		/**
		 * Encrypted text.
		 *
		 * @param string $string String encrypted
		 * @param string $key    The key with which the data was encrypted
		 * @param string $iv     Text used as a seed for encryption
		 *
		 * @return string
		 */

		public function encrypted( $string, $key = false, $iv = false ) {

			if($key == false) {
				$key = $this->key;
			}

			if($iv == false) {
				$iv = md5(md5($key));
			}

			if( ! function_exists('mcrypt_encrypt')) {
				$this->logs->error("Error function mcrypt_encrypt not exists");
				return false;
			}
			// @formatter:off
			$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
			// @formatter:on
			return $encrypted;
		}

		/**
		 * XML to array.
		 *
		 * @param string $input String XML
		 *
		 * @return object
		 */

		public function getXML( $input ) {
			$data = simplexml_load_string($input);
			if( ! is_object($data)) {
				return false;
			}
			return $data;
		}

		/**
		 * XML to array recursive.
		 *
		 * @param string $input    String XML
		 * @param string $callback return result method
		 *
		 * @return object
		 */

		public function loadXML( $input, $callback = null ) {
			if($input != '') {
				return (object)$this->loadXML2($input, $callback);
			} else {
				return false;
			}

		}

		/**
		 * XML to array util method .
		 *
		 * @param string $input    String XML
		 * @param string $callback return result method
		 * @param bool   $recurse  second call is true
		 *
		 * @return object
		 */

		public function loadXML2( $input, $callback = null, $recurse = false ) {

			if(( ! $recurse) && is_string($input)) {
				$data = simplexml_load_string($input);
				if( ! is_object($data)) {
					return false;
				}
			} else {
				$data = $input;
			}

			if($data instanceof SimpleXMLElement) {
				$data = (array)$data;
			}

			if(is_array($data)) {
				foreach ($data as &$item) {
					$item = $this->loadXML2($item, $callback, true);
					if( ! is_array($item)) {
						if(is_numeric($item)) {
							$item = (int)$item;
						} elseif($item == "true") {
							$item = true;
						} elseif($item == "false") {
							$item = false;
						}
					}
				}
			}

			if( ! is_array($data) && is_callable($callback)) {
				return call_user_func($callback, $data);
			} else {
				return $data;
			}
		}

		/**
		 * Gets the location by id.
		 *
		 * @param string $type      type location comuna,region,provincia
		 * @param string $value     location id
		 * @param bool   $resultTxt return null or text %location not found
		 *
		 * @return object
		 */

		public function getLocalidad( $type, $value, $resultTxt = false ) {
			$empty = false;
			if(( ! isset($value)) || ( ! is_numeric($value)) || ($value == '')) {
				if($resultTxt == true) {
					$empty = true;
				} else {
					return '';
				}
			}

			switch ($type) {
				case 'comuna' :
					if($empty) {
						return $this->lang->SIN_COMUNA;
					}

					$sql = "SELECT `COMUNA_NOMBRE` FROM `bm_comuna`";
					$sql .= "WHERE `COMUNA_ID`=$value";
					$result = $this->conexion->queryFetch($sql);

					if($result) {
						return $result[0]['COMUNA_NOMBRE'];
					} else {
						return '';
					}
					break;
				case 'region' :
					if($empty) {
						return $this->lang->SIN_REGION;
					}

					$sql = "SELECT `REGION_NOMBRE` FROM `bm_region`";
					$sql .= "WHERE `REGION_ID`=$value";
					$result = $this->conexion->queryFetch($sql);

					if($result) {
						return $result[0]['REGION_NOMBRE'];
					} else {
						return '';
					}
					break;
				case 'provincia' :
					if($empty) {
						return $this->lang->SIN_PROVINCIA;
					}
					$sql = "SELECT `PROVINCIA_NOMBRE` FROM `bm_provincia`";
					$sql .= "WHERE `PROVINCIA_ID`=$value";
					$result = $this->conexion->queryFetch($sql);

					if($result) {
						return $result[0]['PROVINCIA_NOMBRE'];
					} else {
						return '';
					}
					break;
				default :
					return false;
					break;
			}
		}

		/**
		 * Gets the location by id.
		 *
		 * @param string $type   type location comuna,region,provincia
		 * @param string $value  location id
		 * @param bool   $select default selection
		 *
		 * @return object
		 */

		public function getLocalidadOption( $type, $value = false, $select = false ) {
			$where = '';
			if(($value) && ( ! is_numeric($value))) {
				return false;
			}
			switch ($type) {
				case 'comuna' :
					if($value) {
						$where = ' WHERE `COMUNA_PROVINCIA_ID` =' .$value;
					}
					$sql = "SELECT `COMUNA_NOMBRE`, `COMUNA_ID` FROM `bm_comuna`";
					$R = $this->conexion->queryFetch($sql .$where);
					return $this->getOption($R, 'COMUNA_ID', 'COMUNA_NOMBRE', $select);
					break;
				case 'region' :
					$sql = "SELECT `REGION_ID` , `REGION_NOMBRE` FROM `bm_region`";
					$R = $this->conexion->queryFetch($sql .$where);
					return $this->getOption($R, 'REGION_ID', 'REGION_NOMBRE', $select);
					break;
				case 'provincia' :
					if($value) {
						$where = ' WHERE `PROVINCIA_REGION_ID` =' .$value;
					}
					$sql = "SELECT `PROVINCIA_ID` , `PROVINCIA_NOMBRE` FROM `bm_provincia`";
					$R = $this->conexion->queryFetch($sql .$where);
					return $this->getOption($R, 'PROVINCIA_ID', 'PROVINCIA_NOMBRE', $select);
					break;
				default :
					return false;
					break;
			}
		}

		public function getLocation( $type, $value = false, $select = false ) {

			switch ($type) {
				case 'continent' :
					$getSQL = 'SELECT `code`, `name`, `geonameid` FROM `geonames`.`continentCodes`';
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $this->getOption($getRESULT, 'code', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'country' :
					$getSQL = "SELECT G.`country`,C.`name`, C.`capital` FROM  `geonames`.`countryinfo` C
                                    LEFT JOIN `geonames`.`geoname` G ON C.`geonameid`=G.`geonameid`
                                    WHERE `continent` = '$value' LIMIT 0,200 ";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $this->getOption($getRESULT, 'country', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'state' :
					$getSQL = "SELECT A.`code`, G.`name`, G.`geonameid`, G.`timezone` FROM `geonames`.`admin1CodesAscii` A 
                        LEFT JOIN `geonames`.`geoname` G ON A.`geonameid`=G.`geonameid`  
                            WHERE A.`code` LIKE '$value%' ORDER BY G.`name` LIMIT 0,200";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $this->getOption($getRESULT, 'code', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'region' :
					$getSQL = "SELECT  A.`code`, G.`name`, G.`geonameid`, G.`timezone` FROM `geonames`.`admin2Codes` A 
                                    LEFT JOIN `geonames`.`geoname` G ON A.`geonameid`=G.`geonameid`  
                                        WHERE A.`code` LIKE '$value%' ORDER BY G.`name` LIMIT 0,200";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $this->getOption($getRESULT, 'geonameid', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'city' :
					$getSQL = "SELECT G.`geonameid`,G.`name`,G.`asciiname` 
                                FROM `geonames`.`hierarchy` H 
                                        LEFT JOIN `geonames`.`geoname` G ON H.`childId`=G.`geonameid` 
                                            WHERE H.`parentId` = '$value' LIMIT 0,200";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $this->getOption($getRESULT, 'geonameid', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'other' :
					list( $country, $admin1 ) = explode('.', $value);
					$getSQL = "  SELECT DISTINCT g.`geonameid`,g.`name`,g.`asciiname`  FROM `geonames`.geoname g  
                        INNER JOIN `geonames`.`location` l ON g.`geonameid`=l.`geonameid` 
                     WHERE     l.`country` = '$country' AND l.admin1 = $admin1
                     GROUP BY g.geonameid
                     ORDER BY g.`geonameid`  LIMIT 0,400;";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						if($select == false) {
							$select = '-first-';
						}
						return $this->getOption($getRESULT, 'geonameid', 'name', $select);
					} else {
						return false;
					}
					break;
				case 'bmLocation' :
					$getSQL = "SELECT `idLocation`, `city` FROM `bm_location` WHERE `idState` = '$value'";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						if($select == false) {
							$select = '-first-';
						}
						return $this->getOption($getRESULT, 'idLocation', 'city', $select);
					} else {
						return false;
					}
					break;
				default :
					return '<option>None</option>';
					break;
			}
		}

		/*
		public function getLocationValue( $geonameid ) {
			if(is_numeric($geonameid)) {
				$getGeoLocationSQL = "SELECT `name`, `asciiname`,`latitude`,`longitude`,`population`,`elevation`,`timezone` 
                            FROM `geonames`.`geoname` WHERE `geonameid`=$geonameid LIMIT 1";
				$getGeoLocationRESULT = $this->conexion->queryFetch($getGeoLocationSQL);
				if($getGeoLocationRESULT) {
					$return = (object)$getGeoLocationRESULT[0];
					$return->latitude = $this->convertDECtoDMS($return->latitude);
					$return->longitude = $this->convertDECtoDMS($return->longitude);
					return $return;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}*/

		public function getLocationValue( $type, $value = false ) {

			switch ($type) {
				case 'continent' :
					$getSQL = 'SELECT `code`, `name`, `geonameid` FROM `geonames`.`continentCodes` WHERE `code` = \'' .$value ."' LIMIT 1";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $getRESULT[0]['name'];
					} else {
						return false;
					}
					break;
				case 'country' :
					$getSQL = "SELECT G.`country`,C.`name`, C.`capital` FROM  `geonames`.`countryinfo` C
                                    LEFT JOIN `geonames`.`geoname` G ON C.`geonameid`=G.`geonameid`
                                    WHERE  G.`country` = '$value' LIMIT 1";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $getRESULT[0]['name'];
					} else {
						return false;
					}
					break;
				case 'state' :
					$getSQL = "SELECT A.`code`, G.`name`, G.`geonameid`, G.`timezone` FROM `geonames`.`admin1CodesAscii` A 
                        LEFT JOIN `geonames`.`geoname` G ON A.`geonameid`=G.`geonameid`  
                            WHERE A.`code` = '$value' ORDER BY G.`name` LIMIT 1";
					$getRESULT = $this->conexion->queryFetch($getSQL);
					if($getRESULT) {
						return $getRESULT[0]['name'];
					} else {
						return false;
					}
					break;
				case 'other' :
					if(is_numeric($type)) {
						$getGeoLocationSQL = "SELECT `name`, `asciiname`,`latitude`,`longitude`,`population`,`elevation`,`timezone` 
                                    FROM `geonames`.`geoname` WHERE `geonameid`=$type LIMIT 1";
						$getGeoLocationRESULT = $this->conexion->queryFetch($getGeoLocationSQL);
						if($getGeoLocationRESULT) {
							$return = (object)$getGeoLocationRESULT[0];
							$return->latitude = $this->convertDECtoDMS($return->latitude);
							$return->longitude = $this->convertDECtoDMS($return->longitude);
							return $return;
						} else {
							return false;
						}
					} else {
						return false;
					}
					break;
				default :
					if(is_numeric($type)) {
						$getGeoLocationSQL = "SELECT `name`, `asciiname`,`latitude`,`longitude`,`population`,`elevation`,`timezone` 
                                    FROM `geonames`.`geoname` WHERE `geonameid`=$type LIMIT 1";
						$getGeoLocationRESULT = $this->conexion->queryFetch($getGeoLocationSQL);
						if($getGeoLocationRESULT) {
							$return = (object)$getGeoLocationRESULT[0];
							$return->latitude = $this->convertDECtoDMS($return->latitude);
							$return->longitude = $this->convertDECtoDMS($return->longitude);
							return $return;
						} else {
							return false;
						}
					} else {
						return false;
					}
					break;
			}
		}

		public function date2str( $format, $timestamp ) {
			if($timestamp == 0) {
				return $this->lang->NEVER;
			} else {
				return date($format, $timestamp);
			}
		}

		public function encode( $value ) {
			return utf8_encode($value);
		}

		public function limpiarMetas( $string, $corte = null ) {
			$s = trim($string);
			$s = html_entity_decode($s, ENT_COMPAT, 'UTF-8');
			$s = stripslashes($s);
			$s = htmlentities($s);
			$s = strip_tags($s);
			$s = preg_replace('/(?<!>)n/', "<br />n", $s);
			$s = preg_replace('/\r\n/', "\n", $s);
			$s = str_replace('+%3D+', ' = ', $s);
			$s = str_replace('%0D%0A', '\n', $s);
			$s = str_replace('%23', '#', $s);
			$s = str_replace('%2F', '/', $s);

			$s = preg_replace('/[ ]+/', ' ', $s);
			$s = preg_replace('/^M/', ' ', $s);
			$s = preg_replace('/<!--[^-]*-->/', '', $s);
			$s = strip_tags($s);
			if(isset($corte) && (is_numeric($corte))) {
				$s = mb_substr($s, 0, $corte, 'UTF-8');
			}

			return $s;
		}

		#Deprecate

		public function limpiar_metas( $string, $corte = null ) {
			return $this->limpiarMetas($string, $corte);
		}

		public function getOption( $array, $value, $name, $op = false, $msg = false ) {

			$option = '';
			if($op === 'none') {
				$selec = false;
			} else {
				$selec = true;
			}
			if( ! is_array($array)) {
				return false;
			}

			foreach ($array as $key => $grupo) {
				$selected = '';
				$ConcatMSG = '';
				if(($op !== false) && ($op !== 'all') && ($op !== 'select')) {

					if(isset($grupo['type']) && $grupo['type'] == 'string') {
						$value_option = (string)$grupo[$value];
						$op = (string)$op;
					} elseif(isset($grupo['type']) && $grupo['type'] == 'int') {
						$value_option = (int)$grupo[$value];
						$op = (int)$op;
					} else {
						$value_option = $grupo[$value];
					}

					if($value_option == $op) {
						$selected = 'selected';
						$this->optionSelectName = $grupo[$name];
						if($msg) {
							$ConcatMSG = $msg;
						}
						$selec = false;
					}
				}

				$option .= sprintf('<option %s value="%s">%s%s</option>', $selected, $grupo[$value], $grupo[$name], $ConcatMSG);

			}

			if($op === 'all') {
				$option = $option .'<option value="0">' .$this->lang->ALL .'</option>';
			} elseif($op === "-first-") {
				return $this->fixEncoding($option);
			} elseif(($op === 'select') || ($selec)) {
				$option = '<option selected value="0">' .$this->lang->SELECT .'</option>' .$option;
			} elseif($op === 'select-all') {
				$option = '<option selected value="0">' .$this->lang->SELECT .'</option>' .$option;
				$option .= '<option value="0">' .$this->lang->ALL .'</option>';
			}

			return $this->fixEncoding($option);
		}

		public function optionSelectName( ) {
			return $this->optionSelectName;
		}

		public function getOptionValue( $option, $default = false ) {
			if(isset($option)) {
				$get_option_sql = "SELECT * FROM `bm_option`";
				$get_option_sql .= " WHERE `option_group`='$option'";
				$get_option_sql .= " ORDER BY `orden` ASC";

				$get_option_result = $this->conexion->queryFetch($get_option_sql);

				if($get_option_result) {

					return $this->getOption($get_option_result, 'id_option', 'option', $default);

				} else {
					return false;
				}
			}
		}

		public function getOptionName( $option, $value ) {
			$get_option_sql = "SELECT `option` FROM `bm_option` WHERE";
			$get_option_sql .= " `option_group`='$option' AND `id_option` = '$value' ";
			$get_option_sql .= " ORDER BY `orden` ASC";
			$get_option_result = $this->conexion->queryFetch($get_option_sql);
			if($get_option_result) {

				return $get_option_result[0]['option'];

			} else {
				return false;
			}

		}

		public function arrayKeyToValue( $array, $key, $name ) {

			if(is_array($array)) {
				$new_array = array();
				foreach ($array as $value) {
					$new_array[$value[$key]] = $value[$name];
				}
				return $new_array;
			} else {
				return false;
			}
		}

		function ordenar( &$aTabla, $aCampos ) {
			$aSalida = array();
			foreach ($aCampos as $sCampo => $sOrden) {
				if($sOrden == 'ASC') {
					$s1 = '>';
					$s2 = '<';
				} else {
					$s1 = '<';
					$s2 = '>';
				}
				$aSalida[] = <<<HERE
                if(array_key_exists('$sCampo',\$a)) {
                    if(\$a['$sCampo'] $s1 \$b['$sCampo']) {
                        return 1;
                    } elseif (\$a['$sCampo'] $s2 \$b['$sCampo']) {
                        return -1;
                    }
                }
HERE;
			}
			$aSalida[] = 'return 0;';
			uasort($aTabla, create_function('$a, $b', implode("\n", $aSalida)));
		}

		public function setTableId( $table_id ) {
			unset($this->table_toolbox);
			$this->table_toolbox_param = '';
			$this->table_id = $table_id;
			$this->table_toolbox_buttom = '';
			unset($this->tableSearch);
		}

		#Deprecate

		public function setTable_id( $tableId ) {
			$this->setTableId($tableId);
		}

		public function setTable_url($table_url)
		{
			$this->setTableUrl($table_url);
		}

		public function setTableUrl( $table_url ) {
			$this->table_url = $table_url;
		}

		public function setTable_toolbox( $table_toolbox, $buttons = false ) {
			$this->setTableToolbox($table_toolbox,$buttons);
		}
		
		public function setTableToolbox( $table_toolbox, $buttons = false ) {
			$this->table_toolbox = $table_toolbox;

			if($buttons) {

				if(is_array($buttons)) {

					foreach ($buttons as $key => $button) {

						$format = "{name: '%s', bclass: '%s', onpress : %s}";
						$table_toolbox_buttom[] = sprintf($format, $button['name'], $button['bclass'], $table_toolbox);
					}

					$this->table_toolbox_buttom = 'buttons : [' ."\n" .join(",\n", $table_toolbox_buttom) ."\n" .' ],';
				} else {
					$this->table_toolbox_buttom = '';
				}

			} else {
				$this->table_toolbox_buttom = 'buttons : [' ."\n" ."{name: '" .$this->lang->NEW ."', bclass: 'add', onpress : $table_toolbox},\n{name: '" .$this->lang->DELETE ."', bclass: 'delete', onpress : $table_toolbox}" ."\n" .' ],';
			}

			$this->table_toolbox_param = ' showTableToggleBtn: true,';
		}

		public function setTable_colModel($table = false)
		{
			$this->setTableColModel($table);
		}

		public function setTableColModel( $table = false ) {
			if(is_array($table)) {

				foreach ($table as $key => $vl) {

					if($vl['width'] == 'none') {
						$width = '';
					} else {
						$width = ", width : '" .$vl['width'] ."'";
					}

					$template = "{display: '%s', name : '%s'  %s  , sortable : %s , align: '%s'}";
					$setVar = sprintf($template, $vl['display'], $vl['feature'], $width, $vl['sortable'], $vl['align']);
					$colModel[] = $setVar ."\n";
				}

				$colModel_final = join(',', $colModel);

				$this->colModel = utf8_encode($colModel_final);

			} elseif($table !== false) {
				$get_table_sql = "SELECT HF.`display`,HF.`feature`, HT.`align`,HT.`width`,HT.`sortable`
            FROM `bm_host_table` HT   
            LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
            WHERE HT.`table`='$table' ORDER BY HT.`orden` ASC;";

				$get_table_result = $this->conexion->queryFetch($get_table_sql);

				if($get_table_result) {

					$colModel = array();

					foreach ($get_table_result as $key => $vl) {
						if($vl['width'] == 'none') {
							$width = '';
						} else {
							$width = ", width : '" .$vl['width'] ."'";
						}

						$template = "{display: '%s', name : '%s'  %s  , sortable : %s , align: '%s'}";
						$colModel[] = sprintf($template, $vl['display'], $vl['feature'], $width, $vl['sortable'], $vl['align']);
					}

					$colModel_final = join(",\n", $colModel);

					$this->colModel = utf8_encode($colModel_final);

				} else {
					$this->colModel = false;
				}
			} else {
				$this->colModel = false;
			}
		}

		public function setTableSearch( $display, $name, $isdefault = 'false' ) {
			if( ! isset($this->tableSearch)) {
				$this->tableSearch = "{display: '$display', name : '$name', isdefault: $isdefault}";
			} else {
				$this->tableSearch .= ",{display: '$display', name : '$name', isdefault: $isdefault}";
			}

		}

		public function getTableFL( $title, $width = 'auto', $height = 'auto', $classOrId = '.', $tableIdParam = true ) {
			$filter = $this->table_id ."Filter";

			$objectTable = $classOrId .$this->table_id;

			if(isset($this->tableSearch)) {
				$tableSearch = <<<HERE
searchitems : [
            $this->tableSearch
        ],
HERE;
			} else {
				$tableSearch = '';
			}

			$tamano = '';

			if($width != 'auto') {
				$tamano .= "width: $width,";
			}

			if($height != 'auto') {
				$tamano .= "height: $height,";
			} else {
				$tamano .= "height: 'auto',";
			}

			if($tableIdParam == true) {
				$tableIdParam = "idFlexigrid: '$this->table_id',";
			} else {
				$tableIdParam = '';
			}

			$aSalida = <<<HERE
        
    $this->table_id = $("$objectTable").flexigrid({
        url: '$this->table_url',
        title: '$title',
        $tableIdParam
        dataType: 'json',
        colModel : [
            $this->colModel
        ],
        $this->table_toolbox_buttom
        $tableSearch
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        $this->table_toolbox_param
        resizable: true,
        onSubmit : function(){
            $('$objectTable').flexOptions({params: [{
                       name:'callId', 
                       value:'$this->table_id'
            }].concat($('#$filter').serializeArray())});
            return true;
        },
        $tamano
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    }); 
HERE;

			return $this->fixEncoding($aSalida);
		}

		public function getTable( $title, $width = 'auto', $height = 'auto', $resizable = 'true' ) {
			$filter = $this->table_id ."Filter";

			if(isset($this->tableSearch)) {
				$tableSearch = <<<HERE
searchitems : [
            $this->tableSearch
        ],
HERE;
			} else {
				$tableSearch = '';
			}

			$tamano = '';

			if($width != 'auto') {
				$tamano .= "width: $width,";
			}

			if($height != 'auto') {
				$tamano .= "height: $height,";
			} else {
				$tamano .= "height: 'auto',";
			}

			$aSalida = <<<HERE
        
    $("#$this->table_id").flexigrid({
        url: '$this->table_url',
        title: '$title',
        dataType: 'json',
        colModel : [
            $this->colModel
        ],
        $this->table_toolbox_buttom
        $tableSearch
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        $this->table_toolbox_param
        resizable: $resizable,
        onSubmit : function(){
            $('.$this->table_id').flexOptions({params: [{
                       name:'callId', 
                       value:'$this->table_id'
            }].concat($('#$filter').serializeArray())});
            return true;
        },
        $tamano
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    }); 
HERE;

			return $this->fixEncoding($aSalida);
		}

		public function setParamTable( $var = false, $sortname = 'id', $sortorder = 'desc' ) {
			$param = (object) array();
			if($var) {
				//Valores iniciales , libreria flexigrid
				$param->page = 1;
				$param->sortname = $sortname;
				$param->sortorder = $sortorder;
				$param->qtype = '';
				$param->query = '';
				$param->rp = 15;

				// Validaciones de los parametros enviados por la libreria
				// flexigrid
				if(isset($var['page'])) {
					$param->page = $var['page'];
				}
				if(isset($var['sortname']) && ($var['sortname'] != 'undefined') && ($var['sortname'] != '')) {
					$param->sortname = $var['sortname'];
				}
				if(isset($var['sortorder']) && ($var['sortorder'] != 'undefined') && ($var['sortorder'] != '')) {
					$param->sortorder = $var['sortorder'];
				}
				if(isset($var['qtype'])) {
					$param->qtype = $var['qtype'];
				}
				if(isset($var['query']) && $var['query'] != '') {
					if($var['query'] == 'NULL') {
						$param->query = "(`" .$param->qtype ."` IS NULL OR " .$param->qtype ." = '0' )";
					} else {
						$param->query = "`$param->qtype` LIKE " .$this->conexion->quote($var['query']);
					}
				}
				
				$infinity = false;
				
				if(isset($var['rp'])) {
					if($var['rp'] == 'NaN'){
						$param->rp = 99999;
						$infinity = true;
					} else {
						$param->rp = trim($var['rp']);
					}		
				} 

				$pos = strpos($param->sortname, '`');

				if($pos === false) {
					$param->sortname = "`$param->sortname`";
				}

				$param->sortSql = "ORDER BY $param->sortname $param->sortorder";

				if( ! isset($var['where'])) {
					$where = 'WHERE';
				} else {
					$where = 'AND ';
				}

				$param->searchSql = ($param->qtype != '' && $param->query != '') ? "$where $param->query" : '';

				if($infinity === false){
					$pageStart = ($param->page - 1) * $param->rp;
					$param->limitSql = " LIMIT $pageStart, $param->rp";
				} else {
					$param->limitSql = " LIMIT 0,10000000";
				}

				return (object)$param;
			}
		}

		public function button( $idbutton, $label, $onclick = false, $icon = false ) {
			if($onclick) {
				$onclick = 'onclick="' .$onclick .'"';
			} else {
				$onclick = '';
			}

			if($icon) {
				$icon = '<span class="ui-button-icon-primary ui-icon ' .$icon .'"></span>';
			} else {
				$icon = '';
			}

			$button = '<button id="' .$idbutton .'" ' .$onclick .' class="ui-button ui-widget ui-state-default 
                        ui-corner-all ui-button-text-icon-primary" role="button" aria-disabled="false">
                    ' .$icon .'<span class="ui-button-text">' .$label .'</span></button>';

			return $button;
		}

		function mail( $to, $from_user, $from_email, $subject = '(No subject)', $message = '' ) {
			$from_user = "=?UTF-8?B?" .base64_encode($from_user) ."?=";
			$subject = "=?UTF-8?B?" .base64_encode($subject) ."?=";
			$headers = "From: $from_user <$from_email>\r\n" ."MIME-Version: 1.0" ."\r\n";
			$headers .= "Content-type: text/html; charset=UTF-8" ."\r\n";
			$toArray=explode(',', $to);
			if(count($toArray) > 1){
				foreach ($toArray as $key => $value) {
					if($key == 0){
						$to = trim($value);
					} else {
						$headers .= 'Cc: '.trim($value). "\r\n";
					}
				}
			}
			return mail($to, $subject, $message, $headers);
		}

		public function cdateForDate( $fecha, $horarios = false, $feriados = false ) {
			$date = date("d/m", strtotime($fecha));
			$today = date("N", strtotime($fecha));
			$current_time = date("H", strtotime($fecha)) * 60 * 60 + date("i", strtotime($fecha)) * 60;

			if(str_replace($date, "", $feriados) != $feriados) {
				return false;
			}

			if($horarios == false || $horarios == '') {
				return false;
			}

			$horarios_array = explode(";", $horarios);

			foreach ($horarios_array as $key => $horario) {

				if($horario != '') {

					list( $days, $hours ) = explode(",", $horario);

					//Days param
					$days_range = explode("-", $days);

					if(count($days_range) == 2) {
						$day_start = $days_range[0];
						$day_end = $days_range[1];
					} else {
						$day_start = $days_range[0];
						$day_end = $day_start;
					}

					//Hours param

					$hours_range = explode("-", $hours);

					if(count($hours_range) == 2) {
						$x = explode(":", $hours_range[0]);
						$hour_start = $x[0] * 60 * 60 + $x[1] * 60;
						$x = explode(":", $hours_range[1]);
						$hour_end = $x[0] * 60 * 60 + $x[1] * 60;
					} else {
						$x = explode(":", $hours_range[0]);
						$hour_start = $x[0] * 60 * 60 + $x[1] * 60;
						$hour_end = $hour_start;
					}

					// Valid schedule

					if(($today >= $day_start) && ($today <= $day_end)) {

						if(($current_time >= $hour_start) && ($current_time <= $hour_end)) {
							return true;
						}

					}

				}
			}

			return false;
		}

		public function cdate( $horarios = false, $feriados = false ) {
			$date = date("d/m");
			$today = date("N");
			$current_time = date("H") * 60 * 60 + date("i") * 60;

			if(str_replace($date, "", $feriados) != $feriados) {
				return false;
			}

			if($horarios == false || $horarios == '') {
				return false;
			}

			$horarios_array = explode(";", $horarios);

			foreach ($horarios_array as $key => $horario) {

				if($horario != '') {

					list( $days, $hours ) = explode(",", $horario);

					//Days param
					$days_range = explode("-", $days);

					if(count($days_range) == 2) {
						$day_start = $days_range[0];
						$day_end = $days_range[1];
					} else {
						$day_start = $days_range[0];
						$day_end = $day_start;
					}

					//Hours param

					$hours_range = explode("-", $hours);

					if(count($hours_range) == 2) {
						$x = explode(":", $hours_range[0]);
						$hour_start = $x[0] * 60 * 60 + $x[1] * 60;
						$x = explode(":", $hours_range[1]);
						$hour_end = $x[0] * 60 * 60 + $x[1] * 60;
					} else {
						$x = explode(":", $hours_range[0]);
						$hour_start = $x[0] * 60 * 60 + $x[1] * 60;
						$hour_end = $hour_start;
					}

					// Valid schedule

					if(($today >= $day_start) && ($today <= $day_end)) {

						if(($current_time >= $hour_start) && ($current_time <= $hour_end)) {
							return true;
						}

					}

				}
			}

			return false;
		}

		public function cdateObj( $horarios = false, $feriados = false, $time = false ) {
			if($time && is_numeric($time)) {
				$date = date("d/m", $time);
				$today = date("N", $time);
				$current_time = $time;
				$dateNOW = date("Y-m-d", $time);
			} else {
				$date = date("d/m");
				$today = date("N");
				$current_time = time();
				$dateNOW = date("Y-m-d");
			}

			if(str_replace($date, "", $feriados) != $feriados) {
				return false;
			}

			if($horarios == false || $horarios == '') {
				return false;
			}

			$horarios_array = explode(";", $horarios);
			$count = 0;
			foreach ($horarios_array as $key => $horario) {

				if($horario != '') {

					list( $days, $hours ) = explode(",", $horario);

					//Days param
					$days_range = explode("-", $days);

					if(count($days_range) == 2) {
						$day_start = $days_range[0];
						$day_end = $days_range[1];
					} else {
						$day_start = $days_range[0];
						$day_end = $day_start;
					}

					//Hours param

					$hours_range = explode("-", $hours);

					if(count($hours_range) == 2) {
						$hour_start = strtotime($dateNOW ." " .$hours_range[0] .":00");
						$hour_end = strtotime($dateNOW ." " .$hours_range[1] .":00");
					} else {
						$hour_start = strtotime($dateNOW ." " .$hours_range[0] .":00");
						$hour_end = $hour_start;
					}

					// Valid schedule

					if(($today >= $day_start) && ($today <= $day_end)) {

						if(($current_time >= $hour_start) && ($current_time <= $hour_end)) {
							return true;
						} else {
							if($current_time < $hour_start) {
								if($count == 0 || ($marca > $hour_start)) {
									$result['nexcheck'] = date("Y-m-d H:i:s", $hour_start); ;
									$result['unixtime'] = $hour_start;
									$result['status'] = false;
									$marca = $hour_start;
									$marca = true;
									$result_send = true;
								}
								$count++;
							} elseif($current_time > $hour_end) {
								if( ! isset($marca)) {
									$result['nexcheck'] = date("Y-m-d H:i:s", strtotime($dateNOW ." " ."23:59:59"));
									$result['unixtime'] = strtotime($dateNOW ." " ."23:59:59");
									$result['status'] = false;
									$result_send = true;
								}
							}
						}
					}
				}
			}

			if(isset($result_send) && $result_send == true) {
				return (object)$result;
			}

			return false;
		}

		public function fixEncoding( $string = '' ) {
			if(class_exists("Encoding")) {
				try {
					$string1 = Encoding::toUTF8($string);
					return Encoding::fixUTF8($string1);
				} catch (Exception $e) {
					return $string;
				}
			} else {
				return $string;
			}
		}

		public function cacheDeleteAll( ) {
			$result = array_map('unlink', glob(SITE_PATH ."cache/*.cache"));
		}

		public function convertDMStoDEC( $deg, $min, $sec ) {
			$decimal = ((($min * 60) + ($sec)) / 3600);
			$decimal = explode('.', $decimal);
			return $deg .'.' .$decimal[1];
		}

		public function convertDECtoDMS( $dec ) {
			$vars = explode(".", $dec);
			$deg = $vars[0];
			$tempma = "0." .$vars[1];
			$tempma = $tempma * 3600;
			$min = floor($tempma / 60);
			$sec = $tempma - ($min * 60);
			return array(
				"deg" => $deg,
				"min" => $min,
				"sec" => $sec
			);
		}

		function cleanSpecialCharacters( $string ) {
			$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,Á,É,Í,Ó,Ú");
			$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,A,E,I,O,U");
			$string_limpio = str_replace($search, $replace, $string);
			$string_limpio = preg_replace('/\s+/', '_', $string_limpio);
			$string_limpio = preg_replace("/[^0-9a-zA-Z\-\_]/", "", $string_limpio);
			return $string_limpio;
		}

		public function jsonEncode( $string, $encoding = true ) {
			$encoding = true;
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: text/json');
			header("Connection: close");

			if($encoding != false) {
				$string = $this->fixEncoding($string);
			}

			echo json_encode($string);
		}

		public function getTimeZoneOffset( $offsetSelect = 'GMT', $timezoneName = false ) {
			if($timezoneName) {
				$timezone = new DateTimeZone( $timezoneName );
			} else {
				$timezone = new DateTimeZone( date_default_timezone_get() );
			}

			$dateTime = new DateTime( "now", $timezone );

			$offset = $timezone->getOffset($dateTime);

			$offsetHours = round(abs($offset) / 3600);
			$offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);

			if($offsetSelect === 'GMT') {
				$offsetString = "GMT" .($offset < 0 ? '-' : '+') .$offsetHours;
			} elseif($offsetSelect === 'UTC') {
				$offsetString = "UTC" .($offset < 0 ? '+' : '-') .$offsetHours;
			} else {
				return false;
			}

			return $offsetString;

		}

		public function getPercentil( $data, $percentile, $type = 'INC', $order = FALSE, $value = FALSE ) {
			if(0 < $percentile && $percentile < 1) {
				$p = $percentile;
			} else if(1 < $percentile && $percentile <= 100) {
				$p = $percentile * .01;
			} else {
				return FALSE;
			}

			$count = count($data);

			if($order) {
				sort($data);
			}

			if($type === 'EXC') {
				$allindex = $p * $count + (1 / 2);

				$allindex_round = round($allindex);

				if($value != FALSE) {
					$result = $data[$allindex_round - 1][$value];
				} else {
					$result = $data[$allindex_round - 1];
				}

				return $result;
			}

			$allindex = ($count - 1) * $p;
			$intvalindex = round($allindex);
			$floatval = $allindex - $intvalindex;

			if( ! is_float($floatval)) {
				if($value != FALSE) {
					$result = $data[$intvalindex][$value];
				} else {
					$result = $data[$intvalindex];
				}
			} else {
				if($count > $intvalindex + 1) {
					if($value != FALSE) {
						$result = $floatval * ($data[$intvalindex + 1][$value] - $data[$intvalindex][$value]) + $data[$intvalindex][$value];
					} else {
						$result = $floatval * ($data[$intvalindex + 1] - $data[$intvalindex]) + $data[$intvalindex];
					}
				} else {
					if($value != FALSE) {
						$result = $data[$intvalindex][$value];
					} else {
						$result = $data[$intvalindex];
					}
				}
			}

			return $result;
		}

		/**
		 *  Finds next execution time(stamp) parsin crontab syntax,
		 *  after given starting timestamp (or current time if ommited)
		 *
		 *  @param string $_cron_string:
		 *
		 *      0     1    2    3    4
		 *      *     *    *    *    *
		 *      -     -    -    -    -
		 *      |     |    |    |    |
		 *      |     |    |    |    +----- day of week (0 - 6) (Sunday=0)
		 *      |     |    |    +------- month (1 - 12)
		 *      |     |    +--------- day of month (1 - 31)
		 *      |     +----------- hour (0 - 23)
		 *      +------------- min (0 - 59)
		 *  @param int $_after_timestamp timestamp [default=current timestamp]
		 *  @return int unix timestamp - next execution time will be greater
		 *              than given timestamp (defaults to the current timestamp)
		 *  @throws InvalidArgumentException
		 */
		public static function _crontabParse( $_cron_string, $_after_timestamp = null ) {
			if( ! preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($_cron_string))) {
				throw new InvalidArgumentException( "Invalid cron string: " .$_cron_string );
			}
			if($_after_timestamp && ! is_numeric($_after_timestamp)) {
				throw new InvalidArgumentException( "\$_after_timestamp must be a valid unix timestamp ($_after_timestamp given)" );
			}
			$cron = preg_split("/[\s]+/i", trim($_cron_string));
			$start = empty($_after_timestamp) ? time() : $_after_timestamp;

			$date = array(
				'minutes' => self::_parseCronNumbers($cron[0], 0, 59),
				'hours' => self::_parseCronNumbers($cron[1], 0, 23),
				'dom' => self::_parseCronNumbers($cron[2], 1, 31),
				'month' => self::_parseCronNumbers($cron[3], 1, 12),
				'dow' => self::_parseCronNumbers($cron[4], 0, 6),
			);
			// limited to time()+366 - no need to check more than 1year ahead
			for ($i = 0; $i <= 60 * 60 * 24 * 366; $i += 60) {
				if(in_array(intval(date('j', $start + $i)), $date['dom']) && in_array(intval(date('n', $start + $i)), $date['month']) && in_array(intval(date('w', $start + $i)), $date['dow']) && in_array(intval(date('G', $start + $i)), $date['hours']) && in_array(intval(date('i', $start + $i)), $date['minutes'])) {
					return $start + $i;
				}
			}
			return null;
		}

		/**
		 * get a single cron style notation and parse it into numeric value
		 *
		 * @param string $s cron string element
		 * @param int $min minimum possible value
		 * @param int $max maximum possible value
		 * @return int parsed number
		 */
		protected static function _parseCronNumbers( $s, $min, $max ) {
			$result = array();

			$v = explode(',', $s);
			foreach ($v as $vv) {
				$vvv = explode('/', $vv);
				$step = empty($vvv[1]) ? 1 : $vvv[1];
				$vvvv = explode('-', $vvv[0]);
				$_min = count($vvvv) == 2 ? $vvvv[0] : ($vvv[0] == '*' ? $min : $vvv[0]);
				$_max = count($vvvv) == 2 ? $vvvv[1] : ($vvv[0] == '*' ? $max : $vvv[0]);

				for ($i = $_min; $i <= $_max; $i += $step) {
					$result[$i] = intval($i);
				}
			}
			ksort($result);
			return $result;
		}

		public function timeStart( ) {
			$this->timeStart = microtime(true);
		}

		public function timeEnd( ) {
			$time_end = microtime(true);
			$time = $time_end - $this->timeStart;
			$time = round($time, 3);
			unset($this->timeStart);
			return $time;
		}
		
		/**
		 * get moda for array
		 *
		 * @param array $array array element
		 * @return int moda number
		 */
		 
		 public function modaArray($array=array())
		 {
			 if(is_array($array)){
			 	$arryCount = array_count_values($array);
				$maxKey = 0;
				foreach ($arryCount as $key => $value) {
					if($value > $maxKey) {
						$maxKey = $value;
						$return = $key;
					}
				}
				return $return;
			 } else {
			 	return false;
			 }
		 }


		public function roundNumber($number = 0, $significance = 1)
		{
			if($significance == 'auto'){
				if($number < 1000){
					$significance = 100;
				} elseif ($number < 10000) {
					$significance = 1000;
				}	elseif ($number < 100000) {
					$significance = 10000;
				} else {
					$significance = 1;
				}
			}

			$cal = $number/ $significance;
			$cal = round($cal);
			$cal = $cal*$significance;
			return $cal;
		}
	}
?>