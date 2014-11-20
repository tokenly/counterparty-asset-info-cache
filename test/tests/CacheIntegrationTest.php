<?php

use \Mockery as m;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class CacheIntegrationTest extends TestCase
{

    public function testLiveXCPDClient() {
        if (!getenv('XCPD_CONNECTION_STRING')) { $this->markTestIncomplete(); }

        $asset_info = $this->sampleLTBCoinAssetInfo();
        $cache = $this->app->make('Tokenly\CounterpartyAssetInfoCache\Cache');

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
        $app->bind('Tokenly\XCPDClient\Client', function() {
            $connection_string = getenv('XCPD_CONNECTION_STRING');
            $rpc_user = getenv('XCPD_RPC_USER');
            $rpc_password = getenv('XCPD_RPC_PASSWORD');
            $client = new \Tokenly\XCPDClient\Client($connection_string, $rpc_user, $rpc_password);
            return $client;
        });
    }

    protected function sampleLTBCoinAssetInfo() {
        return json_decode($_j=<<<EOT
    {
        "asset": "LTBCOIN",
        "callable": false,
        "call_date": 0,
        "description": "Crypto-Rewards Program http://ltbcoin.com",
        "owner": "1Hso4cqKAyx9bsan8b5nbPqMTNNce8ZDto",
        "call_price": 0,
        "divisible": true,
        "supply": 17731189327990000,
        "locked": false,
        "issuer": "1Hso4cqKAyx9bsan8b5nbPqMTNNce8ZDto"
    }
EOT
, true);
    }

}
