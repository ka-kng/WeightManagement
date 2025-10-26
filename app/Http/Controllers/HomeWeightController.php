<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\HomeWeightService;

class HomeWeightController extends Controller
{
    private HomeWeightService $weightService;

    public function __construct(HomeWeightService $weightService)
    {
        $this->weightService = $weightService;
    }

    public function show()
    {
        $user = Auth::user();
        $data = $this->weightService->getWeightData($user);

        return view('home.chart.weight', [
            'weekLabels' => $data['week']['labels'],
            'weekDays' => $data['week']['days'],
            'weekWeights' => $data['week']['weights'],
            'weekAverage' => $data['week']['average'],
            'monthLabels' => $data['month']['labels'],
            'monthWeights' => $data['month']['weights'],
            'fullPeriodLabel' => $data['month']['fullPeriodLabel'],
            'monthAverage' => $data['month']['average'],
            'yearLabels' => $data['year']['labels'],
            'yearDays' => $data['year']['days'],
            'yearWeights' => $data['year']['weights'],
            'yearAverage' => $data['year']['average'],
        ]);
    }
}
