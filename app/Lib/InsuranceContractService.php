<?php

namespace App\Lib;

use App\Lib\InsuranceServices\VBI\VBIService;
use App\Models\InsuranceContract;

class InsuranceContractService
{
    /**
     * @var InsuranceContractServiceInterface
     */
    protected $service;

    public function __construct()
    {
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @param InsuranceContract $contract
     * @param bool $paymentStatus
     * @return bool
     */
    public function provideContract(InsuranceContract $contract, $paymentStatus = false)
    {
        if (!empty($this->service)) {
//        $service = new VBIService();
            return $this->service->provide($contract, $paymentStatus);
        } else {
            return false;
        }
    }

    /**
     * Check product can provide contract by online service or not.
     *
     * @param $productCode
     * @return bool|VBIService
     */
    public function hasOnlineProvide($productCode)
    {
        // Check via VBIService
        $service = new VBIService();
        if ($service->hasService($productCode)) {
            return $service;
        }

        return false;
    }

    /**
     * @param $contract_id
     * @param $beneficiary_id
     * @param string $fileType
     * @return bool
     */
    public function getCertificateFile($contract_id, $beneficiary_id, $fileType = '')
    {
        $acceptedFileType = ['contract', 'beneficiary'];

        if (!empty($this->service)) {
            return $this->service->getCertificateFile($contract_id, $beneficiary_id, $fileType);
        } else {
            return false;
        }
    }

    public function getService($serviceName)
    {
        switch ($serviceName) {
            case 'VBI':
                return new VBIService();
                break;
        }

        return false;
    }

    public function getName()
    {
        return $this->service->getName();
    }
}