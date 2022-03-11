<?php

namespace dokuwiki\plugin\oauthdiscord;

use dokuwiki\plugin\oauth\Service\AbstractOAuth2Base;
use OAuth\Common\Http\Uri\Uri;

use OAuth\OAuth2\Service\ServiceInterface as ServiceInterface;


/**
 * Custom Service for Discord oAuth
 */
class Discord extends AbstractOAuth2Base
{

    const SCOPE_IDENTIFY = 'identify';
    const SCOPE_EMAIL = 'email';
    const SCOPE_GUILDS = 'guilds';
    const SCOPE_GUILDSMEMBERSREAD = 'guilds.members.read';

    /** @inheritdoc */
    public function getAuthorizationEndpoint()
    {
        $plugin = plugin_load('helper', 'oauthdiscord');
        return new Uri('https://discord.com/api/oauth2/authorize');
    }

    /** @inheritdoc */
    public function getAccessTokenEndpoint()
    {
        $plugin = plugin_load('helper', 'oauthdiscord');
        return new Uri('https://discord.com/api/oauth2/token');
    }
    
    /** @inheritdoc */
    protected function getAuthorizationMethod()
    {
        $plugin = plugin_load('helper', 'oauthdiscord');
        return (int) ServiceInterface::AUTHORIZATION_METHOD_HEADER_BEARER;
    }
    
}
