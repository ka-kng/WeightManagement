<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\HomeSleepService;

class HomeSleepController extends Controller
{
    protected $service;

    public function __construct(HomeSleepService $service)
    {
        $this->service = $service;
    }

    public function show()
    {
        $user = Auth::user();

        // Service から週・月・年の睡眠データを取得
        $data = $this->service->getSleepData($user);

        return view('home.chart.sleep', array_merge(
            $data['week'],
            $data['month'],
            $data['year']
        ));
    }
}
