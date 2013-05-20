<?php

require_once 'simpletest/autorun.php';
require_once 'support/mocked_bloginfo.php';
require_once '../wordless/wordless.php';
require_once '../wordless/helpers.php';

Wordless::register_helper( 'FunctionalHelper' );

class FunctionalHelperTest extends UnitTestCase {
    private $current_collection;

    function throw_exception() {
        if (func_num_args() < 3) {
            throw new DomainException('Callback exception');
        }

        $args = func_get_args();
        $this->assertGreaterThanOrEqual(3, count($args));
        throw new DomainException(sprintf('Callback exception: %s', $args[1]));
    }    

    function error_collections() {
        $collections = array();
        foreach (array(new stdClass(), stream_context_create(), array(), "str") as $v) {
            $arg = array(2, $v, "1.5", true, null);
            $collections[] = $arg;
            $collections[] = new ArrayIterator($arg);
        }
        
        return $collections;        
    }

    function test_average() {
        $hash = array( "f0" => 12, "f1" => 2, "f3" => true, "f4" => false, "f5" => "str", "f6" => array(), "f7" => new stdClass(), "f8" => 1 );
        $hashIterator = new ArrayIterator( $hash );
        $array = array_values( $hash );
        $arrayIterator = new ArrayIterator( $array );

        $hash2 = array( "f0" => 1.0, "f1" => 0.5, "f3" => true, "f4" => false, "f5" => 1 );
        $hashIterator2 = new ArrayIterator( $hash2 );
        $array2 = array_values( $hash2 );
        $arrayIterator2 = new ArrayIterator( $array2 );

        $hash3 = array( "f0" => array(), "f1" => new stdClass(), "f2" => null, "f3" => "foo" );
        $hashIterator3 = new ArrayIterator( $hash3 );
        $array3 = array_values( $hash3 );
        $arrayIterator3 = new ArrayIterator( $array3 );

        $this->assertIdentical( 5, average( $hash ) );
        $this->assertIdentical( 5, average( $hashIterator ) );
        $this->assertIdentical( 5, average( $array ) );
        $this->assertIdentical( 5, average( $arrayIterator ) );

        $this->assertWithinMargin( 0.83333333333333, average( $hash2 ), 0.001 );
        $this->assertWithinMargin( 0.83333333333333, average( $hashIterator2 ), 0.001 );
        $this->assertWithinMargin( 0.83333333333333, average( $array2 ), 0.001 );
        $this->assertWithinMargin( 0.83333333333333, average( $arrayIterator2 ), 0.001 );

        $this->assertNull( average( $hash3 ) );
        $this->assertNull( average( $hashIterator3 ) );
        $this->assertNull( average( $array3 ) );
        $this->assertNull( average( $arrayIterator3 ) );
    }


    function test_contains() {
        $array = array( 'value0', 'value1', 'value2', 2 );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val3', 'k4' => 2 );
        $hashIterator = new ArrayIterator( $hash );

        $this->assertFalse( contains( array(), 'foo' ) );
        $this->assertFalse( contains( new ArrayIterator(), 'foo' ) );

        $this->assertTrue( contains( $array, 'value0' ) );
        $this->assertTrue( contains( $array, 'value1' ) );
        $this->assertTrue( contains( $array, 'value2' ) );
        $this->assertTrue( contains( $array, 2 ) );
        $this->assertFalse( contains( $array, '2', true ) );
        $this->assertFalse( contains( $array, '2' ) );
        $this->assertTrue( contains( $array, '2', false ) );
        $this->assertFalse( contains( $array, 'value' ) );

        $this->assertTrue( contains( $iterator, 'value0' ) );
        $this->assertTrue( contains( $iterator, 'value1' ) );
        $this->assertTrue( contains( $iterator, 'value2' ) );
        $this->assertTrue( contains( $iterator, 2 ) );
        $this->assertFalse( contains( $iterator, '2', true ) );
        $this->assertFalse( contains( $iterator, '2' ) );
        $this->assertTrue( contains( $iterator, '2', false ) );
        $this->assertFalse( contains( $iterator, 'value' ) );

        $this->assertTrue( contains( $hash, 'val1' ) );
        $this->assertTrue( contains( $hash, 'val2' ) );
        $this->assertTrue( contains( $hash, 'val3' ) );
        $this->assertTrue( contains( $hash, 2 ) );
        $this->assertFalse( contains( $hash, '2', true ) );
        $this->assertFalse( contains( $hash, '2' ) );
        $this->assertTrue( contains( $hash, '2', false ) );
        $this->assertFalse( contains( $hash, 'value' ) );

        $this->assertTrue( contains( $hashIterator, 'val1' ) );
        $this->assertTrue( contains( $hashIterator, 'val2' ) );
        $this->assertTrue( contains( $hashIterator, 'val3' ) );
        $this->assertTrue( contains( $hashIterator, 2 ) );
        $this->assertFalse( contains( $hashIterator, '2', true ) );
        $this->assertFalse( contains( $hashIterator, '2' ) );
        $this->assertTrue( contains( $hashIterator, '2', false ) );
        $this->assertFalse( contains( $hashIterator, 'value' ) );
    }


