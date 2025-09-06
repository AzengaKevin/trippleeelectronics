<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(private readonly RoomService $roomService) {}

    public function index(Request $request)
    {
        $params = $request->only('query');

        $rooms = $this->roomService->get(...$params);

        return new RoomResource($rooms);
    }
}
