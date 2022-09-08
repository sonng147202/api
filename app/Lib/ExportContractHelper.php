<?php
/**
 * Created by PhpStorm.
 * User: khoinx
 * Date: 9/14/17
 * Time: 10:03 AM
 */

namespace Modules\Insurance\Lib;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Lib\ExcelHelper;
use Modules\Insurance\Models\InsuranceContractFile;

class ExportContractHelper
{
    /**
     * @param $contract
     * @param $listBeneficiaries
     * @param $code
     * @return bool
     */
    public function export($contract, $listBeneficiaries, $code)
    {
        // Check template file by insurance type id
        $templateFile = storage_path('exports/indon_'. $contract->type_id .'.xls');
        if (file_exists($templateFile)) {
            try {
                $excelHelper = new ExcelHelper();
                $data = array(
                    'D5' => !empty($contract->customer->name) ? $contract->customer->name : '',
                    'P5' => !empty($contract->customer->phone_number) ? $contract->customer->phone_number : '',
                    'D7' => !empty($contract->customer->address) ?  $contract->customer->address : '',
                    'P6' => !empty($contract->customer->email) ? $contract->customer->email : '',
                    'P3' => $code,
                    'P7' => date('d/m/Y', strtotime($contract->start_time)),
                    'P8' => date('d/m/Y', strtotime($contract->end_time)),
                    //'F17'=>'Không',
                    //'R17'=>'Không',
                    'D6' => !empty($contract->product->name) ? $contract->product->name : '',
                    'D8' => 'Tham gia mới',
                );
                $position = 12;
                $maxRowList = 19;
                if(empty($listBeneficiaries)){
                    $listBeneficiaries = array();
                }
                foreach ($listBeneficiaries as $item){
                    if (isset($item['value'])) {
                        $item = json_decode($item['value'], true);
                    }
                    $data["A{$position}"] = isset($item['name']) ? $item['name'] : '';
                    $data["F{$position}"] = isset($item['identity_card']) ? $item['identity_card'] : '';
                    $data["J{$position}"] = isset($item['date_of_birth']) ? $item['date_of_birth'] :'';
                    $data["M{$position}"] = '';
                    $data["T{$position}"] = isset($item['sex']) ? $item['sex'] : '';
                    $position++;
                }

                $numRowDelete = $maxRowList - count($listBeneficiaries);
                $data['T' . ($position + 5)] = number_format($contract->gross_amount, 0);
                $result = $excelHelper->writeExcel($data,'exports/indon.xls', "{$code}.xls", $position, $numRowDelete);
                $insert_file = new InsuranceContractFile();
                $insert_file->contract_id = $contract->id;
                $insert_file->file_name = $code . ".xls";
                $insert_file->file_path = $result;
                $insert_file->type = InsuranceContractFile::TYPE_CERTIFICATE;
                $insert_file->save();

                // Update contract code
                $contract->code = $code;
                $contract->status = 1;
                $contract->save();

                return true;
            } catch (\Exception $ex) {
                Log::error($ex->getMessage() . '. ' . $ex->getFile() . ':' . $ex->getLine());
                return false;
            }
        } else {
            $fileExt = 'xls';
            // Auto create file
            Excel::create($code, function ($excel) use ($listBeneficiaries) {
                $excel->setTitle('Giấy chứng nhận bảo hiểm');
                $excel->sheet('GCN', function ($sheet) use ($listBeneficiaries) {
                    $sheet->fromArray($listBeneficiaries);
                });
            })->store($fileExt, storage_path('app/contract/certificate'));

            $fileName = $code . '.' . $fileExt;
            Storage::disk('contract')->put($fileName, fopen(storage_path('app/contract/certificate/' . $fileName), 'r'));

            @unlink(storage_path('app/contract/certificate/' . $fileName));

            $result = Storage::disk('contract')->url($fileName);

            $insert_file = new InsuranceContractFile();
            $insert_file->contract_id = $contract->id;
            $insert_file->file_name = $fileName;
            $insert_file->file_path = $result;
            $insert_file->type = InsuranceContractFile::TYPE_CERTIFICATE;
            $insert_file->save();

            // Update contract code
            $contract->code = $code;
            $contract->status = 1;
            $contract->save();

            return true;
        }
    }
}