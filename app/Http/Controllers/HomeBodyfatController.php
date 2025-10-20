<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\HomeBodyfatService;

class HomeBodyfatController extends Controller
{
    protected $service;

    public function __construct(HomeBodyfatService $service)
    {
        $this->service = $service;
    }

    public function show()
    {
        $user = Auth::user();

        // Service から週・月・年データをまとめて取得
        $data = $this->service->getBodyfatData($user);

        // Blade に展開
        return view('home.chart.bodyfat', array_merge(
            $data['week'],
            $data['month'],
            $data['year']
        ));
    }
}
