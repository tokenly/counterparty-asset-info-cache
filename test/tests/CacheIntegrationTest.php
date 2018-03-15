<?php

use Mockery as m;
use PHPUnit\Framework\Assert as PHPUnit;
use Tokenly\CounterpartyAssetInfoCache\AssetCache;
use Tokenly\CounterpartyClient\CounterpartyClient;

/*
* 
*/
class CacheIntegrationTest extends TestCase
{

    public function testLiveXCPDClient() {
        if (!getenv('XCPD_CONNECTION_STRING')) { $this->markTestIncomplete(); }

        $asset_info = $this->sampleLTBCoinAssetInfo();
        $cache = $this->app->make(AssetCache::class);

        PHPUnit::assertEquals($asset_info, $cache->get('LTBCOIN'));
        PHPUnit::assertEquals($asset_info, $cache->getFromCache('LTBCOIN'));
    }


    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->bind(CounterpartyClient::class, function() {
            $connection_string = getenv('XCPD_CONNECTION_STRING');
            $rpc_user = getenv('XCPD_RPC_USER');
            $rpc_password = getenv('XCPD_RPC_PASSWORD');
            $client = new CounterpartyClient($connection_string, $rpc_user, $rpc_password);
            return $client;
        });
    }

    protected function sampleLTBCoinAssetInfo() {
        return json_decode($_j=<<<EOT
    {
        "asset": "LTBCOIN",
        "description": "Crypto-Rewards Program http://ltbcoin.com",
        "owner": "3MAmfj1J3jBCf1XGmqXWjne2WstMNkE6T8",
        "divisible": true,
        "supply": 49386391467990000,
        "locked": true,
        "issuer": "3MAmfj1J3jBCf1XGmqXWjne2WstMNkE6T8",
        "asset_longname": null,
        "status": "valid",
        "tx_hash": "0458efae135cee3ef1e245038d061c5c64636e19ed531ad67359c19a2f26e5b0",
        "block_index": 461006
    }
EOT
, true);
    }

}
