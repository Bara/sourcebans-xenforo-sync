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

class AWickham_SourceBansSync_Model_User extends XFCP_AWickham_SourceBansSync_Model_User
{
	public function getSourceBansGroups($xfUser, $groupMapping = null)
	{
		if (is_array($xfUser))
		{
			$user['user_group_id'] = $xfUser['user_group_id'];
			$user['secondary_group_ids'] = $xfUser['secondary_group_ids'];
		}
		else
		{
			$user['user_group_id'] = $xfUser->get('user_group_id');
			$user['secondary_group_ids'] = $xfUser->get('secondary_group_ids');
		}
		
		// if the primary group is mapped, return that info
		if (array_key_exists($user['user_group_id'], $groupMapping))
		{
			return array(
				$groupMapping[$user['user_group_id']]['web_admin_group_id'],
				$groupMapping[$user['user_group_id']]['server_admin_group_id'],
				$groupMapping[$user['user_group_id']]['server_group_id']					
			);
		}
	
		// go through the secondary groups to see if they need added
		$secondaryGroups = ($user['secondary_group_ids'] ? explode(',', $user['secondary_group_ids']) : array());
		foreach ($secondaryGroups as $groupId)
		{
			// only try to get the group ids if the options have been set for that group
			if (array_key_exists($groupId, $groupMapping))
			{
				return array(
					$groupMapping[$groupId]['web_admin_group_id'],
					$groupMapping[$groupId]['server_admin_group_id'],
					$groupMapping[$groupId]['server_group_id']
				);
			} 
		}
	}
	
	
	// Converts Community ID to Steam ID
	public function toSteamId($communityId)
	{
		if (is_numeric($communityId) && strlen($communityId) >= 16) {
			$z = bcdiv(bcsub($communityId, '76561197960265728'), '2');
		} elseif (is_numeric($communityId)) {
			$z = bcdiv($communityId, '2'); // Actually new User ID format
		} else {
			return $communityId; // We have no idea what this is, so just return it.
		}
		$y = bcmod($communityId, '2');
		return 'STEAM_0:' . $y . ':' . floor($z);
	}
	
	// Converts Steam ID to Community ID
	public function toCommunityId($steamId)
	{
		if (preg_match('/^STEAM_/', $steamId)) {
			$parts = explode(':', $steamId);
			return bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
		} elseif (is_numeric($steamId) && strlen($id) < 16) {
			return bcadd($steamId, '76561197960265728');
		} else {
			return $steamId; // We have no idea what this is, so just return it.
		}
	}
	
	public function getUserBySteamId($steamId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_user_external_auth
			JOIN xf_user ON xf_user.user_id = xf_user_external_auth.user_id
			WHERE provider = \'steam\'
			AND provider_key = ?
		', $steamId);
	}
}