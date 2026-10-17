<?php

namespace App\Http\Controllers\API\DynamicPage;

use App\Http\Controllers\Controller;
use App\Models\DynamicPage;
use App\Traits\ApiResponse;

class DynamicPageController extends Controller
{
    use ApiResponse;

    public function list()
    {
        $data = DynamicPage::where('status', 'active')->paginate(10);

        return $this->ok('Data Retrieve Successfully!', $data);
    }

    public function show($id)
    {
        $data = DynamicPage::where('status', 'active')->find($id);

        if (!$data) {
            return $this->error("Data not found", 404);
        }

        return $this->ok('Data Retrieve Successfully!', $data);
    }
}
