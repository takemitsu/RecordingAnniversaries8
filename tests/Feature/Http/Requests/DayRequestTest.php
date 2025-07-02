<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreDayRequest;
use App\Http\Requests\UpdateDayRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DayRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function storeDayRequest_validates_required_fields()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        // 必須フィールドのテスト
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        
        $errors = $validator->errors();
        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('anniv_at'));
    }

    /** @test */
    public function storeDayRequest_passes_with_valid_data()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        $validData = [
            'name' => '誕生日',
            'desc' => '大切な人の誕生日',
            'anniv_at' => '2023-12-25'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function storeDayRequest_validates_name_max_length()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        $invalidData = [
            'name' => str_repeat('あ', 256), // 256文字
            'anniv_at' => '2023-12-25'
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function storeDayRequest_validates_date_format()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        $invalidData = [
            'name' => '記念日',
            'anniv_at' => '2023/12/25' // 無効な形式
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('anniv_at'));
    }

    /** @test */
    public function storeDayRequest_allows_null_description()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        $validData = [
            'name' => '記念日',
            'desc' => null,
            'anniv_at' => '2023-12-25'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function storeDayRequest_has_japanese_error_messages()
    {
        $request = new StoreDayRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);
        $this->assertArrayHasKey('anniv_at.required', $messages);
        $this->assertArrayHasKey('anniv_at.date_format', $messages);

        // 日本語メッセージかどうかチェック
        $this->assertStringContainsString('必須', $messages['name.required']);
        $this->assertStringContainsString('文字以内', $messages['name.max']);
    }

    /** @test */
    public function updateDayRequest_has_same_validation_rules_as_store()
    {
        $storeRequest = new StoreDayRequest();
        $updateRequest = new UpdateDayRequest();

        $this->assertEquals($storeRequest->rules(), $updateRequest->rules());
        $this->assertEquals($storeRequest->messages(), $updateRequest->messages());
    }

    /** @test */
    public function updateDayRequest_validates_correctly()
    {
        $request = new UpdateDayRequest();
        $rules = $request->rules();

        $validData = [
            'name' => '更新された記念日',
            'desc' => '更新された説明',
            'anniv_at' => '2024-01-01'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function dayRequest_authorize_returns_true()
    {
        $storeRequest = new StoreDayRequest();
        $updateRequest = new UpdateDayRequest();

        $this->assertTrue($storeRequest->authorize());
        $this->assertTrue($updateRequest->authorize());
    }

    /** @test */
    public function dayRequest_validates_various_date_formats()
    {
        $request = new StoreDayRequest();
        $rules = $request->rules();

        // 有効な日付形式
        $validDates = [
            '2023-01-01',
            '2023-12-31',
            '2024-02-29', // うるう年
        ];

        foreach ($validDates as $date) {
            $data = [
                'name' => '記念日',
                'anniv_at' => $date
            ];
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Date {$date} should be valid");
        }

        // 無効な日付形式
        $invalidDates = [
            '2023-13-01', // 無効な月
            '2023-02-30', // 無効な日
            '2023-1-1',   // ゼロパディングなし
            '23-12-25',   // 2桁年
        ];

        foreach ($invalidDates as $date) {
            $data = [
                'name' => '記念日',
                'anniv_at' => $date
            ];
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->fails(), "Date {$date} should be invalid");
        }
    }
}