<?php

namespace Tests\Feature;

use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecordControllerTest extends TestCase
{
    use RefreshDatabase;

    // レコードを新規作成する際のテスト
    public function test_store_creates_a_record_in_database(): void
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 偽のストレージで画像アップロードの影響を避ける
        Storage::fake('public');
        $file = UploadedFile::fake()->image('meal.jpg');

        // ログイン状態で POST リクエストを送信
        $response = $this->actingAs($user)->post(route('records.store'), [
            'date' => now()->format('Y-m-d'),
            'weight' => 65,
            'sleep_hours' => 7,
            'sleep_minutes' => 30,
            'meals' => ['朝食', '昼食'],
            'meal_detail' => 'テスト食事',
            'meal_photos' => [$file],
            'exercises' => ['ランニング'],
            'exercise_detail' => 'テスト運動',
        ]);

        // DB にレコードが作成されていることを確認
        $this->assertDatabaseHas('records', [
            'user_id' => $user->id,
            'weight' => 65,
        ]);

        // 作成後に index ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.index'));
    }

    // レコードの更新がデータベースに反映されるかのテスト
    public function test_update_modifies_a_record_in_database(): void
    {
        $user = User::factory()->create();
        $record = Record::factory()->for($user)->create([
            'weight' => 60,
            'sleep_hours' => 6,
        ]);

        // ログイン状態で PATCH リクエストを送信
        $response = $this->actingAs($user)->patch(route('records.update', $record), [
            'date' => $record->date->format('Y-m-d'),
            'weight' => 70,
            'sleep_hours' => 8,
            'sleep_minutes' => 0,
            'meals' => ['朝食'],
            'meal_detail' => '更新テスト',
            'exercises' => ['筋トレ'],
            'exercise_detail' => '更新運動',
        ]);

        // DB が正しく更新されていることを確認
        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'weight' => 70,
            'sleep_hours' => 8,
        ]);

        // 更新後に show ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.show', $record));
    }

    // レコードがデータベースから削除されることを確認するテスト
    public function test_destroy_deletes_a_record_from_database(): void
    {
        $user = User::factory()->create();
        $record = Record::factory()->for($user)->create();

        // ログイン状態で DELETE リクエストを送信
        $response = $this->actingAs($user)->delete(route('records.destroy', $record));

        // DB から削除されていることを確認
        $this->assertDatabaseMissing('records', [
            'id' => $record->id,
        ]);

        // 削除後に index ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.index'));
    }
}
