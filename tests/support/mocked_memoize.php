<?php

if(!class_exists('memoize_test')) {
	class memoize_test {
	    private static $invocation = 0;

	    public static function invoke($name) {
	        if (self::$invocation > 0) {
	            throw new Exception(sprintf('%s called more than once', $name));
	        }
	        self::$invocation++;
	        return self::$invocation;
	    }

	    public static function reset_invocations() {
	    	// reset invocations to 0 
	    	self::$invocation = 0;
	    }

	    public static function call() {
	        return 'STATIC METHOD VALUE' . self::invoke(__METHOD__);
	    }

	}
}

$memoize_test = new memoize_test;