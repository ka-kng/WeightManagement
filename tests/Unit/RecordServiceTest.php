<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RecordService;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RecordServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RecordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecordService();
    }

    // レコード作成テスト
    // createRecord が正しくレコードを作成してDBに保存されることを確認
    public function test_create_record_saves_to_database()
    {
        $user = User::factory()->create();
        $this->be($user);

        $data = [
            'date' => now(),
            'weight' => 65,
            'sleep_hours' => 7,
            'sleep_minutes' => 30,
            'meals' => ['朝食', '昼食', '夕食'],
            'meal_detail' => 'テストの食事詳細',
            'exercises' => ['ランニング'],
            'exercise_detail' => '筋トレ',
        ];

        $record = $this->service->createRecord($data);

        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'user_id' => $user->id,
            'weight' => 65,
        ]);
    }

    // レコード更新テスト
    // updateRecord でレコード内容が更新されることを確認
    public function test_update_record_updates_database_record()
    {
        $user = User::factory()->create();
        $this->be($user);

        $record = Record::factory()->create(['user_id' => $user->id, 'weight' => 65]);

        $updateData = [
            'date' => now(),
            'weight' => 70,
            'sleep_hours' => 8,
        ];

        $updatedRecord = $this->service->updateRecord($record, $updateData);

        $this->assertEquals(70, $updatedRecord->weight);
        $this->assertDatabaseHas('records', ['id' => $record->id, 'weight' => 70]);
    }

    // レコード削除テスト（画像含む）
    // deleteRecord でレコードと関連画像が削除されることを確認
    public function test_delete_record_removes_record_and_photos()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->be($user);

        // 画像ファイル作成
        $file = UploadedFile::fake()->image('meal.jpg');
        $record = $this->service->createRecord(
            [
                'date' => now(),
                'weight' => 65,
                'sleep_hours' => 7,
            ],
            [$file]
        );

        $this->service->deleteRecord($record);

        $this->assertDatabaseMissing('records', ['id' => $record->id]);
        Storage::disk('public')->assertMissing('meal_photos/' . $file->hashName());
    }
}
