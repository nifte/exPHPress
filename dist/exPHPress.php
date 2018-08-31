<?php
	$static = '';
	$route = array_slice(array_filter(explode('/', $_SERVER['REDIRECT_URL'])), count(array_filter(explode('/', $_SERVER['PHP_SELF'])))-1);
	$valid_route = false;

	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $protocol = 'https://';
	else $protocol = 'http://';
	define('HOME_URL', $protocol . $_SERVER['SERVER_NAME'] . implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), 0, -1)));
	
	class exPHPress {
		public function get($url, $function) {
			$this->test('GET', $url, $function);
		}
		public function post($url, $function) {
			$this->test('POST', $url, $function);
		}
		public function put($url, $function) {
			$this->test('PUT', $url, $function);
		}
		public function delete($url, $function) {
			$this->test('DELETE', $url, $function);
		}
		public function invalid($function) {
			global $invalid_fn;
			$invalid_fn = $function;
		}
		private function test($method, $url, $function) {
			global $route;
			$url = array_filter(explode('/', trim($url, '/')));
			if (count($route) == count($url)) {
				$route_matches = true;
				$req = [];
				for ($i = 0; $i < count($route); $i++) {
					if (preg_match('/^:/', $url[$i])) $req[trim($url[$i], ':')] = $route[$i];
					else if ($route[$i] != $url[$i]) {
						$route_matches = false;
						break;
					}
				}
				if ($_SERVER['REQUEST_METHOD'] == $method && $route_matches) {
					global $valid_route;
					$valid_route = true;
					call_user_func_array($function, [$req, new Response]);
				}
			}
		}
		public function static($dir) {
			if (is_dir($dir)) {
				global $static;
				$static = trim($dir, '/') . '/';
			}
		}
	}

	class Response {
		public function sendFile($file, $vars = null) {
			global $static;
			if (is_array($vars)) extract($vars);
			include $static . $file;
		}
		public function sendStatus($status) {
			http_response_code($status);
		}
		public function json($json) {
			if (is_array($json)) echo json_encode($json);
			else if (is_string($json) && json_decode($json)) echo $json;
			else echo 'Invalid json format.';
		}
		public function setHeader() {
			$args = func_get_args();
			if (count($args) == 1 && is_array($args)) {
				foreach ($args[0] as $header => $value) {
					header("$header: $value");
				}
			} else if (count($args) == 2) header("$args[0]: $args[1]");
		}
		public function redirect($url) {
			$protocol = $_SERVER['REQUEST_SCHEME'] . '://';
			$location = $_SERVER['SERVER_NAME'] . implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), 0, -1));
			$app = $protocol . $location;
			$newLocation = $app . '/' . trim($url, '/');
			header("Location: $newLocation");
		}
	}

	$invalid_fn = function() {
		http_response_code(404);
		echo 'Page not found.';
	};

	$cwd = getcwd();
	register_shutdown_function(function() {
		global $valid_route, $invalid_fn, $cwd;
		if (!$valid_route) {
			chdir($cwd);
			call_user_func_array($invalid_fn, [[], new Response]);
		}
	});