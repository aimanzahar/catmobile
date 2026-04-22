<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\PocketBase\PocketBaseClient;

class LandingController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index()
    {
        $response = $this->client->listRecords('cg_services', null, [
            'filter' => 'is_active=true',
            'sort' => 'sort_order',
            'perPage' => 200,
        ]);

        $services = collect($response['items'] ?? [])
            ->map(fn (array $record) => Service::fromRecord($record));

        return view('welcome', compact('services'));
    }
}
