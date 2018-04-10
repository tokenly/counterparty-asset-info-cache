<?php

namespace Tokenly\CounterpartyAssetInfoCache;

use Illuminate\Contracts\Cache\Repository;
use \Exception;

/*
* AssetCache
*/
class AssetCache
{
    public function __construct(Repository $laravel_cache, $counterparty_client)
    {
        $this->laravel_cache = $laravel_cache;
        $this->counterparty_client = $counterparty_client;
    }

    public function get($asset_name) {
        $cached = $this->getFromCache($asset_name);
        if ($cached === null) {
            $info = $this->loadFromCounterparty($asset_name);
            if ($info) {
                $this->laravel_cache->forever($asset_name, $info);
            }
        } else {
            $info = $cached;
        }

        return $info;
    }

    public function getMultiple($asset_names) {
        $asset_results_by_name = [];
        $assets_to_load = [];
        foreach($asset_names as $asset_name) {
            $cached = $this->getFromCache($asset_name);
            if ($cached) {
                $asset_results_by_name[$asset_name] = $cached;
                continue;
            }
            $asset_results_by_name[$asset_name] = [];
            $assets_to_load[] = $asset_name;
        }

        if ($assets_to_load) {
            $xcpd_results = $this->loadFromCounterparty($assets_to_load);
            if ($xcpd_results) {
                foreach($xcpd_results as $xcpd_result) {
                    $asset_results_by_name[$xcpd_result['asset']] = $xcpd_result;
                    $this->laravel_cache->forever($xcpd_result['asset'], $xcpd_result);
                }
            }
        }

        return array_values($asset_results_by_name);
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

    protected function loadFromCounterparty($asset_names) {
        $requested_single_asset = false;
        if (!is_array($asset_names)) {
            $asset_names = [$asset_names];
            $requested_single_asset = true;
        }

        $assets = $this->counterparty_client->get_asset_info(['assets' => $asset_names]);
        if (!$assets) {
            // this could be a non-valid asset
            return [];
        }


        $asset_info_datas = [];
        foreach($asset_names as $offset => $asset_name) {
            $asset_info_data = $assets[$offset];

            // get the latest issuance to add the transaction hash
            $issuances = $this->counterparty_client->get_issuances([
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

            $asset_info_datas[] = $asset_info_data;
        }


        return $requested_single_asset ? $asset_info_datas[0] : $asset_info_datas;
    }



}
