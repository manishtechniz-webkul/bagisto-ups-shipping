<?php

namespace Webkul\UpsShipping\Listeners;

use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderAddressRepository;
use Webkul\UpsShipping\Repositories\UpsRepository;
use Webkul\UpsShipping\Helpers\ShippingMethodHelper;
use Webkul\UpsShipping\Carriers\Ups;

class Shipment
{
    /**
     * Create new repository instances.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository,
        protected OrderAddressRepository $orderAddressRepository,
        protected ShippingMethodHelper $shippingMethodHelper,
        protected UpsRepository $upsRepository,
        protected Ups $ups
    ) {
    }

    /**
     * Before shippment is created.
     * Todo:: Verify working
     * 
     * @return void
     */
    public function beforeCreated($data)
    {   
        $totalWeight = 0;

        $order = app(OrderRepository::class)->find(request('order_id'));

        if (! is_integer(stripos($order->shipping_method, $this->ups->getMethod()))) {
            return;
        }

        $shippingAddress = $this->upsRepository->getShippingAddress($order->addresses);

        foreach ($data['items'] as $itemId => $inventorySource) {
            $qty = $inventorySource[$data['source']];

            $orderItem = $this->orderItemRepository->find($itemId);

            $totalWeight += $orderItem->weight * $qty;
        }
       
        $shipmentRequest = [
            "Request" => [
                "SubVersion"    => "2403",
                "RequestOption" => "nonvalidate",
            ],

            "Shipment" => [
                "Shipper" => [
                    "Name"                    => core()->getConfigData('sales.shipping.origin.store_name'),
                    "AttentionName"           => core()->getConfigData('sales.shipping.origin.store_name'),
                    "TaxIdentificationNumber" => core()->getConfigData('sales.shipping.origin.vat_number'),
                    "ShipperNumber"           => core()->getConfigData('sales.shipping.origin.contact'),

                    "Phone" => [
                        "Number" => core()->getConfigData('sales.shipping.origin.contact'),
                    ],

                    "Address" => [
                        "AddressLine" => [
                            core()->getConfigData('sales.shipping.origin.address'),
                        ],

                        "City"              => core()->getConfigData('sales.shipping.origin.city'),
                        "CountryCode"       => core()->getConfigData('sales.shipping.origin.country'),
                        "StateProvinceCode" => core()->getConfigData('sales.shipping.origin.state'),
                        "PostalCode"        => core()->getConfigData('sales.shipping.origin.zipcode'),
                    ],
                ],

                "ShipTo" => [
                    "Name"          => $shippingAddress['first_name'].' '.$shippingAddress['last_name'],
                    "AttentionName" => $shippingAddress['first_name'].' '.$shippingAddress['last_name'],

                    "Phone" => [
                        "Number" => $shippingAddress['phone'],
                    ],

                    "Address" => [
                        "AddressLine" => [
                            $shippingAddress['address'],
                        ],

                        "City"              => $shippingAddress['city'],
                        "StateProvinceCode" => $shippingAddress['state'],
                        "PostalCode"        => $shippingAddress['postcode'],
                        "CountryCode"       => $shippingAddress['country'],
                    ],
                ],

                "ShipFrom" => [
                    "Name"          => core()->getConfigData('sales.shipping.origin.store_name'),
                    "AttentionName" => core()->getConfigData('sales.shipping.origin.store_name'),

                    "Phone" => [
                        "Number" => core()->getConfigData('sales.shipping.origin.contact'),
                    ],
                   
                  "Address" => [
                        "AddressLine" => [
                            core()->getConfigData('sales.shipping.origin.address'),
                        ],

                        "City"              => core()->getConfigData('sales.shipping.origin.city'),
                        "CountryCode"       => core()->getConfigData('sales.shipping.origin.country'),
                        "StateProvinceCode" => core()->getConfigData('sales.shipping.origin.state'),
                        "PostalCode"        => core()->getConfigData('sales.shipping.origin.zipcode'),
                    ]
                ],

                "PaymentInformation" => [
                    "ShipmentCharge" => [
                        "Type" => "01",

                        "BillShipper" => [
                            "AccountNumber" => core()->getConfigData('sales.carriers.ups.shipper_number'),
                        ],
                    ],
                ],

                "Service" => [
                    "Code"        => array_flip($this->upsRepository->getServices())[str_replace('mpups_', '', $order->shipping_method)],
                    "Description" => $order->shipping_description,
                ],

                "Package" => [
                    /**
                     * Todo:: Get package code form order
                     */
                    "Packaging" => [
                        "Code" => core()->getConfigData('sales.carriers.ups.container'),
                    ],

                    /**
                     * Todo:: Discuss with mentor.
                     */
                    "Dimensions" => [
                        "UnitOfMeasurement" => [
                            "Code"        => "IN",
                            "Description" => "Inches",
                        ],

                        "Length" => "10",
                        "Width"  => "30",
                        "Height" => "45",
                    ],

                    "PackageWeight" => [
                        "UnitOfMeasurement" => [
                            "Code"        => "LBS",
                            "Description" => "Pounds",
                        ],

                        "Weight" => $totalWeight,
                    ],
                ],
            ],
        ];

        try {
            $this->shippingMethodHelper->callApi('shipments/v1/ship', $shipmentRequest, $method = 'POST', $headers = [
                "transId: ".$data['track_number'],
                "transactionSrc: website",
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}