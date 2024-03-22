<?php

namespace Webkul\UpsShipping\Repositories;

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
}