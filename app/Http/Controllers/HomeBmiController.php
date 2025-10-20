<?php

namespace App\Http\Controllers;

use App\Services\HomeBmiService;
use Illuminate\Support\Facades\Auth;

class HomeBmiController extends Controller
{
    private $bmiService;

    public function __construct(HomeBmiService $bmiService)
    {
        $this->bmiService = $bmiService;
    }

    public function show()
    {
        $user = Auth::user();
        $height = $user->height / 100;

        $data = $this->bmiService->getBmiData($user->id, $height);

        return view('home.chart.bmi', [
            'weekLabels' => $data['week']['labels'],
            'weekDays' => $data['week']['days'] ?? [],
            'weekBmis' => $data['week']['bmis'],
            'weekAverage' => $data['week']['average'],

            'monthLabels' => $data['month']['labels'],
            'monthDays' => $data['month']['days'] ?? [],
            'monthBmis' => $data['month']['bmis'],
            'monthAverage' => $data['month']['average'],
            'fullPeriodLabel' => $data['month']['fullPeriodLabel'],

            'yearLabels' => $data['year']['labels'],
            'yearDays' => $data['year']['days'] ?? [],
            'yearBmis' => $data['year']['bmis'],
            'yearAverage' => $data['year']['average'],
        ]);
    }
}
