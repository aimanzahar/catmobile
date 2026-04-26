<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(): AnonymousResourceCollection
    {
        $response = $this->client->listRecords('cg_services', null, [
            'filter' => 'is_active=true',
            'sort' => 'sort_order',
            'perPage' => 200,
        ]);

        $services = collect($response['items'] ?? [])
            ->map(fn (array $record) => Service::fromRecord($record));

        return ServiceResource::collection($services);
    }
}
