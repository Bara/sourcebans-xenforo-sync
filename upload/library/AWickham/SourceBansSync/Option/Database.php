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

class AWickham_SourceBansSync_Option_Database
{
	/**
	 * Verifies database connection information
	 *
	 * @param array $words List of words to censor (from input). Keys: word, exact, replace
	 * @param XenForo_DataWriter $dw Calling DW
	 * @param string $fieldName Name of field/option
	 *
	 * @return true
	 */
	public static function verifyOption(array &$database, XenForo_DataWriter $dw = null, $fieldName = null)
	{
		if (array_key_exists('newInstall', $database))
		{
			return true;
		}
		
		if (!array_key_exists('adapter', $database) ||
			!array_key_exists('host', $database) ||
			!array_key_exists('port', $database) || 
			!array_key_exists('dbname', $database) || 
			!array_key_exists('username', $database) || 
			!array_key_exists('password', $database))
		{
			return false;
		}
		
		try 
		{
			$db = Zend_Db::factory($database['adapter'], array(
				'host' => $database['host'],
				'port' => $database['port'],
				'dbname' => $database['dbname'],
				'username' => $database['username'],
				'password' => $database['password']
			));
			$db->getConnection();
			
			$sbTables = array(
				$database['table_prefix'] . '_admins',
				$database['table_prefix'] . '_admins_servers_groups',
				$database['table_prefix'] . '_banlog',
				$database['table_prefix'] . '_bans',
				$database['table_prefix'] . '_comments',
				$database['table_prefix'] . '_demos',
				$database['table_prefix'] . '_groups',
				$database['table_prefix'] . '_log',
				$database['table_prefix'] . '_mods',
				$database['table_prefix'] . '_overrides',
				$database['table_prefix'] . '_protests',
				$database['table_prefix'] . '_servers',
				$database['table_prefix'] . '_servers_groups',
				$database['table_prefix'] . '_settings',
				$database['table_prefix'] . '_srvgroups',
				$database['table_prefix'] . '_srvgroups_overrides',
				$database['table_prefix'] . '_submissions'
			);
			$query = $db->listTables();
			if (count(array_diff($sbTables, $query)) > 0) {
				$dw->error(new XenForo_Phrase('sourcebans_table_prefix_invalid'));
				return false;				
			}
		} 
		catch (Zend_Db_Adapter_Exception $e)
		{
			if ($dw)
			{
				$dw->error($e->getMessage(), $fieldName);
			}
			return false;
		}
		
		return true;
	}
}