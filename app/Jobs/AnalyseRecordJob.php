<?php

namespace App\Jobs;

use App\Models\Record;
use App\Models\Analysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI;

class AnalyseRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record;

    public function __construct(Record $record)
    {
        $this->record = $record;
    }

    public function handle(): void
    {
        $user = $this->record->user;

        $prompt = "ユーザー情報:

        - 性別: " . ($user->gender == 1 ? '男性' : '女性') . "
        - 生年月日: " . $user->birth_date->format('Y-m-d') . "
        - 身長: {$user->height}cm
        - 目標体重: {$user->target_weight}kg

        当日の情報:
        - 体重: {$this->record->weight}kg
        - 睡眠: {$this->record->sleep_hours}時間{$this->record->sleep_minutes}分
        - 食事: {$this->record->meal_detail}
        - 運動: {$this->record->exercise_detail}

        上記データを基に次の分析をJSON形式で返してください:
        1. 体重変化予測 (weight_prediction)
        2. 生活傾向分析 (lifestyle_trend)
        3. 異常値検知 (anomaly_detection)
        4. モチベーションコメント (motivation_comment)";

        

    }
}
