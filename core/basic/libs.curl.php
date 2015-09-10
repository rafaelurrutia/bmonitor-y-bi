<?php
if (!class_exists("curl")) {
	class curl {

		function curl($parametro, $logs) {

			$this->logs = $logs;
			$this->retry = 0;
			$this->optsDefecto = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
				CURLOPT_SSL_VERIFYHOST =>  1,
				CURLOPT_SSL_VERIFYPEER =>  1,
				CURLOPT_FOLLOWLOCATION =>  1,
				CURLOPT_RETURNTRANSFER =>  1,
				CURLOPT_TIMEOUT =>  3,
				CURLOPT_MAXCONNECTS => 10,
				CURLOPT_CONNECTTIMEOUT => 3
			);
			$this->activa();
		}

		private function activa() {
			$this->running = null;
			$this->curl_arr = array();
			$this->master = curl_multi_init();
		}

		public function cargar($url, $id = 0, $post = null, $opts = null) {
			$this->idOne = $id;
			$fields_string = null;
			if ($post != null) {
				if (is_array($post)) {
					foreach ($post as $key => $value) { $fields_string .= $key . '=' . $value . '&';
					}
					rtrim($fields_string, '&');
				} else {
					$fields_string = $post;
				}

				$this->opts = array(
					CURLOPT_POST => "1",
					CURLOPT_POSTFIELDS => $fields_string
				);
				$opts_e = $this->optsDefecto + $this->opts;
			} else {
				$opts_e = $this->optsDefecto;
			}

			if (($opts != null) && (is_array($opts))) {
				$opts_e = $opts_e + $opts;
			}

			$this->curl_arr[$id] = curl_init($url);
			curl_setopt_array($this->curl_arr[$id], $opts_e);
			curl_multi_add_handle($this->master, $this->curl_arr[$id]);
		}

		private function execSingle($key = null, $httpCode) {
			if ($key == null) {
				$key = 0;
			}
			if ($this->retry > 0) {
				$retry = $this->retry;
				$code = 0;
				if ($httpCode == null) {
					while ($retry >= 0 && ($code == 0 || $code >= 400)) {
						$res = curl_exec($this->curl_arr[$key]);
						$code = $this->getInfo($key, 'http_code');
						$retry--;
					}
				} else {
					while ($retry >= 0 && $code != $httpCode) {
						$res = curl_exec($this->curl_arr[$key]);
						$code = $this->getInfo($key, 'http_code');
						$retry--;
					}

				}

			} else {
				$res = curl_exec($this->curl_arr[$key]);
			}

			return true;
		}

		public function ejecutar($key = false, $httpCode = null) {
			$no = count($this->curl_arr);
			$res = false;
			if ($no == 1) {
				if ($key == false) {
					foreach ($this->curl_arr as $keyOne => $valor) {
						$key = $keyOne;
					}
				}
				$res = $this->execSingle($key, $httpCode);
			} elseif ($no > 1) {
				if ($key === false) {
					$res = $this->execMulti($httpCode);
				} else {
					$res = $this->execSingle($key, $httpCode);
				}

			}
			if ($res) {
				return true;
			} else {
				return false;
			}
		}

		private function execMulti($httpCode) {
			do {
				$mrc = curl_multi_exec($this->master, $this->running);
			} while($mrc == CURLM_CALL_MULTI_PERFORM);

			while ($this->running && $mrc == CURLM_OK) {
				if (curl_multi_select($this->master) != -1) {
					do {
						$mrc = curl_multi_exec($this->master, $this->running);
					} while ($mrc == CURLM_CALL_MULTI_PERFORM);
				}
			}

			if ($mrc != CURLM_OK) {
				echo "Curl multi read error $mrc\n";
			}
			if ($this->retry > 0) {
				$this->retry = $this->retry - 1;
				foreach ($this->curl_arr as $i => $url) {
					$code = $this->getInfo($i, 'http_code');
					if ($httpCode == null) {
						if ($code > 0 && $code < 400) {
							$this->res[$i] = $code;
						} else {
							$this->execSingle($i, $httpCode);
						}
					} else {
						if ($code == $httpCode) {
							$this->res[$i] = $code;
						} else {
							$this->execSingle($i, $httpCode);
						}
					}
				}
			}
		}

		public function multipleThreadsRequest($nodes, $post = false) {
			$mh = curl_multi_init();
			$curl_array = array();
			$fields_string = null;
			if ($post !== false) {
				if (is_array($post)) {
					foreach ($post as $key => $value) { $fields_string .= $key . '=' . $value . '&';
					}
					rtrim($fields_string, '&');
				} else {
					$fields_string = $post;
				}

				$this->opts = array(
					CURLOPT_POST => "1",
					CURLOPT_POSTFIELDS => $fields_string
				);

				$opts_e = $this->optsDefecto + $this->opts;
			} else {
				$opts_e = $this->optsDefecto;
			}

			foreach ($nodes as $i => $url) {
				$curl_array[$i] = curl_init($url);
				curl_setopt_array($curl_array[$i], $opts_e);
				curl_multi_add_handle($mh, $curl_array[$i]);
			}

			$running = NULL;
			do {
				usleep(10000);
				curl_multi_exec($mh, $running);
			} while($running > 0);

			$res = array();
			foreach ($nodes as $i => $url) {
				$res[$url] = curl_multi_getcontent($curl_array[$i]);
			}

			foreach ($nodes as $i => $url) {
				curl_multi_remove_handle($mh, $curl_array[$i]);
			}
			curl_multi_close($mh);
			return $res;
		}

		function close() {
			foreach ($this->curl_arr as $i => $value) {
				curl_multi_remove_handle($this->master, $this->curl_arr[$i]);
			}
			curl_multi_close($this->master);
		}

		public function getContent($id) {
			$content = curl_multi_getcontent($this->curl_arr[$id]);
			return $content;
		}

		public function getInfo($id, $tipo = null) {
			$info = curl_getinfo($this->curl_arr[$id]);
			if ($tipo == null) {
				return $info;
			} else {
				return $info[$tipo];
			}
		}

	}

}
?>