<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Record;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1; // サンプルユーザーID
        $startDate = Carbon::now()->subYear()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $mealsOptions = ['炭水化物', 'タンパク質', '脂質', 'ビタミン', 'ミネラル'];
        $exercisesOptions = ['有酸素運動', '筋トレ', 'ストレッチ', 'ヨガ', 'スポーツ'];

        $records = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // ランダムで1～3種類の食事を選択
            $mealsCount = rand(1, 3);
            $meals = (array)array_rand(array_flip($mealsOptions), $mealsCount);

            // ランダムで1～2種類の運動を選択
            $exercisesCount = rand(1, 2);
            $exercises = (array)array_rand(array_flip($exercisesOptions), $exercisesCount);

            $records[] = [
                'user_id'        => $userId,
                'date'           => $date->format('Y-m-d'),
                'weight'         => round(rand(5500, 7500) / 100, 1), // 55.0kg～75.0kg
                'sleep_hours'    => rand(5, 9),
                'sleep_minutes'  => rand(0, 59),
                'meals'          => json_encode($meals, JSON_UNESCAPED_UNICODE),
                'meal_detail'    => 'サンプルの食事内容',
                'meal_photos'    => json_encode([]),
                'exercises'      => json_encode($exercises, JSON_UNESCAPED_UNICODE),
                'exercise_detail'=> 'サンプルの運動内容',
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        // 一括インサート（パフォーマンス向上）
        foreach (array_chunk($records, 100) as $chunk) {
            Record::insert($chunk);
        }
    }
}
