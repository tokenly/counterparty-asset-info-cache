<?php

use \Mockery as m;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class CacheTest extends TestCase
{


    public function testSetCache() {
        $asset_info = $this->sampleLTBCoinAssetInfo();

        $cache = $this->app->make('Tokenly\CounterpartyAssetInfoCache\Cache');
        $cache->set('LTBCOIN', $asset_info);

        PHPUnit::assertEquals($asset_info, $cache->getFromCache('LTBCOIN'));
    }

    public function testIsDivisible() {
        $cache = $this->app->make('Tokenly\CounterpartyAssetInfoCache\Cache');

        $cache->set('LTBCOIN', $this->sampleLTBCoinAssetInfo());
        PHPUnit::assertTrue($cache->isDivisible('LTBCOIN'));

        $cache->set('ETHER', $this->sampleEtherAssetInfo());
        PHPUnit::assertFalse($cache->isDivisible('ETHER'));
    }

    public function testXCPDClient() {
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
            $client = m::mock('Tokenly\XCPDClient\Client');
            $client->shouldReceive('get_asset_info')->with(['assets' => ['LTBCOIN']])->andReturn([$this->sampleLTBCoinAssetInfo()]);
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

    protected function sampleEtherAssetInfo() {
        return json_decode($_j=<<<EOT
    {
        "asset": "ETHER",
        "callable": false,
        "call_date": 0,
        "description": "",
        "owner": "1LMPuuw781iW3NJz2GYAjqg1m14s5zQ4Yy",
        "call_price": 0,
        "divisible": false,
        "supply": 9223372036854775807,
        "locked": false,
        "issuer": "1LMPuuw781iW3NJz2GYAjqg1m14s5zQ4Yy"
    }
EOT
, true);
    }

}
