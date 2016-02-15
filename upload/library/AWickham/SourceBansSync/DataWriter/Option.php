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

class AWickham_SourceBansSync_DataWriter_Option extends XFCP_AWickham_SourceBansSync_DataWriter_Option 
{
	protected function _postSave() 
	{
		parent::_postSave();
		
		// update sourcebans if we updated the groups
		if ($this->get('option_id') == 'AWickham_SourceBansSync_Groups') 
		{
			if (is_array(unserialize($this->get('option_value'))) && !array_key_exists('newInstall', unserialize($this->get('option_value')))) 
			{
				// create a group model
				$xfUserGroupModel = XenForo_Model::create('XenForo_Model_UserGroup');
				
				// create a user model
				$xfUserModel = XenForo_Model::create('XenForo_Model_User');
				
				// create a user external model
				$xfUserExternalModel = XenForo_Model::create('XenForo_Model_UserExternal');
				
				// create an admin model
				$sbAdminModel = new AWickham_SourceBansSync_Model_SourceBans_Admin();
				
				// delete the users out of sourcebans that don't exist in xenforo
				$sbAdmins = $sbAdminModel->fetchAll();
				foreach ($sbAdmins as $sbAdmin) 
				{
					$steamAuthKey = $xfUserModel->toCommunityId($sbAdmin['authid']);
					$xfUser = $xfUserModel->getUserBySteamId($steamAuthKey); 
					if (!$xfUser || !$xfUserModel->getSourceBansGroups($xfUser, unserialize($this->get('option_value')))) 
					{
						$sbAdminModel->delete($xfUser['user_id'], $sbAdmin['authid']);
					}
				} 
				
				// loop through all the groups
				foreach (unserialize($this->get('option_value')) as $userGroupId => $sbGroups) 
				{
					//$userGroup = $userGroupModel->getUserGroupById($userGroupId);
					$users = $xfUserGroupModel->getUserIdsInUserGroup($userGroupId);

					foreach ($users as $user => $inGroup) 
					{
						$identities = $xfUserExternalModel->getExternalAuthAssociationsForUser($user);
						
						// pay attention to this user, they have a steam id
						if ($identities && array_key_exists('steam', $identities)) 
						{
							// check to see if the user exists in source bans
							$sbUserSteamId = $xfUserModel->toSteamId($identities['steam']['provider_key']);
							$sbUser = $sbAdminModel->fetchBySteamId($sbUserSteamId);
		
							// get the user
							$xfUser = $xfUserModel->getFullUserById($user);

							// figure out the groups
							list ($gid, $srvGroupsId, $serverGroupId) = $xfUserModel->getSourceBansGroups($xfUser, unserialize($this->get('option_value')));
							
							// add the user to source bans
							if (!$sbUser) 
							{
								if ($gid && $srvGroupsId && $serverGroupId) 
								{
									// add the user
									$insertValues = array(
										'user' => $xfUser['username'],
										'email' => $xfUser['email'],
										'authid' => $sbUserSteamId,
										'password' => XenForo_Application::generateRandomString(8),
										'gid' => $gid,
										'srvgroups_id' => $srvGroupsId,
										'server_group_id' => $serverGroupId,
										'validate' => '',
										'extraflags' => 0
									);
									$sbAdminModel->insert($xfUser['user_id'], $insertValues);
								}
							}
							// update an existing user 
							else 
							{
								if (!$gid || !$srvGroupsId || !$serverGroupId) 
								{
									// remove the user from source bans
									// $sbAdminModel->delete($xfUser, $identities['steam']);
									$sbAdminModel->delete($xfUser, $sbUserSteamId);
								}
								// update the user, finally
								else 
								{
									$updateArray = array(
										'user' => $xfUser['username'],
										'email' => $xfUser['email'],
										'gid' => $gid,
										'srvgroups_id' => $srvGroupsId,
										'server_group_id' => $serverGroupId
									);
									// $sbAdminModel->update($identities['steam'], $updateArray);
									$sbAdminModel->update($sbUserSteamId, $updateArray);
								}
							}
						}
					}
				}
			}

			// rehash the sourcebans servers
			AWickham_SourceBansSync_Model_SourceBans_Rcon::rehash();
		}
	} 
}