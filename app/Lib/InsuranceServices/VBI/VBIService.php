<?php

namespace App\Lib\InsuranceServices\VBI;

use App\Lib\InsuranceContractServiceInterface;
use App\Models\InsuranceContract;
use App\Lib\InsuranceServices\VBI\V1\VBIDriver as VBIDriverV1;
use App\Lib\InsuranceServices\VBI\V2\VBIDriver as VBIDriverV2;

class VBIService implements InsuranceContractServiceInterface
{
    protected $serviceDriver;
    protected $mode;
    protected $version;
    protected $serviceName;

    /**
     * VBIService constructor.
     */
    public function __construct()
    {
        $this->mode = env('INSURANCE_SERVICE_MODE', 'production');
        $this->version = (int)env('VBI_API_VERSION', '2');
        $this->serviceName = 'VBI';

        switch ($this->version) {
            case 1:
                $this->serviceDriver = new VBIDriverV1();
                break;
            case 2:
                $this->serviceDriver = new VBIDriverV2();
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Provide insurance contract
     *
     * @param InsuranceContract $contract
     * @param string $paymentStatus
     * @return array
     */
    public function provide(InsuranceContract $contract, $paymentStatus = false, $getSendData = false)
    {
        return $this->serviceDriver->provide($contract, $paymentStatus, $getSendData);
    }

    public function activeCertificate(InsuranceContract $contract)
    {
        return $this->serviceDriver->activeCertificate($contract);
    }

    /**
     * @param $contract_id
     * @param $beneficiary_id
     * @param $file_type
     * @return array
     */
    public function getCertificateFile($contract_id, $beneficiary_id, $file_type, $type_id = 0)
    {
        // Convert file type
        switch ($file_type) {
            case 'contract':
                $file_type = 'HD';
                break;
            case 'beneficiary':
                $file_type = 'GCN';
                break;
            default:
                $file_type = 'GCN';
                break;
        }
        return $this->serviceDriver->getCertificateFile($contract_id, $beneficiary_id, $file_type, $type_id);
    }

    /**
     * @param $id_hd
     * @param $id_dt
     * @return mixed
     */
    public function getFile($id_hd, $id_dt)
    {
        return $this->serviceDriver->getFile($id_hd, $id_dt);
    }

    /**
     * @param $id_hd
     * @param $id_dt
     * @param $contract_id
     * @param string $type
     * @return bool
     */
    public function saveFile($id_hd, $id_dt, $contract_id, $type = 'beneficiary')
    {
        return $this->serviceDriver->saveFile($id_hd, $id_dt, $contract_id, $type);
    }

    public function hasService($productCode)
    {
        return $this->serviceDriver->checkVBI($productCode);
    }

    public function getName()
    {
        return $this->serviceName;
    }
}
