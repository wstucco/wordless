<?php

if(! class_exists('invoke_test')) {
	class invoke_test {
		function method() {
			return('method_value');
		}

		function return_arguments() {
			return(func_get_args());
		}
	} 
}

$mocked_invoke_obj = new invoke_test;
