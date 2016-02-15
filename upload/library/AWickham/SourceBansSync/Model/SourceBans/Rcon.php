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

class AWickham_SourceBansSync_Model_SourceBans_Rcon {
	public static function rehash() 
	{
		$sbServersModel = new AWickham_SourceBansSync_Model_SourceBans_Server();
		foreach ($sbServersModel->getServers() as $server)
		{
			if (strlen($server['rcon']) > 0)
			{
				$rcon = new AWickham_SourceBansSync_CServerRcon($server['ip'], $server['port'], $server['rcon']);
				if(!@$rcon->Auth())
				{
					continue;
				}
				@$rcon->rconCommand("sm_reloadadmins");
				unset($rcon);
			}
		}		
	}
}