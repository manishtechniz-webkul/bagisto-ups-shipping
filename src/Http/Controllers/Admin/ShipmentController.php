<?php

namespace Webkul\UpsShipping\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Sales\Repositories\ShipmentRepository;
use Webkul\UpsShipping\Helpers\ShippingMethodHelper;

class ShipmentController extends Controller
{
    /**
     * Create new controller instances.
     */
    public function __construct(
        protected ShipmentRepository $shipmentRepository,
        protected ShippingMethodHelper $shippingMethodHelper
    ) {
    }

    public function orderTracking()
    {
        $this->shipmentRepository->where('track_number', request('tracking_id'))->firstOrFail();

        $query = [
            "locale"           => "en_US",
            "returnSignature"  => "false",
            "returnMilestones" => "false",
            "returnPOD"        => "false",
        ];
        try {
            $data = $this->shippingMethodHelper->callApi('track/v1/details/'.request('tracking_id').'?'.http_build_query($query), $data = [], $method = 'GET', $headers = [
                "transId: ".request('tracking_id'),
                "transactionSrc: website",
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'result' => $data,
        ]);
    }
}   