<?php

class CxTemplate
{
	private $data;

	function render($view, $scope = [])
	{
		// Expose for use in a view
		global $db, $service;

		// Handy for debugging
		// $this->scope = $scope;
		$this->data = (array) $scope;

		// Template slot helper
		$slot = function ($name, $default = '') use ($scope, &$slot, &$asset) {
			$scope = isset($scope[$name]) ? $scope[$name] : $scope;

			// View helpers
			global $db, $service;

			// String ?
			if (is_string($scope)) {
				return $scope;
			}

			// Object ?
			$type = $scope[0];

			$resp = isset($scope[$name]) ? $scope[$name] : $default;

			return $resp;
		};

		// Template render helper
		$render = function ($file, $scope = []) use ($slot) {
			/**
			 * Template Helpers
			 */
			// $asset = function ($dir = '') {
			// 	return str_replace(APP, "", $dir);
			// };

			// Buffer output
			ob_start();
			include $file;
			return ob_get_clean();
		};

		// Alias $data to $scope
		$data = $scope;

		// Include main
		return $render($view, $scope);
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
