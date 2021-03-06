<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Flow
 * @ingroup Module
**/
interface ViewResolver
{
	/**
	 * @param	$viewName	string
	 * @return	View
	**/
	public function resolveViewName($viewName);

	/**
	 * @param   $viewName   string
	 * @return  bool
	 */
	public function viewExists($viewName);
}