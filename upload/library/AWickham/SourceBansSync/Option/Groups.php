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

class AWickham_SourceBansSync_Option_Groups
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$database = XenForo_Application::get('options')->AWickham_SourceBansSync_Database;
		if (!array_key_exists('newInstall', $database) && AWickham_SourceBansSync_Option_Database::verifyOption($database))
		{
			$preparedOption['formatParams'] = XenForo_Model::create('AWickham_SourceBansSync_Model_Group')->getUserGroupOptions(
				$preparedOption['option_value']
			);

			return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
				'option_list_option_groups',
				$view, $fieldPrefix, $preparedOption, $canEdit
			);			
		}
		else
		{
			return '';
		}
	}
	
	public static function verifyOption(array &$groups, XenForo_DataWriter $dw = null, $fieldName = null)
	{
		// override for a new install, no group validation should be done
		if (is_array($groups) && array_key_exists('newInstall', $groups)) {
			return true;
		}
		
		foreach ($groups as $groupId => $selectedGroups) {
			if (!is_array($selectedGroups)) {
				unset($groups[$groupId]);
			} else {
				if ($selectedGroups['web_admin_group_id'] == '' || $selectedGroups['server_admin_group_id'] == '' || $selectedGroups['server_group_id'] == '') {
					$dw->error(new XenForo_Phrase('sourcebans_group_association_invalid'));
				}
			}
		}
		
		return true;
	}
}