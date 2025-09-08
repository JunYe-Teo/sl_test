<?php

use App\Models\Log;

//test
// 1. Accept a key(string) and value(some JSON blob/string) and store them.
// 2. Accept a key and return the corresponding latest value
// 3. When given a key AND a timestamp, return whatever the value of the key at the time was.
// 4. Displays all values currently stored in the database.

test('can store key and value', function () {
    $key = 'test';
    $value = '{"data": [{"title": "test store", "description": "this should pass!"}]}';
    $response = $this->post('/api/logs', [
        'key' => $key,
        'value' => $value,
    ]);

    $response->assertStatus(201);

    $latestLog = Log::latest()->first();

    expect($latestLog->key)->toBe($key)
        ->and($latestLog->value)->toBe($value);
});

test('accept key and return corresponding latest value', function () {
    $firstLog = Log::factory()->create();
    $response = $this->get('/api/logs/'.$firstLog->key);
    $response->assertStatus(200);
    $responseData = json_decode($response->getContent());
    expect(json_encode(data_get($responseData, 'data')))->toBe($firstLog->value);

    $value = json_encode(['test' => ['message' => 'create from pest', 'date' => now()->format('Y-m-d')]]);

    // use insert to create log with different `created_at` timestamp
    Log::insert([
        'key' => $firstLog->key,
        'value' => json_encode($value),
        'created_at' => now()->addSecond()->timestamp,
        'updated_at' => now()->addSecond()->timestamp,
    ]);

    // get with same key should return latest value(secondLog)
    $response = $this->get('/api/logs/'.$firstLog->key);
    $response->assertStatus(200);
    $responseData = json_decode($response->getContent());

    expect(json_encode(data_get($responseData, 'data')))->toBe($value);
});

test('can return exact log by key and timestamp', function () {
    $firstLog = Log::factory()->create();

    $value = json_encode(['test' => ['message' => 'create from pest', 'date' => now()->format('Y-m-d')]]);

    // use insert to create log with different `created_at` timestamp
    Log::insert([
                    'key' => $firstLog->key,
                    'value' => json_encode($value),
                    'created_at' => now()->addSecond()->timestamp,
                    'updated_at' => now()->addSecond()->timestamp,
                ]);

    $timestamp = $firstLog->created_at;
    $response = $this->get('/api/logs/'.$firstLog->key.'?timestamp='.$timestamp);
    $response->assertStatus(200);
    $responseData = json_decode($response->getContent());

    expect(json_encode(data_get($responseData, 'data')))->toBe($firstLog->value);
});

test('can return all values stored in database', function () {
    $number = random_int(25, 100);
    Log::factory()->createMany($number);

    $response = $this->get('/api/logs');
    $response->assertStatus(200);
    $responseData = json_decode($response->getContent());
    $responseDataCount = collect(data_get($responseData, 'data'))->count();

    expect($responseDataCount)->toBe($number);
});
