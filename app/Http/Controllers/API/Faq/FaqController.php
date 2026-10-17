<?php

namespace App\Http\Controllers\API\Faq;

use App\Models\Faq;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class FaqController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $faq = Faq::where('status', 'active')->get();
        if ($faq->count() > 0) {
            return $this->ok("Data found", $faq);
        } else {
            return $this->success('No Data found', [], 200);
        }
    }
}
