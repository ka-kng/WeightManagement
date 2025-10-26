<?php

namespace App\Services;

use App\Models\Record;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    // BMIキャッシュ削除（新規登録時）
    Cache::forget("bmi_data_user_{$record->user_id}");

    // 体脂肪率キャッシュ削除（新規登録時）
    Cache::forget("bodyfat_data_user_{$record->user_id}");

    // 睡眠時間キャッシュ削除
    Cache::forget("sleep_data_user_{$record->user_id}");

    // 体重キャッシュ削除
    Cache::forget("weight_data_user_{$record->user_id}");

    return $record;
  }

  // 既存の記録を更新する
  public function updateRecord(Record $record, array $data, array $files = []): Record
  {
    $this->fillRecord($record, $data, $files);
    $record->save();

    // BMIキャッシュ削除（更新時）
    Cache::forget("bmi_data_user_{$record->user_id}");

    // 体脂肪率キャッシュ削除
    Cache::forget("bodyfat_data_user_{$record->user_id}");

    // 睡眠時間キャッシュ削除
    Cache::forget("sleep_data_user_{$record->user_id}");

    // 体重キャッシュ削除
    Cache::forget("weight_data_user_{$record->user_id}");

    return $record;
  }

  // 記録を削除する（画像ファイルも削除）
  public function deleteRecord(Record $record): void
  {
    // meal_photosにデータがある場合
    if ($record->meal_photos) {
      // JSON形式ならデコードして配列に変換
      $photos = is_string($record->meal_photos) ? json_decode($record->meal_photos, true) : $record->meal_photos;

      // 配列が正しく取得できたらファイルを削除
      if (is_array($photos)) {
        foreach ($photos as $photo) {
          // publicディスクからファイルを削除
          Storage::disk('public')->delete($photo);
        }
      }
    }

    $record->delete();

    // BMIキャッシュ削除（削除時）
    Cache::forget("bmi_data_user_{$record->user_id}");

    // 体脂肪率キャッシュ削除
    Cache::forget("bodyfat_data_user_{$record->user_id}");

    // 睡眠時間キャッシュ削除
    Cache::forget("sleep_data_user_{$record->user_id}");

    // 体重キャッシュ削除
    Cache::forget("weight_data_user_{$record->user_id}");
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
      // 既存のmeal_photosが文字列ならデコード
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

    // 保存する前にJSON形式でmeal_photosにセット
    $record->meal_photos = json_encode($photos);
  }
}
