<?php

namespace BrainStorm\Ebay;

use GuzzleHttp\Client;
use Phpfastcache\Helper\Psr16Adapter;

class EbayRequest
{
    protected $appId;
    protected $devId;
    protected $certId;
    protected $RuName;
    protected $sandbox = 'https://api.sandbox.ebay.com';
    protected $production = 'https://api.ebay.com';
    protected $client;
    protected $scope;
    protected $codeAuth;
    public $cache;
    public $url;
    public $version;
    public $siteid;
    
    public function __construct(array $args, Psr16Adapter $cache)
    {
        $this->url = (($args['env'] == 'sandbox') ? $this->sandbox : $this->production);
        $this->appId = (!empty($args['appId']) ? $args['appId'] : '');
        $this->devId = (!empty($args['devId']) ? $args['devId'] : '');
        $this->certId = (!empty($args['certId']) ? $args['certId'] : '');
        $this->RuName = (!empty($args['RuName']) ? $args['RuName'] : '');
        $this->scope = (!empty($args['scope']) ? $args['scope'] : '');
        $this->version = (!empty($args['version']) ? $args['version'] : '');
        $this->siteid = (!empty($args['siteid']) ? $args['siteid'] : '');
        $this->codeAuth = base64_encode(sprintf('%s:%s', $this->appId, $this->certId));
        $this->client = new Client(['base_uri' => $this->url]);
        $this->cache = $cache;
    }
    protected function getAppToken()
    {
        if (!$this->cache->has('access_token')) {
            $response = $this->client->request('POST', '/identity/v1/oauth2/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '. $this->codeAuth
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => 'https://api.ebay.com/oauth/api_scope'
            ]
            ]);
            $myresponse = json_decode($response->getBody(), true);
            $this->cache->set('access_token', $myresponse['access_token'], $myresponse['expires_in']);
            return $myresponse['access_token'];
        } else {
            return $this->cache->get('access_token');
        }
    }
    public function getUrlUserConsent()
    {
        return 'https://auth.sandbox.ebay.com/oauth2/authorize?client_id='. $this->appId .'&redirect_uri='. $this->RuName .'&response_type=code&scope='. $this->scope .'&prompt=login';
    }
    public function getUserToken(string $refresh_token = null)
    {
        if ($this->cache->has('user_token')) {
            return $this->cache->get('user_token');
        } else {
            return (!empty($refresh_token) ? $this->refreshUserToken($refresh_token) : '');
        }
    }
    protected function getEbayUserToken(string $auth)
    {
        $response = $this->client->request('POST', '/identity/v1/oauth2/token', [
    'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic '. $this->codeAuth
    ],
    'form_params' => [
        'grant_type' => 'authorization_code',
        'code' => $auth,
        'redirect_uri' => $this->RuName
    ]
    ]);
        return json_decode($response->getBody(), true);
    }
    public function setAuthToken(string $token, string $expire)
    {
        if (!empty($token) && !empty($expire)) {
            if (!$this->cache->has('auth_token')) {
                if (!$this->cache->has('refresh_token')) {
                    $usertoken = $this->getEbayUserToken($token);
                    if (!empty($usertoken['access_token']) && !empty($usertoken['expires_in'])) {
                        $this->cache->set('user_token', $usertoken['access_token'], $usertoken['expires_in']);
                        $this->cache->set('refresh_token', $usertoken['refresh_token'], $usertoken['refresh_token_expires_in']);
                    }
                } else {
                    if (!$this->cache->has('user_token')) {
                        $refresh_token = $this->cache->get('refresh_token');
                        $user_token = $this->refreshUserToken($refresh_token);
                    }
                }
                $this->cache->set('auth_token', $token, $expire);
            } else {
                if (!$this->cache->has('user_token')) {
                    $refresh_token = $this->cache->get('refresh_token');
                    $user_token = $this->refreshUserToken($refresh_token);
                }
            }
            return $this->cache->get('refresh_token');
        }
    }
    public function refreshUserToken(string $token)
    {
        $response = $this->client->request('POST', '/identity/v1/oauth2/token', [
    'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic '. $this->codeAuth
    ],
    'form_params' => [
        'grant_type' => 'refresh_token',
        'refresh_token' => $token,
        'scope' => $this->scope
    ]
    ]);
        if (!empty($response->getBody())) {
            $myresponse = json_decode($response->getBody(), true);
            $this->cache->set('user_token', $myresponse['access_token'], $myresponse['expires_in']);
        }
        return $this->cache->get('user_token');
    }
}
