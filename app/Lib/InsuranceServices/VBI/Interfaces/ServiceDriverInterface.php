<?php
/**
 * Created by Phong Bui.
 * Date: 11/9/17
 * Time: 09:45
 */

namespace App\Lib\InsuranceServices\VBI\Interfaces;


use App\Models\InsuranceContract;

interface ServiceDriverInterface
{
    public function sendRequest($url, $data, $method, $type);

    public function provide(InsuranceContract $contract, $paymentStatus = false);

    public function activeCertificate(InsuranceContract $contract);

    public function previewContractFile(InsuranceContract $contract);

    public function getCertificateFile($contract_id, $beneficiary_id, $fileType);

    public function getFile($id_hd, $id_dt, $fileType);
}