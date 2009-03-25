<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Inheritable Singleton's pattern implementation.
	 * 
	 * @ingroup Base
	 * 
	 * @example singleton.php
	**/
	abstract class Singleton
	{
		protected function __construct() {/* you can't create me */}
		
		final public static function getInstance(
			$class = null, $args = null /* , ... */
		)
		{
			static $instances = array();
			
			if (null == $class) {
				static $wrapper = null;
				
				if (null == $wrapper)
					$wrapper = new SingletonInstance();
				
				return $wrapper;
			}
			
			if (!isset($instances[$class])) {
				// for Singleton::getInstance('class_name', $arg1, ...) calling
				if (2 < func_num_args()) {
					$args = func_get_args();
					array_shift($args);
					
					// can't call protected constructor through reflection
					eval(
						'$object = new '.$class
						.'($args['.implode('],$args[', array_keys($args)).']);'
					);
				
				} else {
					$object =
						$args
							? new $class($args)
							: new $class();
				}
				
				Assert::isTrue(
					$object instanceof Singleton,
					"Class '{$class}' is something not a Singleton's child"
				);
				
				$instances[$class] = $object;
			}
			
			return $instances[$class];
		}
		
		final private function __clone() {/* do not clone me */}
	}
	
	/**
	 * @ingroup Base
	**/
	final class SingletonInstance extends Singleton
	{
		public function __call($class, $args = null)
		{
			return Singleton::getInstance($class, $args);
		}
	}
?>