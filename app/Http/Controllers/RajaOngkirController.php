<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $results = $this->rajaOngkirService->searchLocation($query);
        return response()->json($results);
    }

    public function calculateCost(Request $request)
    {
        $request->validate([
            'destination_city_id' => 'required',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string|in:jne,pos,tiki,jnt,sicepat,anteraja,wahana,ninja,lion',
        ]);

        $costs = $this->rajaOngkirService->calculateCost(
            $request->destination_city_id,
            $request->weight,
            $request->courier
        );

        return response()->json($costs);
    }
}
