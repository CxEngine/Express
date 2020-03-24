<?php

class CxTemplate
{
	// Provided config
	// private $config = [];

	// Helper functions
	private $helpers;

	// Magic get and set to use in view
	private $data;

	function __construct($config = [
		'helpers' => []
	])
	{
		// $this->config = $config;

		// Break config
		// $helpers =  (object) $config['helpers'];
		// $this->data = (array) $helpers;
		$this->helpers = (array) $config['helpers'];
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

			// print_r($this);
			// Flatten helpers for easy access
			extract($this->helpers);

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
}

return new CxTemplate;
