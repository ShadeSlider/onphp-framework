<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov, Anton Lebedevich        *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class Castable
	{
		protected $cast	= null;

		public function castTo($cast)
		{
			$this->cast = $cast;
			
			return $this;
		}
	}
?>