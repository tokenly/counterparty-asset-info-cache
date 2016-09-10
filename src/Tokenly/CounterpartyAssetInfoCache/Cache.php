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
        $cached = $this->getFromCache($asset_name);
        if ($cached === null) {
            $info = $this->loadFromXCPD($asset_name);
            if ($info) {
                $this->laravel_cache->forever($asset_name, $info);
            }
        } else {
            $info = $cached;
        }

        return $info;
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

    public function forget($asset_name) {
        $this->laravel_cache->forget($asset_name);
    }

    protected function loadFromXCPD($asset_name) {
        $assets = $this->xcpd_client->get_asset_info(['assets' => [$asset_name]]);
        if (!$assets) {
            // this could be a non-valid asset
            return [];
        }
        $asset_info_data = $assets[0];

        // get the latest issuance to add the transaction hash
        $issuances = $this->xcpd_client->get_issuances([
            'filters' => [
                ['field' => 'asset',  'op' => '==', 'value' => $asset_name,],
                ['field' => 'status', 'op' => '==', 'value' => 'valid',],
            ],
            'order_by' => 'tx_index',
            'order_dir' => 'DESC',
            'limit' => 1,
        ]);
        $issuance = $issuances ? $issuances[0] : [];

        $asset_info_data['status']      = $issuance ? $issuance['status']      : null;
        $asset_info_data['tx_hash']     = $issuance ? $issuance['tx_hash']     : null;
        $asset_info_data['block_index'] = $issuance ? $issuance['block_index'] : null;

        return $asset_info_data;
    }



}
