<?php

namespace Convo\Http;

use function Convo\view;
class ServicesController extends \Convo\Http\Controller
{
    /**
     * Display single service
     *
     * @return void
     */
    public static function single()
    {
        $serviceId = sanitize_text_field($_GET['id']);
        $service = wp_remote_get(home_url() . '/wp-json/convo/v1/services/' . $serviceId);
        $service = \json_decode($service['body']);
        view('services/single', ['service' => $service->data]);
    }
}
