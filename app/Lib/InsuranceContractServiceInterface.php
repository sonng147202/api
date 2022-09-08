<?php
namespace App\Lib;

use App\Models\InsuranceContract;

interface InsuranceContractServiceInterface
{
    public function provide(InsuranceContract $contract, $paymentStatus);

    public function activeCertificate(InsuranceContract $contract);

    public function getCertificateFile($contract_id, $beneficiary_id, $fileType);

    public function hasService($productCode);

    public function getName();
}