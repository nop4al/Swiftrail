<?php

namespace App\Http\Controllers\Api;

use App\Services\InfoService;

class InfoController extends ApiBaseController
{
    public function __construct(private InfoService $infoService)
    {
    }

    // GET /api/v1/info
    public function show()
    {
        $info = $this->infoService->getAppInfo();
        return $this->success($info, 'App info');
    }
}
