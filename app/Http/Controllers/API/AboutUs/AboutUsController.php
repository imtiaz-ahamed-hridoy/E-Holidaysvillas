<?php

namespace App\Http\Controllers\API\AboutUs;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    use ApiResponse;

    /**
     * Get About Us Page Data
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $aboutUs = AboutUs::find(1);

        if (!$aboutUs) {
            return $this->error('About Us data not found', 404);
        }

        return $this->success('About Us data fetched successfully', $aboutUs);
    }
}
