<?php

use dokuwiki\plugin\oauth\Adapter;
use dokuwiki\plugin\oauthdiscord\Discord;

/**
 * Service Implementation for oAuth Discord authentication
 */
class action_plugin_oauthdiscord extends Adapter
{

    /** @inheritdoc */
    public function registerServiceClass()
    {
        return Discord::class;
    }

    public function loadDiscordMap()
    {
         /** TODO: map should be externalized */
         /** each row is a rule that is applied in order, available keys determine the semantics.
             guildid + grps: assign grps to each member of this guild
             guildid + roleid + grps: assign grps to each member of this guild that has role roleid
             userid + grps: assign grps to discord user 
             grps: grps that are only assigned when none of the above rules match
           */
         return [
             ['guildid' => '<discord id of guild>', 'grps' => [ 'myguild'] ],
             ['guildid' => '<discord id of guild>', 'roleid' => '<discord id of role>', 'grps' => ['extrarole1', 'extrarole2'] ],
             ['userid' => '<discord id of user>', 'grps' => ['extrarole1','extrarole3'] ],
             ['grps' => ['noaccess'] ] 
          ];
    }

    /** * @inheritDoc */
    public function getUser()
    {
        $oauth = $this->getOAuthService();
        $data = array();

        $url = 'https://discord.com/api/users/@me';
        $raw = $oauth->request($url);
        if (!$raw) throw new OAuthException('Failed to fetch data from userurl');
        dbglog($url);dbglog($raw);
        $result = json_decode($raw, true);
        if (!$result) throw new OAuthException('Failed to parse data from userurl');

        $this->getConf('id-as-address');
        $user = $result['username'].'_'.$result['discriminator'];
        $name = $result['username'];
        $mail = $result['email'];
        if($this->getConf('id-as-address')) {
            $mail = $result['id'].'@discord.dom';
        }

        /* discord guild+role to grps loop */
        $dmap = $this->loadDiscordMap(); 
        $grps = array();
        $guilds = NULL;
        $guildmembers = [];
        foreach($dmap as $dr) {
            dbglog('grps '); dbglog($grps);
            dbglog('dr'); dbglog($dr);
            if( isset($dr['guildid']) ) {
                if( is_null($guilds) ) { /* only retrieve guilds if needed */
                    $userguildsurl = 'https://discord.com/api/users/@me/guilds';
                    $rawguilds = $this->oAuth->request($userguildsurl);
                    dbglog($userguildsurl);dbglog($rawguilds);
                    $guilds = json_decode($rawguilds);
                }
                foreach( $guilds as $g ) {
                    if( $g->id == $dr['guildid'] ) {
                        if( isset($dr['roleid']) ) {
                            if( !isset($guildmembers[$dr['guildid']]) ) { /* only retrieve roles if needed */
                                $usermemberurl = 'https://discord.com/api/users/@me/guilds/'.$dr['guildid'].'/member';
                                $rawmember = $this->oAuth->request($usermemberurl);
                                dbglog($usermemberurl);dbglog($rawmember);
                                $guildmembers[$dr['guildid']] = json_decode($rawmember);
                            }
                            $member = $guildmembers[$dr['guildid']];
                            if( in_array($dr['roleid'], $member->roles) and isset($dr['grps']) ) { 
                                dbglog('case role: adding to grps'); dbglog($dr['grps']);
                                $grps = array_merge($grps, $dr['grps']);
                            } else {
                                dbglog('NOT adding to grps ');
                            }
                        } elseif (isset($dr['grps']) ) { 
                            dbglog('case guild: adding to grps'); dbglog($dr['grps']);
                            $grps = array_merge($grps, $dr['grps']);
                        }
                    }
                }
            } elseif (isset($dr['userid'])) {
                if($result['id'] == $dr['userid'] and isset($dr['grps'])) { 
                    dbglog('case userid: adding to grps '); dbglog($dr['grps']);
                    $grps = array_merge($grps, $dr['grps']);
                } else {
                    dbglog('case userid: skip');
                }
            } elseif (isset($dr['grps'])) {
                if(count($grps) == 0) { /* Note the count == 0 */
                    dbglog('case other: adding to grps '); dbglog($dr['grps']);
                    $grps = $dr['grps'];
                } else {
                    dbglog('case other: skip');
               }
            } else {
                dbglog('case unknown configuration format: skip');
            }
        }
        $grps = array_unique($grps);

        /* OAuthManager only creates user, does not handle grps additions and removals */ 
        /** @var \auth_plugin_oauth $auth */
        global $auth;

        $localUser = $auth->getUserByEmail($mail);
        if ($localUser) {
            $localUserInfo = $auth->getUserData($localUser);
            $correctgrps = array_unique(array_merge($grps, ['user','discord']));
            $diff = array_diff($localUserInfo['grps'], $correctgrps);
            dbglog('diff');dbglog($localUserInfo['grps']);dbglog($correctgrps);dbglog($diff);
            if( count($localUserInfo['grps']) != count($correctgrps) or count($diff) > 0 ) {
                $auth->modifyUser($localUser, ['grps' => $correctgrps ]);
            }
        }

        $ret = compact('user', 'name', 'mail', 'grps');
	dbglog('discord getUser returns'); dbglog($ret);
	return $ret;
    }

    /** @inheritdoc */
    public function getScopes()
    {
        $scopes = [ 'identify' ];
        if(!$this->getConf('id-as-address')) {
            $scopes[] = 'email';
        }
        $scopes[] = 'guilds';
        $scopes[] = 'guilds.members.read';
        dbglog('getScopes'); dbglog($scopes);
        return $scopes;
    }

    /** @inheritDoc */
    public function getLabel()
    {
        return 'Discord';
    }

    /** @inheritDoc */
    public function getColor()
    {
        return '#5865F2';
    }
}
