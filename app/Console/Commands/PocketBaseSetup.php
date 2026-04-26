<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PocketBaseSetup extends Command
{
    protected $signature = 'pocketbase:setup {--force-recreate : Delete and recreate all cg_ collections}';

    protected $description = 'Provision PocketBase collections (cg_users, cg_pets, cg_services, cg_time_slots, cg_bookings, cg_taxi_requests) and seed services.';

    private string $url;

    private string $token;

    public function handle(): int
    {
        $this->url = rtrim((string) config('pocketbase.url'), '/');
        if ($this->url === '') {
            $this->error('POCKETBASE_URL is not configured.');

            return self::FAILURE;
        }

        $this->info("Authenticating superuser at {$this->url}...");
        $auth = Http::timeout((int) config('pocketbase.timeout', 15))
            ->acceptJson()
            ->asJson()
            ->post("{$this->url}/api/collections/_superusers/auth-with-password", [
                'identity' => config('pocketbase.superuser_email'),
                'password' => config('pocketbase.superuser_password'),
            ]);

        if (! $auth->successful()) {
            $this->error('Superuser auth failed: '.$auth->body());

            return self::FAILURE;
        }

        $this->token = $auth->json('token');
        $this->info('✓ Authenticated');

        if ($this->option('force-recreate')) {
            $this->deleteExisting();
        }

        $this->upsertCollection($this->usersCollection());
        $this->upsertCollection($this->servicesCollection());
        $this->upsertCollection($this->timeSlotsCollection());
        $this->upsertCollection($this->petsCollection());
        $this->upsertCollection($this->bookingsCollection());
        $this->upsertCollection($this->taxiRequestsCollection());

        $this->seedServices();

        $this->info('');
        $this->info('All done.');

        return self::SUCCESS;
    }

    private function deleteExisting(): void
    {
        $names = ['cg_taxi_requests', 'cg_bookings', 'cg_time_slots', 'cg_services', 'cg_pets', 'cg_users'];
        foreach ($names as $name) {
            $res = $this->request('delete', "/api/collections/{$name}");
            if ($res->status() === 204) {
                $this->line("  deleted {$name}");
            }
        }
    }

    private function upsertCollection(array $definition): void
    {
        $name = $definition['name'];
        $existing = $this->request('get', "/api/collections/{$name}");

        if ($existing->status() === 404) {
            $res = $this->request('post', '/api/collections', $definition);
            if ($res->successful()) {
                $this->info("✓ Created collection {$name}");
            } else {
                $this->error("✗ Failed to create {$name}: ".$res->body());
            }

            return;
        }

        if (! $existing->successful()) {
            $this->error("✗ Failed to read {$name}: ".$existing->body());

            return;
        }

        $this->ensureMissingFields($name, $existing->json('fields') ?? [], $definition['fields'] ?? []);
    }

    private function ensureMissingFields(string $collection, array $currentFields, array $desiredFields): void
    {
        $currentByName = [];
        foreach ($currentFields as $field) {
            if (isset($field['name'])) {
                $currentByName[$field['name']] = $field;
            }
        }

        $missing = [];
        foreach ($desiredFields as $field) {
            if (! isset($field['name'])) {
                continue;
            }
            if (! isset($currentByName[$field['name']])) {
                $missing[] = $field;
            }
        }

        if ($missing === []) {
            $this->line("• Collection {$collection} already exists");
            return;
        }

        $merged = array_merge($currentFields, $missing);
        $res = $this->request('patch', "/api/collections/{$collection}", ['fields' => $merged]);
        if ($res->successful()) {
            $names = implode(', ', array_map(fn ($f) => $f['name'], $missing));
            $this->info("✓ Added fields to {$collection}: {$names}");
        } else {
            $this->error("✗ Failed to patch {$collection}: ".$res->body());
        }
    }

    private function seedServices(): void
    {
        $services = [
            [
                'name' => 'Basic Grooming',
                'slug' => 'basic-grooming',
                'description' => "Essential grooming package including bath, blow dry, nail trimming, ear cleaning, and light brushing. Perfect for regular maintenance of your cat's hygiene.",
                'price' => 30.00,
                'duration_minutes' => 45,
                'icon' => 'scissors',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Full Grooming',
                'slug' => 'full-grooming',
                'description' => 'Complete grooming experience with bath, full haircut & styling, nail trimming, ear cleaning, teeth brushing, and de-shedding treatment.',
                'price' => 60.00,
                'duration_minutes' => 90,
                'icon' => 'sparkles',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Spa Package',
                'slug' => 'spa-package',
                'description' => 'Premium pampering session featuring everything in Full Grooming plus aromatherapy bath, deep conditioning, paw massage, and premium shampoo treatment.',
                'price' => 90.00,
                'duration_minutes' => 120,
                'icon' => 'heart',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pet Taxi',
                'slug' => 'pet-taxi',
                'description' => 'Convenient door-to-door pickup and drop-off service for your cat. Our trained drivers ensure safe and comfortable transportation.',
                'price' => 25.00,
                'duration_minutes' => 30,
                'icon' => 'truck',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        $this->info('');
        $this->info('Seeding services...');

        foreach ($services as $service) {
            $existing = $this->request('get', "/api/collections/cg_services/records?filter=".urlencode("slug='{$service['slug']}'"));
            if ($existing->successful() && ($existing->json('totalItems') ?? 0) > 0) {
                $this->line("• Service {$service['slug']} already exists");

                continue;
            }

            $res = $this->request('post', '/api/collections/cg_services/records', $service);
            if ($res->successful()) {
                $this->info("✓ Created service {$service['slug']}");
            } else {
                $this->error("✗ Failed to seed {$service['slug']}: ".$res->body());
            }
        }
    }

    private function request(string $method, string $path, array $body = []): Response
    {
        $req = Http::timeout((int) config('pocketbase.timeout', 15))
            ->withToken($this->token)
            ->acceptJson();

        return match ($method) {
            'get' => $req->get($this->url.$path),
            'post' => $req->asJson()->post($this->url.$path, $body),
            'patch' => $req->asJson()->patch($this->url.$path, $body),
            'delete' => $req->delete($this->url.$path),
        };
    }

    private function usersCollection(): array
    {
        return [
            'name' => 'cg_users',
            'type' => 'auth',
            'listRule' => 'id = @request.auth.id',
            'viewRule' => 'id = @request.auth.id',
            'createRule' => '',
            'updateRule' => 'id = @request.auth.id',
            'deleteRule' => 'id = @request.auth.id',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'required' => true,
                    'max' => 255,
                ],
                [
                    'name' => 'avatar',
                    'type' => 'file',
                    'maxSelect' => 1,
                    'maxSize' => 5_242_880,
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                ],
            ],
        ];
    }

    private function petsCollection(): array
    {
        return [
            'name' => 'cg_pets',
            'type' => 'base',
            'listRule' => 'user = @request.auth.id',
            'viewRule' => 'user = @request.auth.id',
            'createRule' => 'user = @request.auth.id',
            'updateRule' => 'user = @request.auth.id',
            'deleteRule' => 'user = @request.auth.id',
            'fields' => [
                [
                    'name' => 'user',
                    'type' => 'relation',
                    'required' => true,
                    'collectionId' => $this->collectionId('cg_users'),
                    'cascadeDelete' => true,
                    'maxSelect' => 1,
                ],
                ['name' => 'name', 'type' => 'text', 'required' => true, 'max' => 255],
                ['name' => 'breed', 'type' => 'text', 'max' => 255],
                ['name' => 'age', 'type' => 'number'],
                ['name' => 'weight', 'type' => 'number'],
                ['name' => 'special_notes', 'type' => 'text'],
                ['name' => 'image', 'type' => 'file', 'maxSelect' => 1, 'maxSize' => 5_242_880, 'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif']],
            ],
        ];
    }

    private function servicesCollection(): array
    {
        return [
            'name' => 'cg_services',
            'type' => 'base',
            'listRule' => '',
            'viewRule' => '',
            'createRule' => null,
            'updateRule' => null,
            'deleteRule' => null,
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'max' => 255],
                ['name' => 'slug', 'type' => 'text', 'required' => true, 'max' => 255],
                ['name' => 'description', 'type' => 'text'],
                ['name' => 'price', 'type' => 'number', 'required' => true],
                ['name' => 'duration_minutes', 'type' => 'number'],
                ['name' => 'icon', 'type' => 'text', 'max' => 255],
                ['name' => 'image', 'type' => 'file', 'maxSelect' => 1, 'maxSize' => 5_242_880],
                ['name' => 'is_active', 'type' => 'bool'],
                ['name' => 'sort_order', 'type' => 'number'],
            ],
            'indexes' => [
                'CREATE UNIQUE INDEX `idx_cg_services_slug` ON `cg_services` (`slug`)',
            ],
        ];
    }

    private function timeSlotsCollection(): array
    {
        return [
            'name' => 'cg_time_slots',
            'type' => 'base',
            'listRule' => '',
            'viewRule' => '',
            'createRule' => null,
            'updateRule' => null,
            'deleteRule' => null,
            'fields' => [
                ['name' => 'date', 'type' => 'date'],
                ['name' => 'start_time', 'type' => 'text', 'max' => 5],
                ['name' => 'end_time', 'type' => 'text', 'max' => 5],
                ['name' => 'is_available', 'type' => 'bool'],
                ['name' => 'max_bookings', 'type' => 'number'],
            ],
            'indexes' => [
                'CREATE INDEX `idx_cg_time_slots_date_avail` ON `cg_time_slots` (`date`, `is_available`)',
            ],
        ];
    }

    private function bookingsCollection(): array
    {
        return [
            'name' => 'cg_bookings',
            'type' => 'base',
            'listRule' => 'user = @request.auth.id',
            'viewRule' => 'user = @request.auth.id',
            'createRule' => 'user = @request.auth.id',
            'updateRule' => 'user = @request.auth.id',
            'deleteRule' => 'user = @request.auth.id',
            'fields' => [
                ['name' => 'user', 'type' => 'relation', 'required' => true, 'collectionId' => $this->collectionId('cg_users'), 'cascadeDelete' => true, 'maxSelect' => 1],
                ['name' => 'pet', 'type' => 'relation', 'required' => true, 'collectionId' => $this->collectionId('cg_pets'), 'cascadeDelete' => true, 'maxSelect' => 1],
                ['name' => 'service', 'type' => 'relation', 'required' => true, 'collectionId' => $this->collectionId('cg_services'), 'cascadeDelete' => false, 'maxSelect' => 1],
                ['name' => 'time_slot', 'type' => 'relation', 'required' => true, 'collectionId' => $this->collectionId('cg_time_slots'), 'cascadeDelete' => false, 'maxSelect' => 1],
                ['name' => 'status', 'type' => 'select', 'maxSelect' => 1, 'values' => ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled']],
                ['name' => 'payment_method', 'type' => 'select', 'maxSelect' => 1, 'values' => ['online', 'cash']],
                ['name' => 'payment_status', 'type' => 'select', 'maxSelect' => 1, 'values' => ['unpaid', 'paid', 'refunded']],
                ['name' => 'total_price', 'type' => 'number'],
                ['name' => 'notes', 'type' => 'text'],
            ],
        ];
    }

    private function taxiRequestsCollection(): array
    {
        return [
            'name' => 'cg_taxi_requests',
            'type' => 'base',
            'listRule' => 'booking.user = @request.auth.id',
            'viewRule' => 'booking.user = @request.auth.id',
            'createRule' => 'booking.user = @request.auth.id',
            'updateRule' => 'booking.user = @request.auth.id',
            'deleteRule' => 'booking.user = @request.auth.id',
            'fields' => [
                ['name' => 'booking', 'type' => 'relation', 'required' => true, 'collectionId' => $this->collectionId('cg_bookings'), 'cascadeDelete' => true, 'maxSelect' => 1],
                ['name' => 'pickup_address', 'type' => 'text', 'required' => true],
                ['name' => 'status', 'type' => 'select', 'maxSelect' => 1, 'values' => ['pending', 'approved', 'rejected', 'scheduled', 'completed']],
                ['name' => 'scheduled_at', 'type' => 'date'],
            ],
        ];
    }

    private function collectionId(string $name): ?string
    {
        $res = $this->request('get', "/api/collections/{$name}");
        if (! $res->successful()) {
            return null;
        }

        return $res->json('id');
    }
}
