<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EntityRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function storeEntityRequest_validates_required_fields()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        // 必須フィールドのテスト
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        
        $errors = $validator->errors();
        $this->assertTrue($errors->has('name'));
        $this->assertFalse($errors->has('desc')); // desc は必須ではない
    }

    /** @test */
    public function storeEntityRequest_passes_with_valid_data()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        $validData = [
            'name' => '家族の記念日',
            'desc' => '家族に関する大切な記念日を管理'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function storeEntityRequest_validates_name_max_length()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        $invalidData = [
            'name' => str_repeat('あ', 256), // 256文字
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function storeEntityRequest_allows_null_description()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        $validData = [
            'name' => 'エンティティ名',
            'desc' => null
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function storeEntityRequest_allows_empty_description()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        $validData = [
            'name' => 'エンティティ名',
            'desc' => ''
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function storeEntityRequest_has_japanese_error_messages()
    {
        $request = new StoreEntityRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);

        // 日本語メッセージかどうかチェック
        $this->assertStringContainsString('必須', $messages['name.required']);
        $this->assertStringContainsString('文字以内', $messages['name.max']);
    }

    /** @test */
    public function updateEntityRequest_has_additional_status_field()
    {
        $storeRequest = new StoreEntityRequest();
        $updateRequest = new UpdateEntityRequest();

        $storeRules = $storeRequest->rules();
        $updateRules = $updateRequest->rules();

        // Store には status がない
        $this->assertArrayNotHasKey('status', $storeRules);
        
        // Update には status がある
        $this->assertArrayHasKey('status', $updateRules);
        $this->assertEquals('boolean', $updateRules['status']);
    }

    /** @test */
    public function updateEntityRequest_validates_status_field()
    {
        $request = new UpdateEntityRequest();
        $rules = $request->rules();

        // 有効な status 値
        $validData = [
            'name' => 'エンティティ名',
            'status' => true
        ];
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        $validData['status'] = false;
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // 無効な status 値
        $invalidData = [
            'name' => 'エンティティ名',
            'status' => 'invalid'
        ];
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('status'));
    }

    /** @test */
    public function updateEntityRequest_has_japanese_error_messages_for_status()
    {
        $request = new UpdateEntityRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('status.boolean', $messages);
        $this->assertStringContainsString('真偽値', $messages['status.boolean']);
    }

    /** @test */
    public function updateEntityRequest_validates_correctly_with_all_fields()
    {
        $request = new UpdateEntityRequest();
        $rules = $request->rules();

        $validData = [
            'name' => '更新されたエンティティ',
            'desc' => '更新された説明',
            'status' => true
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function entityRequest_authorize_returns_true()
    {
        $storeRequest = new StoreEntityRequest();
        $updateRequest = new UpdateEntityRequest();

        $this->assertTrue($storeRequest->authorize());
        $this->assertTrue($updateRequest->authorize());
    }

    /** @test */
    public function entityRequest_validates_name_with_various_characters()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        // 有効な名前
        $validNames = [
            '家族',
            'Family',
            '家族の記念日',
            'Family Anniversary',
            '123',
            'エンティティ名 2023',
            'テスト-エンティティ_1',
        ];

        foreach ($validNames as $name) {
            $data = ['name' => $name];
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Name '{$name}' should be valid");
        }

        // 長すぎる名前
        $tooLongName = str_repeat('あ', 256);
        $data = ['name' => $tooLongName];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails(), "Name should be too long");
    }

    /** @test */
    public function entityRequest_handles_special_characters_in_description()
    {
        $request = new StoreEntityRequest();
        $rules = $request->rules();

        $validData = [
            'name' => 'テスト',
            'desc' => '特殊文字: !@#$%^&*()_+-=[]{}|;:,.<>?/~`'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }
}