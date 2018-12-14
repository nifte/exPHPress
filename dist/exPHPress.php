<?php
	define('APP_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), 0, -1)));
	define('REDIRECT_URL', explode('#', explode('?', $_SERVER['REQUEST_URI'])[0])[0]);
	define('ROUTE', array_slice(array_filter(explode('/', REDIRECT_URL)), count(array_filter(explode('/', $_SERVER['PHP_SELF']))) - 1));

	class exPHPress {
		function __construct() {
			$this->app_dir = getcwd();
			$this->error_fn = function() {
				http_response_code(404);
				echo 'Page not found.';
			};
			register_shutdown_function(function() {
				if (!$this->valid_route) {
					chdir($this->app_dir);
					$res = new Response;
					$res->static_dir = $this->static_dir;
					call_user_func_array($this->error_fn, [[], $res]);
				}
			});
		}
		private $app_dir = '',
			$static_dir = '',
			$error_fn = '',
			$valid_route = false;
		public function get($url, $function) {
			$this->test('GET', $url, $function);
		}
		public function put($url, $function) {
			$this->test('PUT', $url, $function);
		}
		public function post($url, $function) {
			$this->test('POST', $url, $function);
		}
		public function patch($url, $function) {
			$this->test('PATCH', $url, $function);
		}
		public function delete($url, $function) {
			$this->test('DELETE', $url, $function);
		}
		public function any($url, $function) {
			$this->test($_SERVER['REQUEST_METHOD'], $url, $function);
		}
		public function error($function) {
			$this->error_fn = $function;
		}
		private function test($method, $url, $function) {
			$url = array_filter(explode('/', trim($url, '/')));
			if (count(ROUTE) == count($url)) {
				$route_matches = true;
				$req = [];
				for ($i = 0; $i < count(ROUTE); $i++) {
					if (preg_match('/^:/', $url[$i])) $req[trim($url[$i], ':')] = ROUTE[$i];
					else if (ROUTE[$i] != $url[$i]) {
						$route_matches = false;
						break;
					}
				}
				if ($_SERVER['REQUEST_METHOD'] == $method && $route_matches) {
					$this->valid_route = true;
					$res = new Response;
					$res->static_dir = $this->static_dir;
					call_user_func_array($function, [$req, $res]);
				}
			}
		}
		public function static($dir) {
			if (is_dir($dir)) {
				$this->static_dir = trim($dir, '/') . '/';
			}
		}
	}

	class Response {
		public $static_dir = '';
		public function sendFile($file, $vars = null) {
			if (is_array($vars)) extract($vars);
			include $this->static_dir . $file;
		}
		public function sendStatus($status) {
			http_response_code($status);
		}
		public function json($json, $status = null) {
			header('Content-Type: application/json');
			if (is_array($json)) echo json_encode($json);
			else if (is_string($json) && json_decode($json)) echo $json;
			else echo json_encode(['error' => 'Invalid JSON format']);
			if ($status) http_response_code($status);
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
			$new_location = APP_URL . '/' . trim($url, '/');
			header("Location: $new_location");
		}
	}