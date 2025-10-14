<?php

namespace Tests\Feature;

use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_record(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 偽のストレージを使って、画像アップロードのテストができるようにする
        Storage::fake('public');

        // ログインした状態で、記録作成のリクエストを送信
        $response = $this->actingAs($user)->post(route('records.store'), [
            'date' => now()->format('Y-m-d'),
            'weight' => 65,
            'sleep_hours' => 7,
            'sleep_minutes' => 30,
            'meals' => ['朝食', '昼食'],
            'meal_detail' => 'テスト食事',
            'meal_photos' => [UploadedFile::fake()->image('photo1.jpg')],
            'exercises' => ['ランニング'],
            'exercise_detail' => 'テスト運動',
        ]);

        // 作成後に index ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.index'));

        // データベースに正しく保存されているか確認
        $this->assertDatabaseHas('records', [
            'user_id' => $user->id,
            'weight' => 65,
        ]);
    }

    /** @test */
    public function a_user_can_update_a_record(): void
    {
        // ユーザーとその記録を作成
        $user = User::factory()->create();
        $record = Record::factory()->for($user)->create([
            'weight' => 60,
            'sleep_hours' => 6,
        ]);

        // ログインした状態で、記録更新リクエストを送信
        $response = $this->actingAs($user)->patch(route('records.update', $record), [
            'date' => $record->date->format('Y-m-d'),
            'weight' => 70,
            'sleep_hours' => 8,
            'sleep_minutes' => 0,
        ]);

        // 更新後に show ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.show', $record));

        // データベースが正しく更新されていることを確認
        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'weight' => 70,
            'sleep_hours' => 8,
        ]);
    }

    /** @test */
    public function a_user_can_delete_a_record(): void
    {
        // ユーザーと記録を作成
        $user = User::factory()->create();
        $record = Record::factory()->for($user)->create();

        // ログインした状態で、削除リクエストを送信
        $response = $this->actingAs($user)->delete(route('records.destroy', $record));

        // 削除後に index ページへリダイレクトされることを確認
        $response->assertRedirect(route('records.index'));

        // データベースから削除されていることを確認
        $this->assertDatabaseMissing('records', [
            'id' => $record->id,
        ]);
    }

    /** @test */
    public function a_record_belongs_to_a_user(): void
    {
        // ユーザーと記録を作成
        $user = User::factory()->create();
        $record = Record::factory()->for($user)->create();

        // Record の user() 関係が正しいか確認
        $this->assertInstanceOf(User::class, $record->user);
        $this->assertEquals($user->id, $record->user->id);
    }
}