    // DifferenceTest.php
    function test_difference() {
        $intArray = array( 1 => 1, 2, "foo" => 3, 4 );
        $intIterator = new ArrayIterator( $intArray );
        $floatArray = array( "foo" => 4.5, 1.1, 1 );
        $floatIterator = new ArrayIterator( $floatArray );
        $this->assertIdentical( -10, difference( $intArray ) );
        $this->assertIdentical( -10, difference( $intIterator ) );
        $this->assertEqual( -6.6, difference( $floatArray ), '', 0.01 );
        $this->assertEqual( -6.6, difference( $floatIterator ), '', 0.01 );
        $this->assertIdentical( 0, difference( $intArray, 10 ) );
        $this->assertIdentical( 0, difference( $intIterator, 10 ) );
        $this->assertEqual( -10, difference( $floatArray, -3.4 ), '', 0.01 );
        $this->assertEqual( -10, difference( $floatIterator, -3.4 ), '', 0.01 );


        // test elements of wrong type are ignored
        $collections = $this->error_collections();
        foreach($collections as $collection)
            $this->assertWithinMargin( -3.5, difference( $collection ), 0.1 );

    }
    // DropTest.php
    function test_drop() {
        $array = array( 'value1', 'value2', 'value3', 'value4' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val3', 'k4' => 'val4' );
        $hashIterator = new ArrayIterator( $hash );
        $fn = function( $v, $k, $collection ) {
            $return = is_int( $k ) ? ( $k != 2 ) : ( $v[3] != 3 );
            return $return;
        };
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2' ), drop_last( $array, $fn ) );
        $this->assertIdentical( array( 2 => 'value3', 3 => 'value4' ), drop_first( $array, $fn ) );
        $this->assertIdentical( array( 2 => 'value3', 3 => 'value4' ), drop_first( $iterator, $fn ) );
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2' ), drop_last( $iterator, $fn ) );
        $this->assertIdentical( array( 'k3' => 'val3', 'k4' => 'val4' ), drop_first( $hash, $fn ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), drop_last( $hash, $fn ) );
        $this->assertIdentical( array( 'k3' => 'val3', 'k4' => 'val4' ), drop_first( $hashIterator, $fn ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), drop_last( $hashIterator, $fn ) );
    }

    // EachTest.php
    function test_each() {

        $callback = function($value, $key, $collection) {};

        $array = array( 'value0', 'value1', 'value2', 'value3' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k0' => 'value0', 'k1' => 'value1', 'k2' => 'value2' );
        $hashIterator = new ArrayIterator( $hash );
        
        // each already exists in PHP cannot live in global namespace
        $this->assertNull( FunctionalHelper::each( $array, $callback) );
        $this->assertNull( FunctionalHelper::each( $iterator, $callback ) );
        $this->assertNull( FunctionalHelper::each( $hash, $callback ) );
        $this->assertNull( FunctionalHelper::each( $hashIterator, $callback ) );
    }

    // EveryTest.php
    function test_every() {
        $goodArray = array( 'value', 'value', 'value' );
        $goodIterator = new ArrayIterator( $goodArray );
        $badArray = array( 'value', 'nope', 'value' );
        $badIterator = new ArrayIterator( $badArray );

        $functional_callback = function($value, $key, $collection) {
            return $value == 'value' && is_numeric($key);
        };

        $this->assertTrue( every( $goodArray, $functional_callback ) );
        $this->assertTrue( every( $goodIterator, $functional_callback ) );
        $this->assertFalse( every( $badArray, $functional_callback ) ) ;
        $this->assertFalse( every( $badIterator, $functional_callback ) );

    }
    // FalseTest.php
    function test_false() {
        $trueArray = array( false, false, false, false );
        $trueIterator = new ArrayIterator( $trueArray );
        $trueHash = array( 'k1' => false, 'k2' => false, 'k3' => false );
        $trueHashIterator = new ArrayIterator( $trueHash );
        $falseArray = array( false, 0, false, 'foo', array(), (object)array() );
        $falseIterator = new ArrayIterator( $falseArray );
        $falseHash = array( 'k1' => false, 'k2' => 0, 'k3' => false );
        $falseHashIterator = new ArrayIterator( $falseHash );
        $this->assertTrue( false( array() ) );
        $this->assertTrue( false( new ArrayIterator( array() ) ) );
        $this->assertTrue( false( $trueArray ) );
        $this->assertTrue( false( $trueIterator ) );
        $this->assertTrue( false( $trueHash ) );
        $this->assertTrue( false( $trueHashIterator ) );
        $this->assertFalse( false( $falseArray ) );
        $this->assertFalse( false( $falseIterator ) );
        $this->assertFalse( false( $falseHash ) );
        $this->assertFalse( false( $falseHashIterator ) );

    }
    // FalsyTest.php
    function test_falsy() {
        $trueArray = array( false, null, false, false, 0 );
        $trueIterator = new ArrayIterator( $trueArray );
        $trueHash = array( 'k1' => false, 'k2' => null, 'k3' => false, 'k4' => 0 );
        $trueHashIterator = new ArrayIterator( $trueHash );
        $falseArray = array( false, null, 0, 'foo' );
        $falseIterator = new ArrayIterator( $falseArray );
        $falseHash = array( 'k1' => false, 'k2' => 0, 'k3' => true, 'k4' => null );
        $falseHashIterator = new ArrayIterator( $falseHash );
        $this->assertTrue( falsy( array() ) );
        $this->assertTrue( falsy( new ArrayIterator( array() ) ) );
        $this->assertTrue( falsy( $trueArray ) );
        $this->assertTrue( falsy( $trueIterator ) );
        $this->assertTrue( falsy( $trueHash ) );
        $this->assertTrue( falsy( $trueHashIterator ) );
        $this->assertFalse( falsy( $falseArray ) );
        $this->assertFalse( falsy( $falseIterator ) );
        $this->assertFalse( falsy( $falseHash ) );
        $this->assertFalse( falsy( $falseHashIterator ) );

    }
    // FirstIndexOfTest.php
    function test_firstindexof() {
        $array = array( 'value1', 'value', 'value', 'value2' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val1', 'k4' => 'val3' );
        $hashIterator = new ArrayIterator( $hash );
        $this->assertIdentical( 0, first_index_of( $array, 'value1' ) );
        $this->assertIdentical( 0, first_index_of( $iterator, 'value1' ) );
        $this->assertIdentical( 1, first_index_of( $array, 'value' ) );
        $this->assertIdentical( 1, first_index_of( $iterator, 'value' ) );
        $this->assertIdentical( 3, first_index_of( $array, 'value2' ) );
        $this->assertIdentical( 3, first_index_of( $iterator, 'value2' ) );
        $this->assertIdentical( 'k1', first_index_of( $hash, 'val1' ) );
        $this->assertIdentical( 'k1', first_index_of( $hashIterator, 'val1' ) );
        $this->assertIdentical( 'k2', first_index_of( $hash, 'val2' ) );
        $this->assertIdentical( 'k2', first_index_of( $hashIterator, 'val2' ) );
        $this->assertIdentical( 'k4', first_index_of( $hash, 'val3' ) );
        $this->assertIdentical( 'k4', first_index_of( $hashIterator, 'val3' ) );

        $this->assertFalse( first_index_of( $array, 'invalidValue' ) );
        $this->assertFalse( first_index_of( $iterator, 'invalidValue' ) );
        $this->assertFalse( first_index_of( $hash, 'invalidValue' ) );
        $this->assertFalse( first_index_of( $hashIterator, 'invalidValue' ) );

    }

    // FirstTest.php
    function test_first() {
        // first and head are alias
        $aliases = array(
            'first',
            'head'
        );

        $array = array( 'first', 'second', 'third' );
        $iterator = new ArrayIterator( $array );
        $badArray = array( 'foo', 'bar', 'baz' );
        $badIterator = new ArrayIterator( $badArray );

        $callback = function( $v, $k, $collection ) {
            return $v == 'second' && $k == 1;
        };

        foreach($aliases as $function_name) {

            $this->assertIdentical( 'second', $function_name( $array, $callback ) );
            $this->assertIdentical( 'second', $function_name( $iterator, $callback ) );
            $this->assertNull( $function_name( $badArray, $callback ) );
            $this->assertNull( $function_name( $badIterator, $callback ) );
            $this->assertIdentical( 'first', $function_name( $array ) );
            $this->assertIdentical( 'first', $function_name( $array, null ) );
            $this->assertIdentical( 'first', $function_name( $iterator ) );
            $this->assertIdentical( 'first', $function_name( $iterator, null ) );
            $this->assertIdentical( 'foo', $function_name( $badArray ) );
            $this->assertIdentical( 'foo', $function_name( $badArray, null ) );
            $this->assertIdentical( 'foo', $function_name( $badIterator ) );
            $this->assertIdentical( 'foo', $function_name( $badIterator, null ) );
        }

    }
    // FlattenTest.php
    function test_flatten() {
        $goodArray = array( 1, 2, 3, array( 4, 5, 6, array( 7, 8, 9 ) ), 10, array( 11, array( 12, 13 ), 14 ), 15 );
        $goodArray2 = array( 1 => 1, "foo" => "2", 3 => "3", array( "foo" => 5 ) );
        $goodIterator = new ArrayIterator( $goodArray );
        $goodIterator[3] = new ArrayIterator( $goodIterator[3] );
        $goodIterator[5][1] = new ArrayIterator( $goodIterator[5][1] );
        $this->assertIdentical( range( 1, 15 ), flatten( $goodArray ) );
        $this->assertIdentical( range( 1, 15 ), flatten( $goodIterator ) );
        $this->assertIdentical( array( 1, "2", "3", 5 ), flatten( $goodArray2 ) );
        $this->assertEqual( array( new stdClass() ), flatten( array( array( new stdClass() ) ) ) );

    }
    // GroupTest.php
    function test_group() {
        $array = array( 'value1', 'value2', 'value3', 'value4' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val3' );
        $hashIterator = new ArrayIterator( $hash );
        $fn = function( $v, $k, $collection ) {
            return ( is_int( $k ) ? ( $k % 2 == 0 ) : ( $v[3] % 2 == 0 ) ) ? 'foo' : '';
        };
        $this->assertIdentical( array( 'foo' => array( 0 => 'value1', 2 => 'value3' ), '' => array( 1 => 'value2', 3 => 'value4' ) ), group( $array, $fn ) );
        $this->assertIdentical( array( 'foo' => array( 0 => 'value1', 2 => 'value3' ), '' => array( 1 => 'value2', 3 => 'value4' ) ), group( $iterator, $fn ) );
        $this->assertIdentical( array( '' => array( 'k1' => 'val1', 'k3' => 'val3' ), 'foo' => array( 'k2' => 'val2' ) ), group( $hash, $fn ) );
        $this->assertIdentical( array( '' => array( 'k1' => 'val1', 'k3' => 'val3' ), 'foo' => array( 'k2' => 'val2' ) ), group( $hashIterator, $fn ) );

    }
    // InvokeFirstTest.php
    function test_invokefirst() {
        require(Wordless::join_paths(__DIR__, "/support/mocked_invoke.php"));

        $array = array( $mocked_invoke_obj, null, null );
        $iterator = new ArrayIterator( $array );
        $keyArray = array( 'k1' => $mocked_invoke_obj, 'k2' => null );
        $keyIterator = new ArrayIterator( array( 'k1' => $mocked_invoke_obj, 'k2' => null ) );
        $arrayVeryFirstNotCallable = array( null, $mocked_invoke_obj, null, null );
        $iteratorVeryFirstNotCallable = new ArrayIterator( $arrayVeryFirstNotCallable );

        $this->assertIdentical( 'method_value', invoke_first( $array, 'method', array( 1, 2 ) ) );
        $this->assertIdentical( 'method_value', invoke_first( $iterator, 'method' ) );

        $this->assertIdentical( null, invoke_first( $array, 'undefinedMethod' ) );
        $this->assertIdentical( null, invoke_first( $array, 'setExpectedExceptionFromAnnotation' ), 'Protected method' );

        $this->assertIdentical( array( 1, 2 ), invoke_first( $array, 'return_arguments', array( 1, 2 ) ) );

        $this->assertIdentical( 'method_value', invoke_first( $keyArray, 'method' ) );
        $this->assertIdentical( 'method_value', invoke_first( $keyIterator, 'method' ) );

        $this->assertIdentical( 'method_value', invoke_first( $arrayVeryFirstNotCallable, 'method', array( 1, 2 ) ) );
        $this->assertIdentical( 'method_value', invoke_first( $iteratorVeryFirstNotCallable, 'method' ) );

        $this->assertIdentical( null, invoke_first( $arrayVeryFirstNotCallable, 'undefinedMethod' ) );
        $this->assertIdentical( null, invoke_first( $arrayVeryFirstNotCallable, 'setExpectedExceptionFromAnnotation' ), 'Protected method' );

        $this->assertIdentical( array( 1, 2 ), invoke_first( $arrayVeryFirstNotCallable, 'return_arguments', array( 1, 2 ) ) );

    }
    // InvokeIfTest.php
    function test_invokeif() {
        require(Wordless::join_paths(__DIR__, "/support/mocked_invoke.php"));

        $this->assertIdentical( 'method_value', invoke_if( $mocked_invoke_obj, 'method', array(), 'defaultValue' ) );
        $this->assertIdentical( 'method_value', invoke_if( $mocked_invoke_obj, 'method' ) );
        $arguments = array( 1, 2, 3 );
        $this->assertIdentical( $arguments, invoke_if( $mocked_invoke_obj, 'return_arguments', $arguments ) );
        $this->assertNull( invoke_if( $mocked_invoke_obj, 'someMethod', $arguments ) );
        $this->assertNull( invoke_if( 1, 'someMethod', $arguments ) );
        $this->assertNull( invoke_if( null, 'someMethod', $arguments ) );

        $instance = new \stdClass();
        $this->assertIdentical( 'defaultValue', invoke_if( $instance, 'someMethod', array(), 'defaultValue' ) );
        $this->assertIdentical( $instance, invoke_if( $mocked_invoke_obj, 'someMethod', array(), $instance ) );
        $this->assertNull( invoke_if( $mocked_invoke_obj, 'someMethod', array(), null ) );
    }

    // InvokeLastTest.php
    function test_invokelast() {
        require(Wordless::join_paths(__DIR__, "/support/mocked_invoke.php"));

        $array = array( null, null, $mocked_invoke_obj );
        $iterator = new ArrayIterator( $array );
        $keyArray = array( 'k1' => null, 'k2' => $mocked_invoke_obj );
        $keyIterator = new ArrayIterator( array( 'k1' => null, 'k2' => $mocked_invoke_obj ) );
        $arrayVeryLastNotCallable = array( null, null, $mocked_invoke_obj, null );
        $iteratorVeryLastNotCallable = new ArrayIterator( $arrayVeryLastNotCallable );

        $this->assertIdentical( 'method_value', invoke_last( $array, 'method', array( 1, 2 ) ) );
        $this->assertIdentical( 'method_value', invoke_last( $iterator, 'method' ) );
        $this->assertIdentical( null, invoke_last( $array, 'undefinedMethod' ) );
        $this->assertIdentical( null, invoke_last( $array, 'setExpectedExceptionFromAnnotation' ), 'Protected method' );
        $this->assertIdentical( array( 1, 2 ), invoke_last( $array, 'return_arguments', array( 1, 2 ) ) );
        $this->assertIdentical( 'method_value', invoke_last( $keyArray, 'method' ) );
        $this->assertIdentical( 'method_value', invoke_last( $keyIterator, 'method' ) );

        $this->assertIdentical( 'method_value', invoke_last( $arrayVeryLastNotCallable, 'method', array( 1, 2 ) ) );
        $this->assertIdentical( 'method_value', invoke_last( $iteratorVeryLastNotCallable, 'method' ) );

    }
    // InvokeTest.php
    function test_invoke() {
        require(Wordless::join_paths(__DIR__, "/support/mocked_invoke.php"));

        $array = array( $mocked_invoke_obj, $mocked_invoke_obj, $mocked_invoke_obj );
        $iterator = new ArrayIterator( $array );
        $keyArray = array( 'k1' => $mocked_invoke_obj, 'k2' => $mocked_invoke_obj );
        $keyIterator = new ArrayIterator( array( 'k1' => $mocked_invoke_obj, 'k2' => $mocked_invoke_obj ) );

        $this->assertIdentical( array( 'method_value', 'method_value', 'method_value' ), invoke( $array, 'method', array( 1, 2 ) ) );
        $this->assertIdentical( array( 'method_value', 'method_value', 'method_value' ), invoke( $iterator, 'method' ) );
        $this->assertIdentical( array( null, null, null ), invoke( $array, 'undefinedMethod' ) );
        $this->assertIdentical( array( null, null, null ), invoke( $array, 'setExpectedExceptionFromAnnotation' ), 'Protected method' );
        $this->assertIdentical( array( array( 1, 2 ), array( 1, 2 ), array( 1, 2 ) ), invoke( $array, 'return_arguments', array( 1, 2 ) ) );
        $this->assertIdentical( array( 'k1' => 'method_value', 'k2' => 'method_value' ), invoke( $keyArray, 'method' ) );
        $this->assertIdentical( array( 'k1' => 'method_value', 'k2' => 'method_value' ), invoke( $keyIterator, 'method' ) );

    }
    // LastIndexOfTest.php
    function test_lastindexof() {
        $array = array( 'value1', 'value', 'value', 'value2' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val1', 'k4' => 'val3' );
        $hashIterator = new ArrayIterator( $hash );
        $this->assertIdentical( 0, last_index_of( $array, 'value1' ) );
        $this->assertIdentical( 0, last_index_of( $iterator, 'value1' ) );
        $this->assertIdentical( 2, last_index_of( $array, 'value' ) );
        $this->assertIdentical( 2, last_index_of( $iterator, 'value' ) );
        $this->assertIdentical( 3, last_index_of( $array, 'value2' ) );
        $this->assertIdentical( 3, last_index_of( $iterator, 'value2' ) );
        $this->assertIdentical( 'k3', last_index_of( $hash, 'val1' ) );
        $this->assertIdentical( 'k3', last_index_of( $hashIterator, 'val1' ) );
        $this->assertIdentical( 'k2', last_index_of( $hash, 'val2' ) );
        $this->assertIdentical( 'k2', last_index_of( $hashIterator, 'val2' ) );
        $this->assertIdentical( 'k4', last_index_of( $hash, 'val3' ) );
        $this->assertIdentical( 'k4', last_index_of( $hashIterator, 'val3' ) );

        $this->assertFalse( last_index_of( $array, 'invalidValue' ) );
        $this->assertFalse( last_index_of( $iterator, 'invalidValue' ) );
        $this->assertFalse( last_index_of( $hash, 'invalidValue' ) );
        $this->assertFalse( last_index_of( $hashIterator, 'invalidValue' ) );

    }
    // LastTest.php
    function test_last() {
        $array = array( 'first', 'second', 'third', 'fourth' );
        $iterator = new ArrayIterator( $array );
        $badArray = array( 'foo', 'bar', 'baz' );
        $badIterator = new ArrayIterator( $badArray );
        $fn = function( $v, $k, $collection ) {
            return ( $v == 'first' && $k == 0 ) || ( $v == 'third' && $k == 2 );
        };

        $this->assertIdentical( 'third', last( $array, $fn ) );
        $this->assertIdentical( 'third', last( $iterator, $fn ) );
        $this->assertNull( last( $badArray, $fn ) );
        $this->assertNull( last( $badIterator, $fn ) );

        $this->assertIdentical( 'fourth', last( $array ) );
        $this->assertIdentical( 'fourth', last( $array, null ) );
        $this->assertIdentical( 'fourth', last( $iterator ) );
        $this->assertIdentical( 'fourth', last( $iterator, null ) );

    }
    // MapTest.php
    function test_map() {
        $array = array( 'value', 'value' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2' );
        $hashIterator = new ArrayIterator( $hash );
        $fn = function( $v, $k, $collection ) {
            return $k . $v;
        };
        $this->assertIdentical( array( '0value', '1value' ), map( $array, $fn ) );
        $this->assertIdentical( array( '0value', '1value' ), map( $iterator, $fn ) );
        $this->assertIdentical( array( 'k1' => 'k1val1', 'k2' => 'k2val2' ), map( $hash, $fn ) );
        $this->assertIdentical( array( 'k1' => 'k1val1', 'k2' => 'k2val2' ), map( $hashIterator, $fn ) );

    }
    // MathDataProvider.php
    // MaximumTest.php
    function test_maximum() {
        $array = array( 1, "foo", 5.1, 5, "5.2", true, false, array(), new stdClass() );
        $iterator = new ArrayIterator( $array );
        $hash = array(
            'k1' => 1,
            'k2' => '5.2',
            'k3' => 5,
            'k4' => '5.1',
            'k5' => 10.2,
            'k6' => true,
            'k7' => array(),
            'k8' => new stdClass(),
        );

        $hashIterator = new ArrayIterator( $hash );
        $this->assertEqual( '5.2', maximum( $array ) );
        return;
        $this->assertEqual( '5.2', maximum( $iterator ) );
        $this->assertEqual( 10.2, maximum( $hash ) );
        $this->assertEqual( 10.2, maximum( $hashIterator ) );

        $this->assertIdentical( -1, maximum( array( -1 ) ) );

        $this->assertIdentical( 1, maximum( array( 0, 1, 0.0, 1.0, "0", "1", "0.0", "1.0" ) ) );

    }
        // MemoizeTest.php
    function test_memoize() {
        // setUp
        require(Wordless::join_paths(__DIR__, "/support/mocked_memoize.php"));

        function test_memoize_func() {
            return 'TESTFUNC' . memoize_test::invoke( __FUNCTION__ );
        }

        // testMemoizeSimpleObjectCall
        Mock::generate('stdClass', 'MockstdClass', array('execute'));
        $callback = new MockstdClass();
        $callback->returns('execute', 'VALUE1');
        $callback->expectOnce('execute'); 

        $this->assertIdentical( 'VALUE1', memoize( array( $callback, 'execute' ) ) );
        $this->assertIdentical( 'VALUE1', memoize( array( $callback, 'execute' ) ) );
        $this->assertIdentical( 'VALUE1', memoize( array( $callback, 'execute' ) ) );

        // testMemoizeFunctionCall
        memoize_test::reset_invocations();
        $this->assertIdentical( 'TESTFUNC1', memoize( 'test_memoize_func' ) );
        $this->assertIdentical( 'TESTFUNC1', memoize( 'test_memoize_func' ) );
        $this->assertIdentical( 'TESTFUNC1', memoize( 'test_memoize_func' ) );

        // testMemoizeStaticMethodCall
        memoize_test::reset_invocations();
        $this->assertIdentical( 'STATIC METHOD VALUE1', memoize( array( 'memoize_test', 'call' ) ) );
        $this->assertIdentical( 'STATIC METHOD VALUE1', memoize( array( 'memoize_test', 'call' ) ) );
        $this->assertIdentical( 'STATIC METHOD VALUE1', memoize( array( 'memoize_test', 'call' ) ) );

        // testMemoizeClosureCall
        memoize_test::reset_invocations();
        $closure = function() {
            return 'CLOSURE VALUE' . memoize_test::invoke( 'Closure' );
        };
        $this->assertIdentical( 'CLOSURE VALUE1', memoize( $closure ) );
        $this->assertIdentical( 'CLOSURE VALUE1', memoize( $closure ) );
        $this->assertIdentical( 'CLOSURE VALUE1', memoize( $closure ) );

        // test (Arguments)
        $callback = new MockstdClass();        
        $callback->setReturnValueAt(0, 'execute', 'FOO BAR', array('FOO', 'BAR'));
        $callback->setReturnValueAt(1, 'execute', 'BAR BAZ', array('BAR', 'BAZ'));

        $this->assertIdentical('FOO BAR', memoize(array($callback, 'execute'), array('FOO', 'BAR')));
        $this->assertIdentical('FOO BAR', memoize(array($callback, 'execute'), array('FOO', 'BAR')));
        $this->assertIdentical('BAR BAZ', memoize(array($callback, 'execute'), array('BAR', 'BAZ')));
        $this->assertIdentical('BAR BAZ', memoize(array($callback, 'execute'), array('BAR', 'BAZ')));


        // testMemoizeWithCustomKey
        $callback = new MockstdClass();        
        $callback->setReturnValueAt(0, 'execute', 'FOO BAR', array('FOO', 'BAR'));
        $callback->setReturnValueAt(1, 'execute', 'BAR BAZ', array('BAR', 'BAZ'));

        $this->assertIdentical( 'FOO BAR', memoize( array( $callback, 'execute' ), array( 'FOO', 'BAR' ), 'MY:CUSTOM:KEY' ) );
        $this->assertIdentical( 'FOO BAR', memoize( array( $callback, 'execute' ), array( 'BAR', 'BAZ' ), 'MY:CUSTOM:KEY' ), 'Result already memoized' );
        $this->assertIdentical( 'FOO BAR', memoize( array( $callback, 'execute' ), array( 'BAR', 'BAZ' ), array( 'MY', 'CUSTOM', 'KEY' ) ), 'Result already memoized' );

        $this->assertIdentical( 'BAR BAZ', memoize( array( $callback, 'execute' ), array( 'BAR', 'BAZ' ), 'MY:DIFFERENT:KEY' ) );
        $this->assertIdentical( 'BAR BAZ', memoize( array( $callback, 'execute' ), array( 'BAR', 'BAZ' ), 'MY:DIFFERENT:KEY' ), 'Result already memoized' );
        $this->assertIdentical( 'BAR BAZ', memoize( array( $callback, 'execute' ), array( 'FOO', 'BAR' ), 'MY:DIFFERENT:KEY' ), 'Result already memoized' );

        // testResultIsNotStoredIfExceptionIsThrown
        $callback = new MockstdClass();        
        $callback->throwOn('execute');        
        $callback->expectCallCount('execute', 2);

        try {
            memoize( array( $callback, 'execute' ) );
            $this->fail( 'Expected failure' );
        } catch ( Exception $e ) {}

        try {
            memoize( array( $callback, 'execute' ) );
            $this->fail( 'Expected failure' );
        } catch ( Exception $e ) {}

    }

    // MinimumTest.php
    function test_minimum() {
        $array = array( 1, "foo", 5.1, 5, "5.2", true, false, array(), new stdClass() );
        $iterator = new ArrayIterator( $array );
        $hash = array(
            'k1' => 1,
            'k2' => '5.2',
            'k3' => 5,
            'k4' => '5.1',
            'k5' => 10.2,
            'k6' => true,
            'k7' => array(),
            'k8' => new stdClass(),
            'k9' => -10,
        );
        $hashIterator = new ArrayIterator( $hash );

        // testExtractingMinimumValue
        $this->assertEqual( 1, minimum( $array ) );
        $this->assertEqual( 1, minimum( $iterator ) );
        $this->assertEqual( -10, minimum( $hash ) );
        //        $this->assertEqual(-10, minimum($hashIterator));

        // testSpecialCaseNull
        $this->assertIdentical( -1, minimum( array( -1 ) ) );

        // testSpecialCaseSameValueDfferentTypes
        $this->assertIdentical( 1, maximum( array( 0, 1, 0.0, 1.0, "0", "1", "0.0", "1.0" ) ) );
    }

    // NoneTest.php
    function test_none() {
        $goodArray = array( 'value', 'value', 'value' );
        $goodIterator = new ArrayIterator( $goodArray );
        $badArray = array( 'value', 'value', 'foo' );
        $badIterator = new ArrayIterator( $badArray );

        function none_functional_callback($value, $key, $collection) {
            return $value != 'value' && strlen($key) > 0;
        }        

        $this->assertTrue( none( $goodArray, 'none_functional_callback' ) );
        $this->assertTrue( none( $goodIterator, 'none_functional_callback' ) );
        $this->assertFalse( none( $badArray, 'none_functional_callback' ) );
        $this->assertFalse( none( $badIterator, 'none_functional_callback' ) );

        // testExceptionIsThrownInArray()
        // $this->expectException('DomainException');
        // none($goodArray, array($this, 'exception'));

        // testExceptionIsThrownInIterator()
        // $this->expectException('DomainException');
        // none($goodIterator, array($this, 'exception'));
     }

    // PartitionTest.php
    function test_partition() {
        $array = array( 'value1', 'value2', 'value3' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val3' );
        $hashIterator = new ArrayIterator( $hash );
        $fn = function( $v, $k, $collection ) {
            return is_int( $k ) ? ( $k % 2 == 0 ) : ( $v[3] % 2 == 0 );
        };
        $this->assertIdentical( array( array( 0 => 'value1', 2 => 'value3' ), array( 1 => 'value2' ) ), partition( $array, $fn ) );
        $this->assertIdentical( array( array( 0 => 'value1', 2 => 'value3' ), array( 1 => 'value2' ) ), partition( $iterator, $fn ) );
        $this->assertIdentical( array( array( 'k2' => 'val2' ), array( 'k1' => 'val1', 'k3' => 'val3' ) ), partition( $hash, $fn ) );
        $this->assertIdentical( array( array( 'k2' => 'val2' ), array( 'k1' => 'val1', 'k3' => 'val3' ) ), partition( $hashIterator, $fn ) );

        // TODO:
        // function testExceptionIsThrownInArray()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception');
        //     partition($this->array, array($this, 'exception'));
        // }

        // function testExceptionIsThrownInHash()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception');
        //     partition($this->hash, array($this, 'exception'));
        // }

        // function testExceptionIsThrownInIterator()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception');
        //     partition($this->iterator, array($this, 'exception'));
        // }

        // function testExceptionIsThrownInHashIterator()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception');
        //     partition($this->hashIterator, array($this, 'exception'));
        // }
    }

    // TODO: PluckTest.php
    // function test_pluck() {
    //     $propertyExistsEverywhereArray = array( (object)array( 'property' => 1 ), (object)array( 'property' => 2 ) );
    //     $propertyExistsEverywhereIterator = new ArrayIterator( $propertyExistsEverywhereArray );
    //     $propertyExistsSomewhere = array( (object)array( 'property' => 1 ), (object)array( 'otherProperty' => 2 ) );
    //     $propertyMagicGet = array( new MagicGet( array( 'property' => 1 ) ), new MagicGet( array( 'property' => 2 ) ), array( 'property' => '3' ), new ArrayObject( array( 'property' => 4 ) ) );
    //     $mixedCollection = array( (object)array( 'property' => 1 ), array( 'key'  => 'value' ), array( 'property' => 2 ) );
    //     $keyedCollection = array( 'test' => (object)array( 'property' => 1 ), 'test2' => (object)array( 'property' => 2 ) );
    //     $numericArrayCollection = array( 'one' => array( 1 ), 'two' => array( 1 => 2 ), 'three' => array( 'idx' => 2 ), 'four' => new ArrayObject( array( 2 ) ), 'five' => $fixedArray );
    //     $issetExceptionArray = array( (object)array( 'property' => 1 ), new MagicGetException( true, false ) );
    //     $issetExceptionIterator = new ArrayIterator( $issetExceptionArray );
    //     $getExceptionArray = array( (object)array( 'property' => 1 ), new MagicGetException( false, true ) );
    //     $getExceptionIterator = new ArrayIterator( $getExceptionArray );
    //     $this->assertIdentical( array( 1, 2, '3', 4 ), pluck( $propertyMagicGet, 'property' ) );
    //     $this->assertIdentical( array( 1, 2 ), pluck( $propertyExistsEverywhereArray, 'property' ) );
    //     $this->assertIdentical( array( 1, 2 ), pluck( $propertyExistsEverywhereIterator, 'property' ) );

    //     $this->assertIdentical( array( 1, null ), pluck( $propertyExistsSomewhere, 'property' ) );
    //     $this->assertIdentical( array( null, 2 ), pluck( $propertyExistsSomewhere, 'otherProperty' ) );

    //     $this->assertIdentical( array( 1, null, 2 ), pluck( $mixedCollection, 'property' ) );

    //     $this->assertIdentical( array( null, null ), pluck( array( $this, 'foo' ), 'preserveGlobalState' ) );

    //     $this->assertIdentical( array( 'test' => 1, 'test2' => 2 ), pluck( $keyedCollection, 'property' ) );

    //     $this->assertIdentical( array( 'one' => 1, 'two' => null, 'three' => null, 'four' => 2, 'five' => 3 ), pluck( $numericArrayCollection, 0 ) );
    //     $this->assertIdentical( array( 'one' => 1, 'two' => null, 'three' => null, 'four' => 2, 'five' => 3 ), pluck( $numericArrayCollection, 0 ) );
    //     $this->assertIdentical( array( 'one' => 1, 'two' => null, 'three' => null, 'four' => 2, 'five' => 3 ), pluck( new ArrayIterator( $numericArrayCollection ), 0 ) );
    //     $this->assertIdentical( array( 1, null, null, 2, 3 ), pluck( array_values( $numericArrayCollection ), 0 ) );
    //     $this->assertIdentical( array( 1, null, null, 2, 3 ), pluck( new ArrayIterator( array_values( $numericArrayCollection ) ), 0 ) );
    //     $this->assertIdentical( array( 'one' => 1, 'two' => null, 'three' => null, 'four' => 2, 'five' => 3 ), pluck( $numericArrayCollection, '0' ) );

    //     $this->assertIdentical( array( '1', '2' ), pluck( $it, null ) );

    //     $this->assertIdentical( array( 'one' => '1', 'two' => '2' ), pluck( $it, null ) );

    //     $caller = new PluckCaller();
    //     $this->assertIdentical( array( 'test' => 1, 'test2' => 2 ), $caller->call( $keyedCollection, 'property' ) );
    // }

    // ProductTest.php
    function test_product() {
        $intArray = array( 1 => 1, 2, "foo" => 3, 4 );
        $intIterator = new ArrayIterator( $intArray );
        $floatArray = array( "foo" => 1.5, 1.1, 1 );
        $floatIterator = new ArrayIterator( $floatArray );
        $this->assertIdentical( 240, product( $intArray, 10 ) );
        $this->assertIdentical( 240, product( $intArray, 10 ) );
        $this->assertIdentical( 24, product( $intArray ) );
        $this->assertIdentical( 24, product( $intIterator ) );
        $this->assertWithinMargin( 1.65, product( $floatArray ), 0.01 );
        $this->assertWithinMargin( 1.65, product( $floatIterator ), 0.01 );

        foreach($this->error_collections() as $collection)
            $this->assertWithinMargin( 3, product( $collection ), 0.01 );

    }

//     // RatioTest.php
    function test_ratio() {
        $intArray = array( 1 => 1, 2, "foo" => 3, 4 );
        $intIterator = new ArrayIterator( $intArray );
        $floatArray = array( "foo" => 1.5, 1.1, 1 );
        $floatIterator = new ArrayIterator( $floatArray );
        $this->assertIdentical( 1, ratio( array( 1 ) ) );
        $this->assertIdentical( 1, ratio( new ArrayIterator( array( 1 ) ) ) );
        $this->assertIdentical( 1, ratio( $intArray, 24 ) );
        $this->assertIdentical( 1, ratio( $intIterator, 24 ) );
        $this->assertWithinMargin( -1, ratio( $floatArray, -1.65 ), 0.01 );
        $this->assertWithinMargin( -1, ratio( $floatIterator, -1.65 ), 0.01 );

        // testElementsOfWrongTypeAreIgnored
        foreach($this->error_collections() as $collection)
            $this->assertWithinMargin( 0.333, ratio( $collection ), 0.001 );

    }
//     // ReduceTest.php
    function reduce_functional_callback($value, $key, $collection, $returnValue) {
        $this->assertTrue(contains($this->current_collection, $value));
        $this->assertTrue(isset($this->current_collection[$key]));
        $this->assertIdentical($collection, $this->current_collection);

        $ret = $key . ':' . $value;
        if ($returnValue) {
            return $returnValue . ',' . $ret;
        }
        return $ret;
    }

    function test_reduce() {
        $array = array( 'one', 'two', 'three' );
        $iterator = new ArrayIterator( $array );

        $this->current_collection = $array;
        $this->assertIdentical( '0:one,1:two,2:three', reduce_left( $array, array($this, 'reduce_functional_callback') ) );
        $this->assertIdentical( 'default,0:one,1:two,2:three', reduce_left( $array, array($this, 'reduce_functional_callback'), 'default' ) );
        $this->assertIdentical( '2:three,1:two,0:one', reduce_right( $array, array($this, 'reduce_functional_callback') ) );
        $this->assertIdentical( 'default,2:three,1:two,0:one', reduce_right( $array, array($this, 'reduce_functional_callback'), 'default' ) );

        $this->current_collection = $iterator;
        $this->assertIdentical( '0:one,1:two,2:three', reduce_left( $iterator, array($this, 'reduce_functional_callback') ) );
        $this->assertIdentical( 'default,0:one,1:two,2:three', reduce_left( $iterator, array($this, 'reduce_functional_callback'), 'default' ) );
        $this->assertIdentical( '2:three,1:two,0:one', reduce_right( $iterator, array($this, 'reduce_functional_callback') ) );
        $this->assertIdentical( 'default,2:three,1:two,0:one', reduce_right( $iterator, array($this, 'reduce_functional_callback'), 'default' ) );

        $this->assertIdentical( 'initial', reduce_left( array(), function() {}, 'initial' ) );
        $this->assertNull( reduce_left( array(), function() {} ) );
        $this->assertNull( reduce_left( array(), function() {}, null ) );
        $this->assertIdentical( 'initial', reduce_left( new ArrayIterator( array() ), function() {}, 'initial' ) );
        $this->assertNull( reduce_left( new ArrayIterator( array() ), function() {} ) );
        $this->assertNull( reduce_left( new ArrayIterator( array() ), function() {}, null ) );
        $this->assertIdentical( 'initial', reduce_right( array(), function() {}, 'initial' ) );
        $this->assertNull( reduce_right( array(), function() {} ) );
        $this->assertNull( reduce_right( array(), function() {}, null ) );
        $this->assertIdentical( 'initial', reduce_right( new ArrayIterator( array() ), function() {}, 'initial' ) );
        $this->assertNull( reduce_right( new ArrayIterator( array() ), function() {} ) );
        $this->assertNull( reduce_right( new ArrayIterator( array() ), function() {}, null ) );

        // TODO:
        // function testExceptionThrownInIteratorCallbackWhileReduceLeft()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception: 0');
        //     reduce_left($this->iterator, array($this, 'exception'));
        // }

        // function testExceptionThrownInIteratorCallbackWhileReduceRight()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception: 2');
        //     reduce_right($this->iterator, array($this, 'exception'));
        // }

        // function testExceptionThrownInArrayCallbackWhileReduceLeft()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception: 0');
        //     reduce_left($this->array, array($this, 'exception'));
        // }

        // function testExceptionThrownInArrayCallbackWhileReduceRight()
        // {
        //     $this->setExpectedException('DomainException', 'Callback exception: 2');
        //     reduce_right($this->array, array($this, 'exception'));
        // }        
    }

    // RejectTest.php
    function test_reject() {
        $array = array( 'value', 'wrong', 'value' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'value', 'k2' => 'wrong', 'k3' => 'value' );
        $hashIterator = new ArrayIterator( $hash );
        $fn = function( $v, $k, $collection ) {
            return $v == 'wrong' && strlen( $k ) > 0;
        };
        $this->assertIdentical( array( 0 => 'value', 2 => 'value' ), reject( $array, $fn ) );
        $this->assertIdentical( array( 0 => 'value', 2 => 'value' ), reject( $iterator, $fn ) );
        $this->assertIdentical( array( 'k1' => 'value', 'k3' => 'value' ), reject( $hash, $fn ) );
        $this->assertIdentical( array( 'k1' => 'value', 'k3' => 'value' ), reject( $hashIterator, $fn ) );
    }

    // SelectTest.php
    function test_select() {
        $array = array( 'value', 'wrong', 'value' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'value', 'k2' => 'wrong', 'k3' => 'value' );
        $hashIterator = new ArrayIterator( $hash );
        $callback = function( $v, $k, $collection ) {
            return $v == 'value' && strlen( $k ) > 0;
        };

        foreach(array('select', 'filter') as $function_name) {
            $this->assertIdentical( array( 'value', 2 => 'value' ), $function_name( $array, $callback ) );
            $this->assertIdentical( array( 'value', 2 => 'value' ), $function_name( $iterator, $callback ) );
            $this->assertIdentical( array( 'k1' => 'value', 'k3' => 'value' ), $function_name( $hash, $callback ) );
            $this->assertIdentical( array( 'k1' => 'value', 'k3' => 'value' ), $function_name( $hashIterator, $callback ) );
        }
    }

    // SomeTest.php
    function test_some() {
        $goodArray = array( 'value', 'wrong' );
        $goodIterator = new ArrayIterator( $goodArray );
        $badArray = array( 'wrong', 'wrong', 'wrong' );
        $badIterator = new ArrayIterator( $badArray );

        function some_functional_callback($value, $key, $collection) {
            return $value == 'value' && $key === 0;
        }

        $this->assertTrue( some( $goodArray, 'some_functional_callback' ) );
        $this->assertTrue( some( $goodIterator, 'some_functional_callback'  ) );
        $this->assertFalse( some( $badArray, 'some_functional_callback'  ) );
        $this->assertFalse( some( $badIterator, 'some_functional_callback'  ) );
    }

    // SumTest.php
    function test_sum() {
        $intArray = array( 1 => 1, 2, "foo" => 3 );
        $intIterator = new ArrayIterator( $intArray );
        $floatArray = array( 1.1, 2.9, 3.5 );
        $floatIterator = new ArrayIterator( $floatArray );

        $this->assertIdentical( 6, sum( $intArray ) );
        $this->assertIdentical( 6, sum( $intIterator ) );
        $this->assertWithinMargin( 7.5, sum( $floatArray ), 0.01 );
        $this->assertWithinMargin( 7.5, sum( $floatIterator ), 0.01 );
        $this->assertIdentical( 10, sum( $intArray, 4 ) );
        $this->assertIdentical( 10, sum( $intIterator, 4 ) );
        $this->assertWithinMargin( 10, sum( $floatArray, 2.5 ), 0.01 );
        $this->assertWithinMargin( 10, sum( $floatIterator, 2.5 ), 0.01 );

        // testElementsOfWrongTypeAreIgnored
        foreach($this->error_collections() as $collection)
            $this->assertWithinMargin( 3.5, sum( $collection ), 0.1 );
    }

    // TailTest.php
    function test_tail() {
        $array = array( 1, 2, 3, 4 );
        $iterator = new ArrayIterator( $array );
        $badArray = array( 'foo', 'bar', 'baz' );
        $badIterator = new ArrayIterator( $badArray );
        $fn = function( $v, $k, $collection ) {
            return $v > 2;
        };

        $this->assertIdentical( array( 2 => 3, 3 => 4 ), tail( $array, $fn ) );
        $this->assertIdentical( array( 2 => 3, 3 => 4 ), tail( $iterator, $fn ) );
        $this->assertIdentical( array(), tail( $badArray, $fn ) );
        $this->assertIdentical( array(), tail( $badIterator, $fn ) );

        // testWithoutCallback
        $this->assertIdentical( array( 1 => 2, 2 => 3, 3 => 4 ), tail( $array ) );
        $this->assertIdentical( array( 1 => 2, 2 => 3, 3 => 4 ), tail( $array, null ) );
        $this->assertIdentical( array( 1 => 2, 2 => 3, 3 => 4 ), tail( $iterator ) );
        $this->assertIdentical( array( 1 => 2, 2 => 3, 3 => 4 ), tail( $iterator, null ) );
        $this->assertIdentical( array( 1 => 'bar', 2 => 'baz' ), tail( $badArray ) );
        $this->assertIdentical( array( 1 => 'bar', 2 => 'baz' ), tail( $badArray, null ) );
        $this->assertIdentical( array( 1 => 'bar', 2 => 'baz' ), tail( $badIterator ) );
        $this->assertIdentical( array( 1 => 'bar', 2 => 'baz' ), tail( $badIterator, null ) );
    }

    // TrueTest.php
    function test_true() {
        $trueArray = array( true, true, true, true );
        $trueIterator = new ArrayIterator( $trueArray );
        $trueHash = array( 'k1' => true, 'k2' => true, 'k3' => true );
        $trueHashIterator = new ArrayIterator( $trueHash );
        $falseArray = array( true, 1, true );
        $falseIterator = new ArrayIterator( $falseArray );
        $falseHash = array( 'k1' => true, 'k2' => 1, 'k3' => true );
        $falseHashIterator = new ArrayIterator( $falseHash );
        $this->assertTrue( true( array() ) );
        $this->assertTrue( true( new ArrayIterator( array() ) ) );
        $this->assertTrue( true( $trueArray ) );
        $this->assertTrue( true( $trueIterator ) );
        $this->assertTrue( true( $trueHash ) );
        $this->assertTrue( true( $trueHashIterator ) );
        $this->assertFalse( true( $falseArray ) );
        $this->assertFalse( true( $falseIterator ) );
        $this->assertFalse( true( $falseHash ) );
        $this->assertFalse( true( $falseHashIterator ) );
    }

    // TruthyTest.php
    function test_truthy() {
        $trueArray = array( true, true, 'foo', true, true, 1 );
        $trueIterator = new ArrayIterator( $trueArray );
        $trueHash = array( 'k1' => true, 'k2' => 'foo', 'k3' => true, 'k4' => 1 );
        $trueHashIterator = new ArrayIterator( $trueHash );
        $falseArray = array( true, 0, true, null );
        $falseIterator = new ArrayIterator( $falseArray );
        $falseHash = array( 'k1' => true, 'k2' => 0, 'k3' => true, 'k4' => null );
        $falseHashIterator = new ArrayIterator( $falseHash );
        $this->assertTrue( truthy( array() ) );
        $this->assertTrue( truthy( new ArrayIterator( array() ) ) );
        $this->assertTrue( truthy( $trueArray ) );
        $this->assertTrue( truthy( $trueIterator ) );
        $this->assertTrue( truthy( $trueHash ) );
        $this->assertTrue( truthy( $trueHashIterator ) );
        $this->assertFalse( truthy( $falseArray ) );
        $this->assertFalse( truthy( $falseIterator ) );
        $this->assertFalse( truthy( $falseHash ) );
        $this->assertFalse( truthy( $falseHashIterator ) );
    }

    // UniqueTest.php
    function test_unique() {
        $array = array( 'value1', 'value2', 'value1', 'value' );
        $iterator = new ArrayIterator( $array );
        $mixedTypesArray = array( 1, '1', '2', 2, '3', 4 );
        $mixedTypesIterator = new ArrayIterator( $mixedTypesArray );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2', 'k3' => 'val2', 'k1' => 'val1' );
        $hashIterator = new ArrayIterator( $hash );

        // testDefaultBehavior
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2', 3 => 'value' ), unique( $array ) );
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2', 3 => 'value' ), unique( $iterator ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), unique( $hash ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), unique( $hashIterator ) );
        $fn = function( $value, $key, $collection ) {
            return $value;
        };
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2', 3 => 'value' ), unique( $array, $fn ) );
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2', 3 => 'value' ), unique( $iterator, $fn ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), unique( $hash, $fn ) );
        $this->assertIdentical( array( 'k1' => 'val1', 'k2' => 'val2' ), unique( $hashIterator, $fn ) );

        // testUnifyingByClosure
        $fn = function( $value, $key, $collection ) {
            return $key === 0 ? 'zero' : 'else';
        };
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2' ), unique( $array, $fn ) );
        $this->assertIdentical( array( 0 => 'value1', 1 => 'value2' ), unique( $iterator, $fn ) );
        $fn = function( $value, $key, $collection ) {
            return 0;
        };
        $this->assertIdentical( array( 'k1' => 'val1' ), unique( $hash, $fn ) );
        $this->assertIdentical( array( 'k1' => 'val1' ), unique( $hashIterator, $fn ) );

        // testUnifyingStrict
        $this->assertIdentical( array( 0 => 1, 2 => '2', 4 => '3', 5 => 4 ), unique( $mixedTypesArray, null, false ) );
        $this->assertIdentical( array( 1, '1', '2', 2, '3', 4 ), unique( $mixedTypesArray ) );
        $this->assertIdentical( array( 0 => 1, 2 => '2', 4 => '3', 5 => 4 ), unique( $mixedTypesIterator, null, false ) );
        $this->assertIdentical( array( 1, '1', '2', 2, '3', 4 ), unique( $mixedTypesIterator ) );

        $fn = function( $value, $key, $collection ) {
            return $value;
        };

        $this->assertIdentical( array( 0 => 1, 2 => '2', 4 => '3', 5 => 4 ), unique( $mixedTypesArray, $fn, false ) );
        $this->assertIdentical( array( 1, '1', '2', 2, '3', 4 ), unique( $mixedTypesArray, $fn ) );
        $this->assertIdentical( array( 0 => 1, 2 => '2', 4 => '3', 5 => 4 ), unique( $mixedTypesIterator, null, false ) );
        $this->assertIdentical( array( 1, '1', '2', 2, '3', 4 ), unique( $mixedTypesIterator, $fn ) );

        // testPassingNullAsCallback
        $this->assertSame(array(0 => 'value1', 1 => 'value2', 3 => 'value'), unique($array));
        $this->assertSame(array(0 => 'value1', 1 => 'value2', 3 => 'value'), unique($array, null));
        $this->assertSame(array(0 => 'value1', 1 => 'value2', 3 => 'value'), unique($array, null, false));
        $this->assertSame(array(0 => 'value1', 1 => 'value2', 3 => 'value'), unique($array, null, true));

    }

    // ZipTest.php
    function test_zip() {
        $array = array( 'value', 'value' );
        $iterator = new ArrayIterator( $array );
        $hash = array( 'k1' => 'val1', 'k2' => 'val2' );
        $hashIterator = new ArrayIterator( $hash );
        $result = array( array( 'one', 1, -1 ), array( 'two', 2, -2 ), array( 'three', 3, -3 ) );

        // testZippingSameSizedArrays
        $this->assertIdentical( $result, zip( array( 'one', 'two', 'three' ), array( 1, 2, 3 ), array( -1, -2, -3 ) ) );
        $this->assertIdentical(
            $result,
            zip(
                new ArrayIterator( array( 'one', 'two', 'three' ) ),
                new ArrayIterator( array( 1, 2, 3 ) ),
                new ArrayIterator( array( -1, -2, -3 ) )
            )
        );

        // testZippingDifferentlySizedArrays
        $result = array( array( 'one', 1, -1, true ), array( 'two', 2, -2, false ), array( 'three', 3, -3, null ) );
        $this->assertIdentical(
            $result,
            zip( array( 'one', 'two', 'three' ), array( 1, 2, 3 ), array( -1, -2, -3 ), array( true, false ) )
        );

        // testZippingHashes
        $result = array( array( 1, -1 ), array( 2, -2 ), array( true, false ) );
        $this->assertIdentical(
            $result,
            zip(
                array( 'foo' => 1, 'bar' => 2, true ),
                array( 'foo' => -1, 'bar' => -2, false, "ignore" )
            )
        );
        $this->assertIdentical(
            $result,
            zip(
                new ArrayIterator( array( 'foo' => 1, 'bar' => 2, true ) ),
                new ArrayIterator( array( 'foo' => -1, 'bar' => -2, false, "ignore" ) )
            )
        );

        // testZippingWithCallback
        $result = array( 'one1-11', 'two2-2', 'three3-3' );
        $this->assertIdentical(
            $result,
            zip(
                array( 'one', 'two', 'three' ),
                array( 1, 2, 3 ),
                array( -1, -2, -3 ),
                array( true, false ),
                function( $one, $two, $three, $four ) {
                    return $one . $two . $three . $four;
                }
            )
        );
        $this->assertIdentical(
            $result,
            zip(
                new ArrayIterator( array( 'one', 'two', 'three' ) ),
                new ArrayIterator( array( 1, 2, 3 ) ),
                new ArrayIterator( array( -1, -2, -3 ) ),
                new ArrayIterator( array( true, false ) ),
                function( $one, $two, $three, $four ) {
                    return $one . $two . $three . $four;
                }
            )
        );

        // testZippingArraysWithVariousElements
        $object = new stdClass();
        $resource = stream_context_create();
        $result = array(
            array( array( 1 ), $object, array( 2 ) ),
            array( null, 'foo', null ),
            array( $resource, null, 2 )
        );

        $this->assertIdentical(
            $result,
            zip(
                array( array( 1 ), null, $resource ),
                array( $object, 'foo', null ),
                array( array( 2 ), null, 2 )
            )
        );

        // TODO: testZipSpecialCases
        // $this->assertIdentical( array(), zip( array() ) );
        // $this->assertIdentical( array(), zip( array(), array() ) );
        // $this->assertIdentical( array(), zip( array(), array(), function() {
        //             throw new BadFunctionCallException( 'Should not be called' );
        //         } ) );

    }

}



                                    
