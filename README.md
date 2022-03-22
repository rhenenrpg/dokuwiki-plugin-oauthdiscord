# dokuwiki-plugin-oauthdiscord

oauthdiscord Plugin for DokuWiki

Discord Service for use with the oAuth Plugin (https://www.dokuwiki.org/plugin:oauth)

NOTES: WORK IN PROGRESS
- install is now manual only
- function loadDiscordMap in action.php is hard coded map (should be externalized)
- function getScopes in action.php is hardcoded, but should take discord map into account and yield minimal required scopes.



All documentation for this plugin can be found at
http://www.dokuwiki.org/plugin:oauthdiscord (URL INCORRECT)

If you install this plugin manually, make sure it is installed in
lib/plugins/oauthdiscord/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----
Copyright (C) Martijn Sanders

based on oauthgeneric Copyright (C) Andreas Gohr <dokuwiki@cosmocode.de>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the COPYING file in your DokuWiki folder for details
