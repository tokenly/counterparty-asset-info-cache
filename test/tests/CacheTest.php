<?php

use Mockery as m;
use PHPUnit\Framework\Assert as PHPUnit;
use Tokenly\CounterpartyAssetInfoCache\AssetCache;
use Tokenly\CounterpartyClient\CounterpartyClient;

/*
* 
*/
class CacheTest extends TestCase
{


    public function testSetCache() {
        $asset_info = $this->sampleLTBCoinAssetInfo();

        $cache = $this->app->make(AssetCache::class);
        $cache->set('LTBCOIN', $asset_info);

        PHPUnit::assertEquals($asset_info, $cache->getFromCache('LTBCOIN'));
    }

    public function testIsDivisible() {
        $cache = $this->app->make(AssetCache::class);

        $cache->set('LTBCOIN', $this->sampleLTBCoinAssetInfo());
        PHPUnit::assertTrue($cache->isDivisible('LTBCOIN'));

        $cache->set('EARLY', $this->sampleEarlyAssetInfo());
        PHPUnit::assertFalse($cache->isDivisible('EARLY'));
    }

    public function testXCPDClient() {
        $asset_info = $this->sampleLTBCoinAssetInfo();
        $cache = $this->app->make(AssetCache::class);
        $expected_combined_info = array_merge($asset_info, $this->sampleLTBCoinIssuancesInfo());
        PHPUnit::assertEquals($expected_combined_info, $cache->get('LTBCOIN'));
        PHPUnit::assertEquals($expected_combined_info, $cache->getFromCache('LTBCOIN'));
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
            $client = m::mock(CounterpartyClient::class);
            $client->shouldReceive('get_asset_info')->with(['assets' => ['LTBCOIN']])->andReturn([$this->sampleLTBCoinAssetInfo()]);

            $expected_issuances_query = [
                'filters' => [
                    ['field' => 'asset',  'op' => '==', 'value' => 'LTBCOIN',],
                    ['field' => 'status', 'op' => '==', 'value' => 'valid',],
                ],
                'order_by' => 'tx_index',
                'order_dir' => 'DESC',
                'limit' => 1,
            ];

            $client->shouldReceive('get_issuances')->with($expected_issuances_query)->andReturn([$this->sampleLTBCoinIssuancesInfo()]);
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

    protected function sampleLTBCoinIssuancesInfo() {
        return json_decode($_j=<<<EOT
    {
        "status": "valid",
        "tx_hash": "0x0f00",
        "block_index": 1000000
    }
EOT
, true);
    }

    protected function sampleEarlyAssetInfo() {
        return json_decode($_j=<<<EOT
    {
        "asset": "EARLY",
        "callable": false,
        "call_date": 0,
        "description": "http://letstalkbitcoin.com/blog/post/tcv",
        "owner": "1MCEtBB5X4ercRsvq2GmgysZ9ZDsqj8Xh7",
        "call_price": 0,
        "divisible": false,
        "supply": 2401,
        "locked": true,
        "issuer": "1MCEtBB5X4ercRsvq2GmgysZ9ZDsqj8Xh7"
    }
EOT
, true);
    }

}
