<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class DBSchema implements DialectString
	{
		private $tables = array();
		
		public function addTable(DBTable $table)
		{
			$name = $table->getName();
			
			Assert::isFalse(
				isset($this->tables[$name]),
				"table '{$name}' already exist"
			);
			
			$this->tables[$table->getName()] = $table;
			
			return $this;
		}
		
		public function getTableByName($name)
		{
			Assert::isTrue(
				isset($this->tables[$name]),
				"table '{$name}' does not exist"
			);
			
			return $this->tables[$name];
		}
		
		// TODO: respect dependency order
		public function toString(Dialect $dialect)
		{
			$out = array();
			
			foreach ($this->tables as $name => $table) {
				$out[] = $table->toString($dialect);
			}
			
			return implode("\n\n", $out);
		}
	}
?>