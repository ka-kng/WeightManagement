<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordRequest;
use App\Models\Record;
use App\Services\RecordService;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
{
    private RecordService $recordService;

     // コンストラクタでサービスを依存性注入
    public function __construct(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    // 記録一覧ページ
    public function index()
    {
        $records = Record::where('user_id', Auth::id())->orderBy('date', 'desc')->paginate(20);
        return view('record.list', compact('records'));
    }

    // 記録作成フォーム
    public function create()
    {
        return view('record.form', ['record' => new Record()]);
    }


    // 記録を保存
    public function store(RecordRequest $request)
    {
        $data = $request->validated();
        $files = $request->file('meal_photos') ?? [];
        $files = is_array($files) ? $files : [$files];

        $this->recordService->createRecord($data, $files);

        return redirect()->route('records.index')->with('success', '記録を登録しました');
    }

    // 記録詳細ページ
    public function show(Record $record)
    {
        return view('record.show', compact('record'));
    }

    // 記録編集フォーム
    public function edit(Record $record)
    {
        return view('record.form', compact('record'));
    }

    // 記録を更新
    public function update(RecordRequest $request, Record $record)
    {
        $data = $request->validated();
        $files = $request->file('meal_photos') ?? [];
        $files = is_array($files) ? $files : [$files];

        $this->recordService->updateRecord($record, $data, $files);

        return redirect()->route('records.show', $record)->with('success', '記録を更新しました');
    }

    // 記録を削除
    public function destroy(Record $record)
    {
        $this->recordService->deleteRecord($record);
        return redirect()->route('records.index')->with('success', '記録を削除しました');
    }
}
