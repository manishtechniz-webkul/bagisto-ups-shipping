<?php

namespace Webkul\UpsShipping\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Webkul\Checkout\Facades\Cart;
use Webkul\Core\Repositories\ChannelRepository as Channel;
use Webkul\Checkout\Repositories\CartAddressRepository as CartAddress;
use Webkul\UpsShipping\Repositories\UpsRepository as UpsRepository;

class ShippingMethodHelper
{
    /**
     * Create new controller instances.
     */
    public function __construct(
       protected CartAddress $cartAddress,
       protected Channel $channel,
       protected UpsRepository $upsRepository
    ) {
    }

    /**
     * Shipping address
     * 
     * @param array
     */
    public function servicesWithRates($address)
    {
        $allServices = [];

        $adminData = $this->channel->findByField('code', core()->getCurrentChannelCode())->first();
        
        if (! core()->getConfigData('sales.carriers.ups.ups_active')) {
            return $allServices;
        }

        foreach (Cart::getCart()->items()->get() as $cartProduct) {
            $countryId = core()->getConfigData('sales.shipping.origin.country');
            
            // if admin's product
            if (! isset($cartProduct->marketplace_seller_id)) {
                $address1 = core()->getConfigData('sales.shipping.origin.address1');

                $city = core()->getConfigData('sales.shipping.origin.city');
            }
            
            // create a simple xml object for AccessRequest & RateRequest
            $accessRequesttXML = new \SimpleXMLElement("<AccessRequest></AccessRequest>");

            $rateRequestXML = new \SimpleXMLElement("<RatingServiceSelectionRequest></RatingServiceSelectionRequest>");
            
            // create AccessRequest XML
            $accessRequesttXML->addChild("AccessLicenseNumber", core()->getConfigData('sales.carriers.ups.access_license_key'));

            $accessRequesttXML->addChild("UserId", core()->getConfigData('sales.carriers.ups.user_id'));

            $accessRequesttXML->addChild("Password", core()->getConfigData('sales.carriers.ups.password'));
            
            // create RateRequest XML
            $request = $rateRequestXML->addChild('Request');

            $request->addChild("RequestAction", "Rate");

            $request->addChild("RequestOption", "Shop");
            
            $shipment = $rateRequestXML->addChild('Shipment');

            $shipper = $shipment->addChild('Shipper');
            
            if (! isset($cartProduct->marketplace_seller_id)) {
                $shipper->addChild("Name", $adminData->name);

                $shipper->addChild("ShipperNumber", core()->getConfigData('sales.carriers.ups.shipper_number'));

                $shipperddress = $shipper->addChild('Address');

                $shipperddress->addChild("AddressLine1", $address1 ? $address1 : '');

                $shipperddress->addChild("City", $city ? $city : '');

                $shipperddress->addChild("PostalCode", core()->getConfigData('sales.shipping.origin.zipcode'));

                $shipperddress->addChild("CountryCode", $countryId);

                $shipFrom = $shipment->addChild('ShipFrom');

                $shipFrom->addChild("CompanyName", $adminData->hostname);

                $shipFromAddress = $shipFrom->addChild('Address');

                $shipFromAddress->addChild("AddressLine1", $address1 ? $address1 : '');

                $shipFromAddress->addChild("City", $city ? $city : '');

                $shipFromAddress->addChild("StateProvinceCode", core()->getConfigData('sales.shipping.origin.state'));

                $shipFromAddress->addChild("PostalCode", core()->getConfigData('sales.shipping.origin.zipcode'));

                $shipFromAddress->addChild("CountryCode", $countryId);
            }

            $shipTo = $shipment->addChild('ShipTo');

            $shipTo->addChild("CompanyName", $address->first_name.' '.$address->last_name);
            
            $shipToAddress = $shipTo->addChild('Address');

            $shipToAddress->addChild("AddressLine1", $address->address1);

            $shipToAddress->addChild("City", $address->city);
            
            if ($address->country == 'PR') {
                $shipToAddress->addChild("PostalCode", '00'.$address->postcode);
            } else {
                $shipToAddress->addChild("PostalCode", $address->postcode);
            }
            
            $shipToAddress->addChild("CountryCode", $address->country);

            $package = $shipment->addChild('Package');

            $packageType = $package->addChild('PackagingType');

            $packageType->addChild("Code", core()->getConfigData('sales.carriers.ups.container'));
            
            $packageWeight = $package->addChild('PackageWeight');

            $unitOfMeasurement = $packageWeight->addChild('UnitOfMeasurement');

            $unitOfMeasurement->addChild("Code", "LBS");

            $packageWeight->addChild("Weight", $this->getWeight($cartProduct->weight));

            try {
                $ch = curl_init();

                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                curl_setopt($ch, CURLOPT_POST, true);

                curl_setopt($ch, CURLOPT_URL, 'https://onlinetools.ups.com/ups.app/xml/Rate');

                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-type: text/xml",
                    "Accept: text/xml",
                    "Cache-Control: no-cache",
                    "Pragma: no-cache",
                ]);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $accessRequesttXML->asXML().$rateRequestXML->asXML());
                
                $response = curl_exec($ch);
                
                curl_close($ch);

                $upsServices = json_decode(json_encode(simplexml_load_string($response)));

