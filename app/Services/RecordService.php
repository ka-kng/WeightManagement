<?php

namespace App\Services;

use App\Models\Record;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordService
{
  // 新しい記録を作成する
  public function createRecord(array $data, array $files = []): Record
  {
    $record = new Record();
    $record->user_id = Auth::id();
    $this->fillRecord($record, $data, $files);
    $record->save();

    return $record;
  }

  // 既存の記録を更新する
  public function updateRecord(Record $record, array $data, array $files = []): Record
  {
    $this->fillRecord($record, $data, $files);
    $record->save();

    return $record;
  }

  // 記録を削除する（画像ファイルも削除）
  public function deleteRecord(Record $record): void
  {
    if ($record->meal_photos) {
      $photos = is_string($record->meal_photos) ? json_decode($record->meal_photos, true) : $record->meal_photos;
      if (is_array($photos)) {
        foreach ($photos as $photo) {
          Storage::disk('public')->delete($photo);
        }
      }
    }
    $record->delete();
  }

  // モデルにフォームデータと画像データをセットする
  private function fillRecord(Record $record, array $data, array $files = []): void
  {
    // 基本データをセット
    $record->date = $data['date'];
    $record->weight = $data['weight'];
    $record->sleep_hours = $data['sleep_hours'];
    $record->sleep_minutes = $data['sleep_minutes'] ?? 0;
    $record->meals = isset($data['meals']) ? json_encode($data['meals'], JSON_UNESCAPED_UNICODE) : null;
    $record->meal_detail = $data['meal_detail'] ?? null;
    $record->exercises = isset($data['exercises']) ? json_encode($data['exercises'], JSON_UNESCAPED_UNICODE) : null;
    $record->exercise_detail = $data['exercise_detail'] ?? null;

    // 画像アップロード
    $photos = [];
    if ($record->meal_photos) {
      $photos = is_string($record->meal_photos) ? json_decode($record->meal_photos, true) : $record->meal_photos;
    }

    if ($files) {
      // 古い写真を削除
      foreach ($photos as $oldPhoto) {
        Storage::disk('public')->delete($oldPhoto);
      }

      // 新しい写真をアップロード
      $photos = [];
      foreach ($files as $file) {
        $photos[] = $file->store('meal_photos', 'public');
      }
    }

    $record->meal_photos = json_encode($photos);
  }
}
