<?php
/**
 * This module provides a set of functional primitives for PHP, heavily inspired
 * by Scala’s traversable collection, Dojo’s array functions and Underscore.js
 *
 * Original code from Lars Strojny and his functional-php repository on github
 * https://github.com/lstrojny/functional-php
 *
 * @ingroup helperclass
 */


class FunctionalHelper {


	/**
	 * Returns the average of all numeric values in the array or null if no numeric value was found
	 *
	 * @param \Traversable|array $collection
	 * @return null|float|int
	 */
	function average( $collection ) {
		$sum = null;
		$divisor = 0;

		foreach ( $collection as $element ) {
			if ( is_numeric( $element ) ) {
				$sum += $element;
				++$divisor;
			}
		}

		if ( $sum === null ) {
			return null;
		}

		return $sum / $divisor;
	}

	/**
	 * Returns true if the collection contains the given value. If the third parameter is
	 * true values will be compared in strict mode
	 *
	 * @param \Traversable|array $collection
	 * @param mixed   $value
	 * @param bool    $strict
	 * @return bool
	 */
	function contains( $collection, $value, $strict = true ) {

		foreach ( $collection as $element ) {
			if ( $value === $element || ( !$strict && $value == $element ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Takes a collection and returns the difference of all elements
	 *
	 * @param \Traversable|array $collection
	 * @return integer|float
	 */
	function difference( $collection, $initial = 0 ) {

		$result = $initial;
		foreach ( $collection as $value ) {

			if ( is_numeric( $value ) ) {
				$result -= $value;
			}

		}

		return $result;
	}

	/**
	 * Drop all elements from a collection until callback returns false
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function drop_first( $collection, $callback ) {

		$result = array();

		$drop = true;
		foreach ( $collection as $index => $element ) {

			if ( $drop ) {
				if ( !call_user_func( $callback, $element, $index, $collection ) ) {
					$drop = false;
				} else {
					continue;
				}
			}

			$result[$index] = $element;
		}

		return $result;
	}

	/**
	 * Drop all elements from a collection after callback returns true
	 *
	 * @param \Traversable|array $collection
	 * @param callable|integer $callback
	 * @return array
	 */
	function drop_last( $collection, $callback ) {

		$result = array();

		$drop = false;
		foreach ( $collection as $index => $element ) {

			if ( !$drop && !call_user_func( $callback, $element, $index, $collection ) ) {
				break;
			}

			$result[$index] = $element;
		}

		return $result;
	}

	/**
	 * Iterates over a collection of elements, yielding each in turn to a callback function. Each invocation of $callback
	 * is called with three arguments: (element, index, collection)
	 *
	 * TODO: MUST BE RENAMED
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return null
	 */
	static function each( $collection, $callback ) {

		foreach ( $collection as $index => $element ) {
			call_user_func( $callback, $element, $index, $collection );
		}
	}

	/**
	 * Returns true if every value in the collection passes the callback truthy test. Opposite of Functional\none().
	 * Callback arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return bool
	 */
	function every( $collection, $callback ) {

		foreach ( $collection as $index => $element ) {

			if ( !call_user_func( $callback, $element, $index, $collection ) ) {
				return false;
			}

		}

		return true;
	}

	/**
	 * Returns true if all elements of the collection are strictly false
	 *
	 * @param \Traversable|array $collection
	 * @return bool
	 */
	function false( $collection ) {

		foreach ( $collection as $value ) {
			if ( $value !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if all elements of the collection evaluate to false
	 *
	 * @param \Traversable|array $collection
	 * @return bool
	 */
	function falsy( $collection ) {

		foreach ( $collection as $value ) {
			if ( $value ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Alias of Functional\select()
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function filter( $collection, $callback ) {

		return select( $collection, $callback );
	}

	/**
	 * Looks through each element in the collection, returning the first one that passes a truthy test (callback). The
	 * function returns as soon as it finds an acceptable element, and doesn't traverse the entire collection. Callback
	 * arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return mixed
	 */
	function first( $collection, $callback = null ) {

		if ( $callback !== null ) {
		}

		foreach ( $collection as $index => $element ) {

			if ( $callback === null ) {
				return $element;
			}

			if ( call_user_func( $callback, $element, $index, $collection ) ) {
				return $element;
			}

		}
	}

	/**
	 * Returns the first index holding specified value in the collection. Returns false if value was not found
	 *
	 * @param \Traversable|array $collection
	 * @param mixed   $value
	 * @return mixed
	 */
	function first_index_of( $collection, $value ) {

		foreach ( $collection as $index => $element ) {
			if ( $element === $value ) {
				return $index;
			}
		}

		return false;
	}

	/**
	* Takes a nested combination of collections and returns their contents as a single, flat array.
	* Does not preserve indexes.
	*
	* @param \Traversable|array $collection
	* @return array
	*/
	function flatten($collection)
	{
	    $it = new RecursiveIteratorIterator(new RecursiveArrayOnlyIterator($collection));

	    $result = array();
	    foreach($it as $val) {
	        $result[] = $val;
	    }

	    return $result;
	}	

	/**
	 * Groups a collection by index returned by callback.
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function group( $collection, $callback ) {

		$groups = array();

		foreach ( $collection as $index => $element ) {
			$groupKey = call_user_func( $callback, $element, $index, $collection );


			if ( !isset( $groups[$groupKey] ) ) {
				$groups[$groupKey] = array();
			}

			$groups[$groupKey][$index] = $element;
		}

		return $groups;
	}

	/**
	 * Alias for Functional\first
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return mixed
	 */
	function head( $collection, $callback = null ) {
		return first( $collection, $callback );
	}

	/**
	 * Calls the method named by $methodName on each value in the collection. Any extra arguments passed to invoke will be
	 * forwarded on to the method invocation.
	 *
	 * @param \Traversable|array $collection
	 * @param string  $methodName
	 * @param array   $arguments
	 * @return array
	 */
	function invoke( $collection, $methodName, array $arguments = array() ) {

		$aggregation = array();

		foreach ( $collection as $index => $element ) {

			$value = null;

			$callback = array( $element, $methodName );
			if ( is_callable( $callback ) ) {
				$value = call_user_func_array( $callback, $arguments );
			}

			$aggregation[$index] = $value;
		}

		return $aggregation;
	}

	/**
	 * Calls the method named by $methodName on first value in the collection. Any extra arguments passed to invoke will be
	 * forwarded on to the method invocation.
	 *
	 * @param \Traversable|array $collection
	 * @param string  $methodName
	 * @param array   $arguments
	 * @return array
	 */
	function invoke_first( $collection, $methodName, array $arguments = array() ) {

		foreach ( $collection as $index => $element ) {

			$callback = array( $element, $methodName );
			if ( is_callable( $callback ) ) {
				return call_user_func_array( $callback, $arguments );
			}
		}

		return null;
	}

	/**
	 * Calls the method named by $methodName on $object. Any extra arguments passed to invoke_if will be
	 * forwarded on to the method invocation. If $method is not callable on $object, $defaultValue is returned.
	 *
	 * @param mixed   $object
	 * @param string  $methodName
	 * @param array   $methodArguments
	 * @param mixed   $defaultValue
	 * @return mixed
	 */
	function invoke_if( $object, $methodName, array $methodArguments = array(), $defaultValue = null ) {
		$callback = array( $object, $methodName );
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, $methodArguments );
		}

		return $defaultValue;
	}

	/**
	 * Calls the method named by $methodName on last value in the collection. Any extra arguments passed to invoke will be
	 * forwarded on to the method invocation.
	 *
	 * @param \Traversable|array $collection
	 * @param string  $methodName
	 * @param array   $arguments
	 * @return array
	 */
	function invoke_last( $collection, $methodName, array $arguments = array() ) {

		$lastCallback = null;

		foreach ( $collection as $index => $element ) {

			$value = null;

			$callback = array( $element, $methodName );
			if ( is_callable( $callback ) ) {
				$lastCallback = $callback;
			}
		}

		if ( !$lastCallback ) {
			return null;
		}

		return call_user_func_array( $lastCallback, $arguments );
	}

	/**
	 * Looks through each element in the collection, returning the last one that passes a truthy test (callback).
	 * Callback arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return mixed
	 */
	function last( $collection, $callback = null ) {

		if ( $callback !== null ) {
		}

		$match = null;
		foreach ( $collection as $index => $element ) {

			if ( $callback === null || call_user_func( $callback, $element, $index, $collection ) ) {
				$match = $element;
			}

		}

		return $match;
	}

	/**
	 * Returns the last index holding specified value in the collection. Returns false if value was not found
	 *
	 * @param \Traversable|array $collection
	 * @param mixed   $value
	 * @return mixed
	 */
	function last_index_of( $collection, $value ) {

		$matchingIndex = false;

		foreach ( $collection as $index => $element ) {
			if ( $element === $value ) {
				$matchingIndex = $index;
			}
		}

		return $matchingIndex;
	}

	/**
	 * Produces a new array of elements by mapping each element in collection through a transformation function (callback).
	 * Callback arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function map( $collection, $callback ) {

		$aggregation = array();

		foreach ( $collection as $index => $element ) {
			$aggregation[$index] = call_user_func( $callback, $element, $index, $collection );
		}

		return $aggregation;
	}

	/**
	 * Returns the maximum value of a collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function maximum( $collection ) {

		$max = null;

		foreach ( $collection as $index => $element ) {

			if ( !is_numeric( $element ) ) {
				continue;
			}

			if ( $element > $max || $max === null ) {
				$max = $element;
			}
		}

		return $max;
	}

	/**
	 * Memoizes callbacks and returns their value instead of calling them
	 *
	 * @param callable $callback  Callable closure or function
	 * @param array   $arguments Arguments
	 * @param array|string $key       Optional memoize key to override the auto calculated hash
	 * @return mixed
	 */
	function memoize( $callback, array $arguments = array(), $key = null ) {

		static $keyGenerator = null,
		$storage = array();

		if ( !$keyGenerator ) {
			$keyGenerator = function( $value ) use ( &$keyGenerator ) {
				$type = gettype( $value );
				if ( $type === 'array' ) {
					$key = join( ':', map( $value, $keyGenerator ) );
				} elseif ( $type === 'object' ) {
					$key = get_class( $value ) . ':' . spl_object_hash( $value );
				} else {
					$key = (string) $value;
				}

				return $key;
			};
		}

		if ( $key === null ) {
			$key = $keyGenerator( array_merge( array( $callback ), $arguments ) );
		} else {
			$key = $keyGenerator( $key );
		}

		if ( !isset( $storage[$key] ) && !array_key_exists( $key, $storage ) ) {
			$storage[$key] = call_user_func_array( $callback, $arguments );
		}

		return $storage[$key];
	}

	/**
	 * Returns the minimum value of a collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function minimum( $collection ) {

		$max = null;

		foreach ( $collection as $index => $element ) {

			if ( !is_numeric( $element ) ) {
				continue;
			}

			if ( $element < $max || $max === null ) {
				$max = $element;
			}
		}

		return $max;
	}

	/**
	 * Returns true if all of the elements in the collection pass the callback falsy test. Opposite of Functional\all().
	 * Callback arguments will be element, index, collection.
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return bool
	 */
	function none( $collection, $callback ) {

		foreach ( $collection as $index => $element ) {

			if ( call_user_func( $callback, $element, $index, $collection ) ) {
				return false;
			}

		}

		return true;
	}

	/**
	 * Partitions a collection by callback result. The truthy partition is the first one
	 * (array index "0"), the falsy the second one (array index "1")
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function partition( $collection, $callback ) {

		$partitions = array(
			0 => array(),
			1 => array()
		);

		foreach ( $collection as $index => $element ) {
			$partitionKey = call_user_func( $callback, $element, $index, $collection ) ? 0 : 1;
			$partitions[$partitionKey][$index] = $element;
		}

		return $partitions;
	}

	/**
	 * Extract a property from a collection of objects.
	 *
	 * @param \Traversable|array $collection
	 * @param string  $propertyName
	 * @return array
	 */
	function pluck( $collection, $propertyName ) {

		$aggregation = array();

		foreach ( $collection as $index => $element ) {

			$value = null;

			if ( is_object( $element ) && isset( $element->{$propertyName} ) ) {
				$value = $element->{$propertyName};
			} elseif ( ( is_array( $element ) || $element instanceof ArrayAccess ) && isset( $element[$propertyName] ) ) {
				$value = $element[$propertyName];
			}

			$aggregation[$index] = $value;
		}

		return $aggregation;
	}

	/**
	 * Takes a collection and returns the product of all elements
	 *
	 * @param \Traversable|array $collection
	 * @return integer|float
	 */
	function product( $collection, $initial = 1 ) {

		$result = $initial;
		foreach ( $collection as $value ) {

			if ( is_numeric( $value ) ) {
				$result *= $value;
			}

		}

		return $result;
	}

	/**
	 * Takes a collection and returns the quotient of all elements
	 *
	 * @param \Traversable|array $collection
	 * @return integer|float
	 */
	function ratio( $collection, $initial = 1 ) {

		$result = $initial;
		foreach ( $collection as $value ) {

			if ( is_numeric( $value ) ) {
				$result /= $value;
			}

		}

		return $result;
	}

	/**
	 *
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function reduce_left( $collection, $callback, $initial = null ) {

		foreach ( $collection as $index => $value ) {
			$initial = call_user_func( $callback, $value, $index, $collection, $initial );
		}

		return $initial;
	}

	/**
	 *
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function reduce_right( $collection, $callback, $initial = null ) {

		$data = array();
		foreach ( $collection as $index => $value ) {
			$data[] = array( $index, $value );
		}

		while ( list( $index, $value ) = array_pop( $data ) ) {
			$initial = call_user_func( $callback, $value, $index, $collection, $initial );
		}

		return $initial;
	}

	/**
	 * Returns the elements in list without the elements that the truthy test (callback) passes. The opposite of
	 * Functional\select(). Callback arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function reject( $collection, $callback ) {

		$aggregation = array();

		foreach ( $collection as $index => $element ) {

			if ( !call_user_func( $callback, $element, $index, $collection ) ) {
				$aggregation[$index] = $element;
			}

		}

		return $aggregation;
	}

	/**
	 * Looks through each element in the list, returning an array of all the elements that pass a truthy test (callback).
	 * Opposite is Functional\reject(). Callback arguments will be element, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function select( $collection, $callback ) {

		$aggregation = array();

		foreach ( $collection as $index => $element ) {

			if ( call_user_func( $callback, $element, $index, $collection ) ) {
				$aggregation[$index] = $element;
			}

		}

		return $aggregation;
	}

	/**
	 * Returns true if some of the elements in the collection pass the callback truthy test. Short-circuits and stops
	 * traversing the collection if a truthy element is found. Callback arguments will be value, index, collection
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return bool
	 */
	function some( $collection, $callback ) {

		foreach ( $collection as $index => $element ) {

			if ( call_user_func( $callback, $element, $index, $collection ) ) {
				return true;
			}

		}

		return false;
	}

	/**
	 * Takes a collection and returns the sum of the elements
	 *
	 * @param \Traversable|array $collection
	 * @return integer|float
	 */
	function sum( $collection, $initial = 0 ) {

		$result = $initial;
		foreach ( $collection as $value ) {

			if ( is_numeric( $value ) ) {
				$result += $value;
			}

		}

		return $result;
	}

	/**
	 * Returns all items from $collection except first element (head). Preserves $collection keys.
	 * Takes an optional callback for filtering the collection.
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function tail( $collection, $callback = null ) {

		if ( $callback !== null ) {
		}

		$tail = array();
		$isHead = true;

		foreach ( $collection as $index => $element ) {
			if ( $isHead ) {
				$isHead = false;
				continue;
			}

			if ( !$callback || call_user_func( $callback, $element, $index, $collection ) ) {
				$tail[$index] = $element;
			}
		}

		return $tail;
	}

	/**
	 * Returns true if all elements of the collection are strictly true
	 *
	 * @param \Traversable|array $collection
	 * @return bool
	 */
	function true( $collection ) {

		foreach ( $collection as $value ) {
			if ( $value !== true ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if all elements of the collection evaluate to true
	 *
	 * @param \Traversable|array $collection
	 * @return bool
	 */
	function truthy( $collection ) {

		foreach ( $collection as $value ) {
			if ( !$value ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of unique elements
	 *
	 * @param \Traversable|array $collection
	 * @param callable $callback
	 * @return array
	 */
	function unique( $collection, $callback = null, $strict = true ) {
		if ( $callback != null ) {
		}

		$indexes = array();
		$aggregation = array();
		foreach ( $collection as $key => $element ) {

			if ( $callback ) {
				$index = call_user_func( $callback, $element, $key, $collection );
			} else {
				$index = $element;
			}

			if ( !in_array( $index, $indexes, $strict ) ) {
				$aggregation[$key] = $element;

				$indexes[] = $index;
			}
		}

		return $aggregation;
	}

	/**
	 * Recombines arrays by index and applies a callback optionally
	 *
	 * @param \Traversable|array $collection One or more callbacks
	 * @param callable $callback   Optionally the last argument can be a callback
	 * @return array
	 */
	function zip( $collection ) {
		$args = func_get_args();

		$callback = null;
		if ( is_callable( end( $args ) ) ) {
			$callback = array_pop( $args );
		}

		foreach ( $args as $position => $collection ) {
		}

		$result = array();
		foreach ( func_get_arg( 0 ) as $index => $value ) {
			$zipped = array();

			foreach ( $args as $arg ) {
				$zipped[] = isset( $arg[$index] ) ? $arg[$index] : null;
			}

			if ( $callback !== null ) {
				$zipped = call_user_func_array( $callback, $zipped );
			}

			$result[] = $zipped;
		}

		return $result;
	}

}

class RecursiveArrayOnlyIterator extends RecursiveArrayIterator
{
	public function hasChildren() {
		return is_array( $this->current() ) || $this->current() instanceof Traversable;
	}
}



/**
 * we don't register this helper by default, register it only if you need its
 * functionalities
 * */
//Wordless::register_helper('FunctionalHelper');
