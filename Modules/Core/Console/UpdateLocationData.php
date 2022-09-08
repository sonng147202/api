<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Insurance\Models\District;
use Modules\Insurance\Models\Province;
use Modules\Insurance\Models\Ward;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateLocationData extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'location:update-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update list locations from excel file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Read excel data file
        $file = Module::getModulePath('Core') . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'Dia-Gioi-Hanh-Chinh-VietNam.xls';
        if (file_exists($file)) {
            // Get data
            $data = Excel::load($file)->toArray();
            if ($data) {
                $provinces = $data[0];
                $districts = $data[1];
                $wards = $data[2];

                // Order districts, wards by id
                $data = [];
                foreach ($districts as $district) {
                    if (!isset($data[$district['ma_tinhtp']])) {
                        $data[$district['ma_tinhtp']] = [];
                    }
                    $data[$district['ma_tinhtp']][] = [
                        'name' => $district['ten'],
                        'id' => $district['ma_huyentpthi_xa']
                    ];
                }
                $districts = $data;

                $data = [];
                foreach ($wards as $ward) {
                    if (!isset($data[$ward['ma_huyen']])) {
                        $data[$ward['ma_huyen']] = [];
                    }
                    $data[$ward['ma_huyen']][] = [
                        'name' => $ward['ten'],
                        'id' => $ward['ma_xaphuongthi_tran']
                    ];
                }
                $wards = $data;

                // Insert data
                foreach ($provinces as $province) {
                    $provinceInfo = Province::create([
                        'name' => $province['ten'],
                        'name_without_accent' => str_slug($province['ten'])
                    ]);

                    if ($provinceInfo) {
                        // Insert district
                        if (!empty($districts[$province['ma_tinh']])) {
                            foreach ($districts[$province['ma_tinh']] as $district) {
                                $districtInfo = District::create([
                                    'province_id' => $provinceInfo->id,
                                    'name' => $district['name'],
                                    'name_without_accent' => str_slug($district['name'])
                                ]);

                                if ($districtInfo) {
                                    // Insert ward
                                    if (!empty($wards[$district['id']])) {
                                        foreach ($wards[$district['id']] as $ward) {
                                            $wardInfo = Ward::create([
                                                'district_id' => $districtInfo->id,
                                                'name' => $ward['name'],
                                                'name_without_accent' => str_slug($ward['name'])
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $this->error('Location file not found');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
