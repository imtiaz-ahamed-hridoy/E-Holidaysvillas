<?php

namespace App\Http\Controllers\API\SystemSetting;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    use ApiResponse;

    public function systemSetting()
    {
        $systemSetting = SystemSetting::first();
        if ($systemSetting) {
            return $this->ok('Data Retrieve Successfully!',$systemSetting, 200);
        }
        return $this->error("System Setting not found", 500);
    }
}
