<?php

namespace Tokenly\CounterpartyAssetInfoCache;

use Illuminate\Contracts\Cache\Repository;
use Tokenly\XCPDClient\Client;
use \Exception;

/*
* Cache
*/
class Cache
{
    public function __construct(Repository $laravel_cache, Client $xcpd_client)
    {
        $this->laravel_cache = $laravel_cache;
        $this->xcpd_client = $xcpd_client;
    }

    public function get($asset_name) {
        return $this->laravel_cache->rememberForever($asset_name, function() use ($asset_name) {
            return $this->loadFromXCPD($asset_name);
        });
    }

    public function isDivisible($asset_name) {
        $info = $this->get($asset_name);
        if (!isset($info['divisible'])) { return null; }
        return !!$info['divisible'];
    }

    public function getFromCache($asset_name) {
        return $this->laravel_cache->get($asset_name);
    }

    public function set($asset_name, $asset_info) {
        $this->laravel_cache->forever($asset_name, $asset_info);
    }

    protected function loadFromXCPD($asset_name) {
        $assets = $this->xcpd_client->get_asset_info(['assets' => [$asset_name]]);
        if (!$assets) {
            // this could be a non-valid asset
            return [];
        }
        return $assets[0];
    }



}
