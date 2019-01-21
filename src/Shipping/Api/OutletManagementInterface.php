<?php
namespace SM\Shipping\Api;

interface OutletManagementInterface
{

    /**
     * Find offices for the customer
     *
     * @param string $postcode
     * @param string $city
     * @return \Vendor\Module\Api\Data\OfficeInterface[]
     */
    public function fetchOutlets();
}