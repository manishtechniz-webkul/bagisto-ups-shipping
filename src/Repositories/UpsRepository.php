<?php

namespace Webkul\UpsShipping\Repositories;

use Webkul\Sales\Repositories\OrderRepository;

class UpsRepository
{
    /**
     * Get only allowed Services
     * 
     * @param $allowedServices
     * @return $secvices
     */
    public function validateAllowedMethods($service, $allowedServices)
    {
        if (in_array($service, $allowedServices)) {
            return true;
        }

        return false;
    }

    /**
     * Get shipping address
     * 
     * @param array $addresses
     * @return array
     */
    public function getShippingAddress($addresses)
    {
        foreach ($addresses as $address) {
            if ($address['address_type'] == 'order_shipping') {
                return $address;
            }
        }
    }
    
    /**
     * Get services
     * 
     * @return array
     */
    public function getServices()
    {
        return [
            '01' => 'Next Day Air',
            '02' => '2nd Day Air',
            '03' => 'Ups Ground',
            '07' => 'Ups Worldwide Express',
            '08' => 'Ups Worldwide Expedited',
            '11' => 'Standard',
            '12' => '3 Day Select',
            '13' => 'Next Day Air Saver',
            '14' => 'Next Day Air Early A.M.',
            '54' => 'Ups Worldwide Express Plus',
            '59' => '2nd Day Air A.M.',
            '65' => 'UPS World Wide Saver',
            '82' => 'Today Standard',
            '83' => 'Today Dedicated Courier',
            '84' => 'Today Intercity',
            '85' => 'Today Express',
            '86' => 'Today Express Saver',
            '03' => 'Ups Ground',
        ];
    }
}