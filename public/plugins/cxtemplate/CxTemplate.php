<?php

class CxTemplate
{
	// Provided config
	private $config = [];

	// Magic get and set to use in view
	private $data;

	function __construct($config = [
		'helpers' => []
	])
	{
		$this->config = $config;

		// Attach helpers
		$helpers =  (object) $config['helpers'];
		$this->data = (array) $helpers;
	}

	function render($view, $context = [])
	{
		// Handy for debugging
		// $this->context = $context;
		$this->data = (array) $context;

		// Template slot helper
		$slot = function ($name, $default = '') use ($context, &$slot, &$asset) {
			$context = isset($context[$name]) ? $context[$name] : $context;

			// String ?
			if (is_string($context)) {
				return $context;
			}

			$resp = isset($context[$name]) ? $context[$name] : $default;

			return $resp;
		};

		// Template render helper
		$render = function ($file, $context = [], $return = false) use ($slot, &$render) {
			/**
			 * Template Helpers
			 */
			// $asset = function ($dir = '') {
			// 	return str_replace(APP, "", $dir);
			// };

			// Flatten context for easy access
			if ($context) {
				extract($context);
			}

			// Buffer output
			ob_start();
			include $file;
			$resp = ob_get_clean();

			// Return or echo
			if ($return) {
				return $resp;
			}
			echo $resp;
		};

		// Include main
		return $render($view, $context, true);
	}

	// magic methods: for accessing helpers and template global data
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
