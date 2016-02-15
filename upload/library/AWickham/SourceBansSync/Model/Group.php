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

class AWickham_SourceBansSync_Model_Group extends XenForo_Model
{
	public function getUserGroupOptions($selectedGroupIds)
	{
		$serverGroupModel = new AWickham_SourceBansSync_Model_SourceBans_ServerGroup();
		$serverAdminModel = new AWickham_SourceBansSync_Model_SourceBans_ServerAdminGroup();
		$webAdminModel = new AWickham_SourceBansSync_Model_SourceBans_WebAdminGroup();
		
		$userGroups = array();
		foreach ($this->getUserGroups() AS $userGroup)
		{
			$selectedServerGroupId = (array_key_exists($userGroup['user_group_id'], $selectedGroupIds) && is_array($selectedGroupIds[$userGroup['user_group_id']]) && array_key_exists('server_group_id', $selectedGroupIds[$userGroup['user_group_id']])) ? $selectedGroupIds[$userGroup['user_group_id']]['server_group_id'] : array(); 
			$selectedServerAdminGroupId = (array_key_exists($userGroup['user_group_id'], $selectedGroupIds) && is_array($selectedGroupIds[$userGroup['user_group_id']]) && array_key_exists('server_admin_group_id', $selectedGroupIds[$userGroup['user_group_id']])) ? $selectedGroupIds[$userGroup['user_group_id']]['server_admin_group_id'] : array();
			$selectedWebAdminGroupId = (array_key_exists($userGroup['user_group_id'], $selectedGroupIds) && is_array($selectedGroupIds[$userGroup['user_group_id']]) && array_key_exists('web_admin_group_id', $selectedGroupIds[$userGroup['user_group_id']])) ? $selectedGroupIds[$userGroup['user_group_id']]['web_admin_group_id'] : array();
			
			$userGroups[] = array(
				'label' => $userGroup['title'],
				'value' => $userGroup['user_group_id'],
				'selected' => in_array($userGroup['user_group_id'], array_keys($selectedGroupIds)),
				'server_group_id' => $serverGroupModel->getSourceBansServerGroupOptions($selectedServerGroupId),
				'server_admin_group_id' => $serverAdminModel->getSourceBansServerAdminGroupOptions($selectedServerAdminGroupId),
				'web_admin_group_id' => $webAdminModel->getSourceBansWebAdminGroupOptions($selectedWebAdminGroupId)
			);
		}
		
		return $userGroups;
	}
	
	public function getUserGroups()
	{
		return $this->_getDb()->fetchAll('
		SELECT user_group_id, title
		FROM xf_user_group
		WHERE user_group_id !=1
		ORDER BY user_group_id
		');
	}
}