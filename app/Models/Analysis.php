<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    // 実際のテーブル名を指定
    protected $table = 'ai_analyses';

    // 保存可能なカラムを指定
    protected $fillable = [
        'record_id',
        'weight_prediction',
        'lifestyle_trend',
        'anomaly_detection',
        'motivation_comment',
    ];

    // Record とのリレーション
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
}
