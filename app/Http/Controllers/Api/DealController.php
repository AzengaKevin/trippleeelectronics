<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DealResource;
use App\Services\DealService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function __construct(private DealService $dealService) {}

    public function index(Request $request)
    {

        $params = $request->only('query', 'client', 'limit');

        $deals = $this->dealService->get(...$params);

        return new DealResource($deals);
    }
}
