<?php

declare(strict_types=1);

it('reports that the application and database are healthy', function (): void {
    $response = $this->getJson(route('health'));

    $response
        ->assertOk()
        ->assertJson([
            'application' => config('app.name'),
            'status' => 'healthy',
            'services' => [
                'database' => 'available',
            ],
        ])
        ->assertJsonStructure([
            'application',
            'status',
            'services' => [
                'database',
            ],
            'timestamp',
        ]);
});
