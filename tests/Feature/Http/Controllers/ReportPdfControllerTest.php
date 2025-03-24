<?php

use App\Models\Report;
use App\Models\User;
use App\Services\GeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can download report PDF', function () {
    // Arrange
    $user = User::factory()->create();
    $report = Report::factory()->recycle($user)->create();

    // Act
    $response = $this->actingAs($user)->get(route('report.pdf', $report));

    // Assert
    $response->assertStatus(200);
    $this->assertNotEmpty($response->getContent());
    $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

    $fileName = GeneratorService::generateFileName(['report', $report->contract->name, sprintf('%04d', $report->year), sprintf('%02d', $report->month)]);
    $this->assertEquals('inline; filename=' . $fileName, $response->headers->get('Content-Disposition'));
});
