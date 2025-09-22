<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyseRecordJob;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    public function index()
    {
        $records = Record::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('record.list', compact('records'));
    }

    public function create()
    {
        return view('record.form', ['record' => new Record()]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'date' => 'required|date',
            'weight' => 'required|numeric|min:0',
            'sleep_hours' => 'required|integer|min:0|max:23',
            'sleep_minutes' => 'required|integer|min:0|max:59',
            'meals' => 'nullable|array',
            'meal_detail' => 'nullable|string',
            'meal_photos'   => 'nullable|array|max:5',
            'meal_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'exercises' =>  'nullable|array',
            'exercise_detail' => 'nullable|string',
        ], [
            'meal_photos.max'      => '画像は最大5枚までアップロードできます。',
            'meal_photos.*.max' => '1ファイルの容量は最大5MBまでです。',
            'meal_photos.*.image' => '画像ファイルのみアップロードできます。',
            'meal_photos.*.mimes' => '許可されている形式は jpeg, png, jpg, gif です。',
        ]);

        $record = new Record();
        $record->user_id = Auth::id();
        $record->date = $validated['date'];
        $record->weight = $validated['weight'];
        $record->sleep_hours = $validated['sleep_hours'];
        $record->sleep_minutes = $validated['sleep_minutes'] ?? 0;
        $record->meals = isset($validated['meals']) ? json_encode($validated['meals'], JSON_UNESCAPED_UNICODE) : null;
        $record->meal_detail = $validated['meal_detail'] ?? null;
        $record->exercises = isset($validated['exercises']) ? json_encode($validated['exercises'], JSON_UNESCAPED_UNICODE) : null;
        $record->exercise_detail = $validated['exercise_detail'] ?? null;

        $photos = [];
        if ($request->hasFile('meal_photos')) {
            $files = $request->file('meal_photos');
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                $photos[] = $file->store('meal_photos', 'public');
            }
        }
        $record->meal_photos = json_encode($photos);

        $record->save();

        AnalyseRecordJob::dispatch($record);

        return redirect()->route('records.index', $record)->with('success', '記録を更新しました');
    }

    public function show(Record $record)
    {
        return view('record.show', compact('record'));
    }

    public function edit(Record $record)
    {
        return view('record.form', compact('record'));
    }

    public function update(Request $request, Record $record)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'weight' => 'required|numeric|min:0',
            'sleep_hours' => 'required|integer|min:0|max:23',
            'sleep_minutes' => 'required|integer|min:0|max:59',
            'meals' => 'nullable|array',
            'meal_detail' => 'nullable|string',
            'meal_photos'   => 'nullable|array|max:5',
            'meal_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'exercises' =>  'nullable|array',
            'exercise_detail' => 'nullable|string',
        ], [
            'meal_photos.max'      => '画像は最大5枚までアップロードできます。',
            'meal_photos.*.max' => '1ファイルの容量は最大5MBまでです。',
            'meal_photos.*.image' => '画像ファイルのみアップロードできます。',
            'meal_photos.*.mimes' => '許可されている形式は jpeg, png, jpg, gif です。',
        ]);

        $record->date = $validated['date'];
        $record->weight = $validated['weight'];
        $record->sleep_hours = $validated['sleep_hours'];
        $record->sleep_minutes = $validated['sleep_minutes'] ?? 0;
        $record->meals = isset($validated['meals']) ? json_encode($validated['meals'], JSON_UNESCAPED_UNICODE) : null;
        $record->meal_detail = $validated['meal_detail'] ?? null;
        $record->exercises = isset($validated['exercises']) ? json_encode($validated['exercises'], JSON_UNESCAPED_UNICODE) : null;
        $record->exercise_detail = $validated['exercise_detail'] ?? null;

        $photos = $record->meal_photos ? json_decode($record->meal_photos, true) : [];

        if ($request->hasFile('meal_photos')) {

            if ($photos) {
                foreach ($photos as $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $photos = [];

            $files = $request->file('meal_photos');
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                $photos[] = $file->store('meal_photos', 'public');
            }
        }

        $record->meal_photos = json_encode($photos);
        $record->save();

        return redirect()->route('records.show', $record)->with('success', '記録を更新しました');
    }

    public function destroy(Record $record)
    {
        if ($record->meal_photos) {
            foreach (json_decode($record->meal_photos, true) as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $record->delete();

        return redirect()->route('records.index')->with('success', '記録を削除しました');
    }
}
