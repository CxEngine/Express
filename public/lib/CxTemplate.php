<?php





class CxTemplate
{
	private $data;

	function render($view, $scope = [])
	{
		global $db, $service;

		// Handy for debugging
		// $this->scope = $scope;
		$this->data = (array) $scope;

		// Template render helper
		$render = function ($file, $scope = []) {
			ob_start();
			include $file;
			return ob_get_clean();
		};

		// Template slot helper
		$slot = function ($name, $default = '') use ($scope, &$slot) {
			$scope = isset($scope[$name]) ? $scope[$name] : $scope;
			// $scope = isset($scope[$name]) ? $scope[$name] : null;
			// echo $name;
			// getCx()->debug($scope);

			// View helpers
			global $db, $service;

			// String ?
			if (is_string($scope)) {
				return $scope;
			}

			// Object ?
			$type = $scope[0];
			// getCx()->debug($type);

			// ['file' => 'view.php', 'data' => []]
			if ($scope['file']) {
				$data = $scope['data'];
				$this->data = (array) $data;
				// $this->$data = $data;
				include $scope['file'];
			}

			// ['file', 'view.php']
			if ($type === 'file') {
				include $scope[1];
			}
			$resp = isset($scope[$name]) ? $scope[$name] : $default;
			// echo "cool".$resp;			

			return $resp;
		};

		// Alias $data to $scope
		$data = $scope;

		// Include main
		return $render($view, $scope);
		// include $view;
	}

	// magic methods!
	public function __set($property, $value)
	{
		return $this->data[$property] = $value;
	}

	public function __get($property)
	{
		return array_key_exists($property, $this->data)
			? $this->data[$property]
			: null;
	}
}

return new CxTemplate;
