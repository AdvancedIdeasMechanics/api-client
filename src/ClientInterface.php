<?php
declare(strict_types=1);

namespace Advancedideasmechanics\Api;

interface ClientInterface
{
    public function getAccessToken();
    public function makeApiRequest($endpoint, $jsonBody, $method = "GET", $additionalHeaders = []);

}