                $allServices = $this->requestResponse($upsServices, $cartProduct);
            } catch(\Exception $e) {
                return $allServices = [];
            }
        }
        
        return $allServices;
    }

    /**
     * Get service name via service code
     *
     * @param string $serviceCode
     */
    protected function getServiceName($serviceCode): string
    {
        $mapServices = $this->upsRepository->getServices();

        if (in_array($serviceCode, array_keys($mapServices))) {
            return $mapServices[$serviceCode];
        }

        return $serviceCode;
    }

    /**
     * convert current weight unit to LBS
     *
     * @param string $weight
     **/
    public function getWeight($weight)
    {
        $convertedWeight = '';

        if (strtoupper(core()->getConfigData('general.general.locale_options.weight_unit')) == 'LBS') 
        {
            if (strtoupper(core()->getConfigData('sales.carriers.ups.weight_unit')) == 'LBS') 
            {
                $convertedWeight = $weight;
            } else {
                //kgs to lbs
                $convertedWeight = $weight/0.45359237;
            }
        } else {
            $convertedWeight = $weight/0.45359237;
        }

        return $convertedWeight;
    }

    /**
     * Get the current error
     *
     * @param array
     **/
    public function getErrorLog($errors) 
    {
        foreach($errors->Response->Error as $errorLog)
        {
            $exception[] = $errorLog->ErrorDescription;
        }
        
        $status = $errors->Response->ResponseStatusDescription;
        
        if (gettype($errors->Response->Error) !== 'array') 
        {
            $status = $errors->Response->Error->ErrorSeverity;

            $exception[] = $errors->Response->Error->ErrorDescription;
        }

        $shippingLog = new Logger('shipping');

        $shippingLog->pushHandler(new StreamHandler(storage_path('logs/ups.log')), Logger::INFO);
        
        $shippingLog->info('shipping', [
            'status'      => $status,
            'description' => $exception,
        ]);

        return true;
    }

    /**
     * Optimize request response
     * 
     * @param array $upsServices
     * @param array $cartProduct
     */
    public function requestResponse($upsServices, $cartProduct): array
    {
        if (isset($upsServices->Response->ResponseStatusDescription)
            && $upsServices->Response->ResponseStatusDescription == 'Success'
        ) {
            if (isset($upsServices->RatedShipment)) {
                return $this->optimizeUpsServiceResouces($upsServices, $cartProduct);
            }
        } else {
            $this->getErrorLog($upsServices);

            return $services = [];
        }
    }

    /**
     * Optimize ups services resources
     * 
     * @param array $upsServices
     * @param array $cartProduct
     */
    public function optimizeUpsServiceResouces($upsServices, $cartProduct)
    {
        foreach($upsServices->RatedShipment as $services) {
            $serviceCode = $services->Service->Code;

            if ($this->upsRepository->validateAllowedMethods($serviceCode, explode(",", core()->getConfigData('sales.carriers.ups.services')) ?? [])) {
                $cartProductServices[$this->getServiceName($serviceCode)][] = [
                    'classId'               => $serviceCode,
                    'rate'                  => $services->RatedPackage->TotalCharges->MonetaryValue,
                    'currency'              => $services->RatedPackage->TotalCharges->CurrencyCode,
                    'weight'                => $services->BillingWeight->Weight,
                    'weightUnit'            => $services->BillingWeight->UnitOfMeasurement->Code,
                    'marketplace_seller_id' => $cartProduct->marketplace_seller_id ?? 0,
                    'itemQuantity'          => $cartProduct->quantity,
                ];
            }
        }

        return $cartProductServices ?? [];
    }

    /**
     * Generate access token.
     * Todo:: Verify working
     * 
     * @return array|Exception
     */
    public function generateAccessToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic " . base64_encode("demowebkul7:Webkul12#"),
            ],

            CURLOPT_POSTFIELDS     => "grant_type=authorization_code&code=string&redirect_uri=string",
            CURLOPT_URL            => $this->upsRepository->getShippingModeUrl('v1/oauth/token', $subDirectory = 'security'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "POST",
        ]);

        $result = json_decode(curl_exec($curl));

        $info = curl_getinfo($curl);

        curl_close($curl);

        return match($info['http_code']) {
            201             => $result,
            400,401,403,429 => throw new \Exception($result?->response?->errors[0]?->message ?? trans('ups::app.admin.api-error.generate-token-failed')),
            default         => throw new \Exception(trans('ups::app.admin.api-error.generate-token-failed')),
        };
    }

    /**
     * Call ups api
     * Todo:: Verify working
     * 
     * @return array|Exception
     */
    public function callApi(string $permalink, array $data = [], string $method = "GET", array $headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge([
                "Authorization: Bearer ".$this->generateAccessToken()['accessToken'],
                "Content-Type: application/json",
            ], $headers),

            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_URL            => $this->upsRepository->getShippingModeUrl($permalink),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        $result = json_decode(curl_exec($curl));

        $info = curl_getinfo($curl);

        curl_close($curl);

        return match($info['http_code']) {
            201             => $result,
            400,401,403,429 => throw new \Exception($result?->response?->errors[0]?->message ?? trans('ups::app.admin.api-error.encountered-error')),
            default         => throw new \Exception(trans('ups::app.admin.api-error.encountered-error')),
        };
    }
}