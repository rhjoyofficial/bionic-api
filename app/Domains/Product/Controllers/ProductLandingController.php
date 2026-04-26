<?php

namespace App\Domains\Product\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated  This legacy controller is superseded by the Landing domain.
 *              The full system now uses LandingPageController + /product-page/{slug}.
 *              Do NOT add new features here. Scheduled for removal once all
 *              consumers are confirmed migrated.
 */
class ProductLandingController extends Controller
{
    public function show($slug)
    {
        Log::warning('Deprecated ProductLandingController hit.', ['slug' => $slug]);

        return ApiResponse::error('This endpoint is deprecated. Use /product-page/{slug} instead.', null, 410);
    }
}
