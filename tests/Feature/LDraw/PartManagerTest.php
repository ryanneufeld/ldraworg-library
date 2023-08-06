<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('add or change part', function () {
    $u = User::factory()->create([
        'name' => 'James Jessiman',
        'realname' => 'James Jessiman',
        'account_type' => 1,
    ]);
    User::factory()->create([
        'name' => 'PTadmin',
        'realname' => 'PTadmin',
        'account_type' => 2,
    ]);
    User::factory()->create([
        'name' => 'Steffen',
        'realname' => 'Steffen',
        'account_type' => 0,
    ]);
    User::factory()->create([
        'name' => 'westrate',
        'realname' => 'Andrew Westrate',
        'account_type' => 0,
    ]);
    User::factory()->create([
        'name' => 'unknown',
        'realname' => 'unknown',
        'account_type' => 2,
    ]);
    $manager = app(\App\LDraw\PartManager::class);
    $part = $manager->addOrChangePart(file_get_contents(__DIR__ . '/testfiles/parts/3001.dat'));
    expect($part->user->id)->toBe($u->id);
    expect($part->release)->toBe(null);
    expect($part->history->count())->toBe(6);
    expect($part->part_license_id)->toBe($part->user->part_license_id);
    expect((array)$part->missing_parts)->toBe(['s\\3001s01.dat']);
    $spart = $manager->addOrChangePart(file_get_contents(__DIR__ . '/testfiles/parts/s/3001s01.dat'));
    expect($spart->parents->find($part->id))->not->toBeNull();
    $part->refresh();
    expect((array)$part->missing_parts)->toBe([]);
    expect($part->subparts->find($spart->id))->not->toBeNull();
    expect($manager->movePart($spart, '3001as01.dat', $spart->type))->toBe(true);
    expect($spart->filename)->toBe('parts/s/3001as01.dat');
    $part->body->refresh();
    expect(strpos($part->body->body, $spart->name()))->not->toBe(false);
});
