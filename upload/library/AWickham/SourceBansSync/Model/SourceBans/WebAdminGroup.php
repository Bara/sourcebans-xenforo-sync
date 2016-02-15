<?php

/*
    SourceBans Sync XenForo Add-on
    Copyright (C) 2011  Andrew Wickham

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class AWickham_SourceBansSync_Model_SourceBans_WebAdminGroup extends AWickham_SourceBansSync_Model_SourceBans
{
	public function getSourceBansWebAdminGroupOptions($selectedGroupId)
	{
		$userGroups = array();
		$userGroups[] = array(
			'label' => '-- Select Web Admin Group --',
			'value' => '',
			'selected' => ''
		);
		$userGroups[] = array(
			'label' => 'No Permissions',
			'value' => '-1',
			'selected' => ($selectedGroupId == '-1')
		);
		foreach ($this->_getSourceBansWebAdminGroups() as $webAdminGroup)
		{
			$userGroups[] = array(
				'label' => $webAdminGroup['name'],
				'value' => $webAdminGroup['gid'],
				'selected' => ($selectedGroupId == $webAdminGroup['gid'])
			);
		}
		
		return $userGroups;
	}
	
	public function _getSourceBansWebAdminGroups()
	{
		return $this->_getDb()->fetchAll('
		SELECT gid, name 
		FROM ' . $this->_getTablePrefix() . '_groups
		WHERE type = 1
		');
	}	
	

}