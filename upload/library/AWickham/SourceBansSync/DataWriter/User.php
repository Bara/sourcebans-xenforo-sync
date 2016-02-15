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

class AWickham_SourceBansSync_DataWriter_User extends XFCP_AWickham_SourceBansSync_DataWriter_User
{
	protected $_identities;
	protected $_currentSteamId;
	
	protected function _postDelete() {
		// $identities = $this->_getUserModel()->getIdentities($this->get('user_id'));
		$identities = $this->_getUserModel()->getUserAuthenticationObjectByUserId($this->get('user_id'));
		
		// create a user model
		$xfUserModel = XenForo_Model::create('XenForo_Model_User');
		
		// delete the user from source bans
		if ($identities && array_key_exists('steam', $identities))
		{
			$adminModel = new AWickham_SourceBansSync_Model_SourceBans_Admin();
			$adminModel->delete($this->get('user_id'), $xfUserModel->toSteamId($identities['steam']['provider_key']));
			
			// rehash the sourcebans servers
			AWickham_SourceBansSync_Model_SourceBans_Rcon::rehash();
		}
		
		parent::_postDelete();
	}
	
	protected function _preSave()
	{
		parent::_preSave();
		
		// create a user model
		$xfUserModel = XenForo_Model::create('XenForo_Model_User');
		
		// get the user's pre-save identities
		if ($this->_existingData != array())
		{
			$this->_identities = $this->_getUserModel()->getUserAuthenticationObjectByUserId($this->get('user_id'));
			$identities = unserialize($this->get('identities'));
			
			// set the current steam id
			if ($identities && array_key_exists('steam', $identities))
			{
				$this->_currentSteamId = $xfUserModel->toSteamId($identities['steam']['provider_key']);
			}
	
			// if the steam id changed
			if ($identities && (array_key_exists('steam', $this->_identities) && array_key_exists('steam', $identities) && $this->_identities['steam'] != $identities['steam']))
			{
				$this->_currentSteamId = $xfUserModel->toSteamId($this->_identities['steam']['provider_key']);
				
				// see if the Steam ID is already in use
				$adminModel = new AWickham_SourceBansSync_Model_SourceBans_Admin();
				if ($adminModel->fetchBySteamId($xfUserModel->toSteamId($identities['steam']['provider_key'])))
				{
					$this->error(new XenForo_Phrase('steam_id_already_in_use'), 'identities');
				} 			
			}
		}
	}
	
	protected function _postSave()
	{
		parent::_postSave();
		
		// get the source bans admin model
		$sbAdminModel = new AWickham_SourceBansSync_Model_SourceBans_Admin();
		$xfUserModel = XenForo_Model::create('XenForo_Model_User');
		$xfUserExternalModel = XenForo_Model::create('XenForo_Model_UserExternal');

		// get the user's new identities
		$identities = unserialize($this->get('identities'));
		
		// only do this if the steam id is set
		if ($identities && array_key_exists('steam', $identities))
		{
			// check to see if the user exists in source bans
			$sbUser = $sbAdminModel->fetchBySteamId($xfUserModel->toSteamId($this->_currentSteamId));
			
			// add the user to source bans
			if (!$sbUser)
			{
				// figure out the groups
				list ($gid, $srvgroups_id, $serverGroupId) = $xfUserModel->getSourceBansGroups($this, XenForo_Application::get('options')->AWickham_SourceBansSync_Groups);
				if ($gid && $srvgroups_id && $serverGroupId)
				{
					// add the user
					$insertValues = array(
						'user' => $this->get('username'),
						'email' => $this->get('email'),
						'authid' => $xfUserModel->toSteamId($identities['steam']['provider_key']),
						'password' => XenForo_Application::generateRandomString(8),
						'gid' => $gid,
						'srvgroups_id' => $srvgroups_id,
						'server_group_id' => $serverGroupId,
						'validate' => '',
						'extraflags' => 0
					);
					$sbAdminModel->insert($this->get('user_id'), $insertValues);

					// rehash the sourcebans servers
					AWickham_SourceBansSync_Model_SourceBans_Rcon::rehash();
				}
			}
			// update an existing user
			else 
			{
				// update the user if anything we care about changed
				if ($this->isChanged('user_group_id') || $this->isChanged('secondary_group_ids') || $this->isChanged('email') || $this->isChanged('username') || (array_key_exists('steam', $this->_identities) && $this->_identities['steam'] != $identities['steam']))
				{
					// figure out the groups
					list ($gid, $srvgroups_id, $serverGroupId) = $xfUserModel->getSourceBansGroups($this, XenForo_Application::get('options')->AWickham_SourceBansSync_Groups);
					if (!$gid || !$srvgroups_id || !$serverGroupId)
					{
						// remove the user from source bans
						$sbAdminModel->delete($this->get('user_id'), $xfUserModel->toSteamId($this->_currentSteamId));
					}
					// update the user, finally
					else 
					{
						$updateArray = array(
							'user' => $this->get('username'),
							'email' => $this->get('email'),
							'authid' => $xfUserModel->toSteamId($identities['steam']['provider_key']),
							'gid' => $gid,
							'srvgroups_id' => $srvgroups_id,
							'server_group_id' => $serverGroupId
						);
						$sbAdminModel->update($xfUserModel->toSteamId($this->_currentSteamId), $updateArray);						
					}
					
					// rehash the sourcebans servers
					AWickham_SourceBansSync_Model_SourceBans_Rcon::rehash();
				}
			}
		}
	}	
}