<?php

class WPLA_AmazonHelper {

    // get Developer ID for given marketplace
    static function getDevIdForMarketId( $market_id ) {

        $market = WPLA_AmazonMarket::getMarket( $market_id );

        switch ( $market->code ) {

            case 'US':
            case 'CA':
            case 'BR':
            case 'MX':
                $developer_id = '4566-1167-5522';
                break;
            
            case 'UK':
            case 'DE':
            case 'FR':
            case 'IT':
            case 'ES':
                $developer_id = '1464-3546-1406';
                break;
            
            case 'AU':
                $developer_id = '1498-4611-2787';
                break;
                            
        }

        return $developer_id;
    }

} // class WPLA_AmazonHelper

