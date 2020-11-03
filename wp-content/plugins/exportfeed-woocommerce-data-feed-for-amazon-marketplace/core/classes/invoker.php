<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
// invokes the amazon request for feed submission to retrieve informations
class CPF_Invoker
{
    public function __construct()
    {
//        define ('SUBMITED_DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
    }

    public function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request)
    {
        try {
            $response = $service->submitFeed($request);

            if ($response->isSetSubmitFeedResult()) {
                $submitFeedResult = $response->getSubmitFeedResult();

                if ($submitFeedResult->isSetFeedSubmissionInfo()) {
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();

                    if ($feedSubmissionInfo->isSetFeedSubmissionId()) {

                        $result                       = new stdClass();
                        $result->FeedSubmissionId     = $feedSubmissionInfo->getFeedSubmissionId();
                        $result->FeedProcessingStatus = $feedSubmissionInfo->getFeedProcessingStatus();
                        $result->FeedType             = $feedSubmissionInfo->getFeedType();
                        $result->SubmittedDate        = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                        $result->success              = true;
                        return $result;
                    }
                }
            }

        } catch (MarketplaceWebService_Exception $ex) {
            $error               = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            $error->success      = false;
            return $error;
        }

        $result          = new stdClass();
        $result->success = false;
        return $result;
    }

    public function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service_, $request, $handle)
    {
        $result = new stdClass();

        try {
            $response = $service->getFeedSubmissionResult($request);
            if ($response->isSetGetFeedSubmissionResultResult()) {
                rewind($handle);
                $result->content = stream_get_contents($handle);
                $result->success = true;
            }
        } catch (MarketplaceWebService_Exception $ex) {
            $result->ErrorMessage = $ex->getMessage();
            $result->ErrorCode    = $ex->getErrorCode();
            $result->StatusCode   = $ex->getStatusCode();
            $result->success      = false;
        }
        return $result;
    }

    public function invokeListMarketplaceParticipations(MarketplaceWebServiceSellers_Interface $service, $request)
    {
        try {
            $response = $service->ListMarketplaceParticipations($request);
            if ($response->isSetListMarketplaceParticipationsResult()) {
                $listMarketplaceParticipationsResult = $response->getListMarketplaceParticipationsResult();

                // process marketplaces
                $listMarketplaces = $listMarketplaceParticipationsResult->getListMarketplaces();
                $marketplaceList  = $listMarketplaces->getMarketplace();
                foreach ($marketplaceList as $marketplace) {

                    $key = $marketplace->getMarketplaceId();

                    $allowed_markets[$key]                      = new stdClass();
                    $allowed_markets[$key]->MarketplaceId       = $marketplace->getMarketplaceId();
                    $allowed_markets[$key]->Name                = $marketplace->getName();
                    $allowed_markets[$key]->DefaultLanguageCode = $marketplace->getDefaultLanguageCode();
                    $allowed_markets[$key]->DefaultCountryCode  = $marketplace->getDefaultCountryCode();
                    $allowed_markets[$key]->DefaultCurrencyCode = $marketplace->getDefaultCurrencyCode();
                    $allowed_markets[$key]->DomainName          = $marketplace->getDomainName();
                }

                // process participations
                $listParticipations = $listMarketplaceParticipationsResult->getListParticipations();
                $participationList  = $listParticipations->getParticipation();
                foreach ($participationList as $participation) {

                    $key                                               = $marketplace->getMarketplaceId();
                    $allowed_markets[$key]->MarketplaceId              = $participation->getMarketplaceId();
                    $allowed_markets[$key]->SellerId                   = $participation->getSellerId();
                    $allowed_markets[$key]->HasSellerSuspendedListings = $participation->getHasSellerSuspendedListings();
                }
                // echo "<pre>allowed_markets: ";print_r($allowed_markets);echo"</pre>";#die();

                $result                  = new stdClass();
                $result->allowed_markets = $allowed_markets;
                $result->success         = true;
                return $result;
            }

        } catch (MarketplaceWebServiceSellers_Exception $ex) {
            $error               = new stdClass();
            $error->success      = false;
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }
        $result          = new stdClass();
        $result->success = false;
        return $result;
    }
}
