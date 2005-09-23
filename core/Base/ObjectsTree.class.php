<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class ObjectsTree extends NamedObject
	{
		private $parent	= null;

		public function getParent()
		{
			return $this->parent;
		}

		public function setParent(NamedObject $parent)
		{
			Assert::brothers($this, $parent);

			$this->parent = $parent;

			return $this;
		}

		public function dropParent()
		{
			$this->parent = null;

			return $this;
		}
		
		public function getDisplayName($delimiter = ' :: ')
		{
			$name = array($this->getName());
			
			$parent = $this;
			
			while ($parent = $parent->getParent())
				$name[] = $parent->getName();
			
			$name = array_reverse($name);
			
			return implode($delimiter, $name);
		}
	}
?>