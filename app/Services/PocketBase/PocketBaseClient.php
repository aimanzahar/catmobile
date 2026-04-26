<?php

namespace App\Services\PocketBase;

use App\Services\PocketBase\Exceptions\PocketBaseAuthException;
use App\Services\PocketBase\Exceptions\PocketBaseException;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\Exceptions\PocketBaseValidationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PocketBaseClient
{
    private const SUPERUSER_CACHE_KEY = 'pocketbase:superuser_token';

    private const SUPERUSER_CACHE_TTL = 1800;

    private string $baseUrl;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('pocketbase.url'), '/');
        $this->timeout = (int) config('pocketbase.timeout', 15);
    }

    public function superuserToken(): string
    {
        $cached = Cache::get(self::SUPERUSER_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $email = (string) config('pocketbase.superuser_email');
        $password = (string) config('pocketbase.superuser_password');

        $response = $this->client()
            ->asJson()
            ->post($this->url('/api/collections/_superusers/auth-with-password'), [
                'identity' => $email,
                'password' => $password,
            ]);

        $this->ensureSuccess($response);

        $token = (string) $response->json('token');
        Cache::put(self::SUPERUSER_CACHE_KEY, $token, self::SUPERUSER_CACHE_TTL);

        return $token;
    }

    public function authWithPassword(string $collection, string $identity, string $password): array
    {
        $response = $this->client()
            ->asJson()
            ->post($this->url("/api/collections/{$collection}/auth-with-password"), [
                'identity' => $identity,
                'password' => $password,
            ]);

        if ($response->status() === 400 || $response->status() === 401) {
            throw new PocketBaseAuthException('Invalid credentials.', 401);
        }

        $this->ensureSuccess($response);

        return [
            'token' => $response->json('token'),
            'record' => $response->json('record'),
        ];
    }

    public function authRefresh(string $collection, string $token): array
    {
        $response = $this->client($token)
            ->withBody('{}', 'application/json')
            ->post($this->url("/api/collections/{$collection}/auth-refresh"));

        if ($response->status() === 401 || $response->status() === 403) {
            throw new PocketBaseAuthException('Token is invalid or expired.', 401);
        }

        $this->ensureSuccess($response);

        return [
            'token' => $response->json('token'),
            'record' => $response->json('record'),
        ];
    }

    public function createRecord(string $collection, array $data, ?string $token = null, array $files = []): array
    {
        if ($files !== []) {
            $request = $this->multipartRequest($this->client($token), $data, $files);
            $response = $request->post($this->url("/api/collections/{$collection}/records"));
        } else {
            $response = $this->client($token)
                ->asJson()
                ->post($this->url("/api/collections/{$collection}/records"), $data);
        }

        $this->ensureSuccess($response);

        return $response->json();
    }

    public function updateRecord(string $collection, string $id, array $data, ?string $token = null, array $files = []): array
    {
        if ($files !== []) {
            $request = $this->multipartRequest($this->client($token), $data, $files);
            $response = $request->patch($this->url("/api/collections/{$collection}/records/{$id}"));
        } else {
            $response = $this->client($token)
                ->asJson()
                ->patch($this->url("/api/collections/{$collection}/records/{$id}"), $data);
        }

        $this->ensureSuccess($response);

        return $response->json();
    }

    public function fileUrl(string $collection, string $recordId, string $filename, ?string $thumb = null): string
    {
        $url = $this->baseUrl."/api/files/{$collection}/{$recordId}/{$filename}";
        if ($thumb !== null && $thumb !== '') {
            $url .= '?thumb='.urlencode($thumb);
        }

        return $url;
    }

    /**
     * Build a Guzzle multipart body directly. We bypass Laravel's `attach()` helper because
     * it runs the multipart entry through `array_filter`, which strips falsy values like
     * `''` and `'0'` from the `contents` key and crashes Guzzle with "A 'contents' key is required".
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string, UploadedFile>  $files
     */
    private function multipartRequest(PendingRequest $request, array $fields, array $files): PendingRequest
    {
        $multipart = [];

        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }
            $multipart[] = [
                'name' => $key,
                'contents' => is_scalar($value) ? (string) $value : json_encode($value),
            ];
        }

        foreach ($files as $field => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $multipart[] = [
                'name' => $field,
                'contents' => file_get_contents($file->getRealPath()),
                'filename' => $file->getClientOriginalName() ?: ($field.'.bin'),
            ];
        }

        return $request->bodyFormat('multipart')->withOptions(['multipart' => $multipart]);
    }

    public function deleteRecord(string $collection, string $id, ?string $token = null): void
    {
        $response = $this->client($token)->delete($this->url("/api/collections/{$collection}/records/{$id}"));

        $this->ensureSuccess($response);
    }

    public function getRecord(string $collection, string $id, ?string $token = null, ?string $expand = null): array
    {
        $query = $expand ? ['expand' => $expand] : [];

        $response = $this->client($token)->get($this->url("/api/collections/{$collection}/records/{$id}"), $query);

        $this->ensureSuccess($response);

        return $response->json();
    }

    public function listRecords(string $collection, ?string $token = null, array $params = []): array
    {
        $query = array_filter([
            'page' => $params['page'] ?? null,
            'perPage' => $params['perPage'] ?? 200,
            'sort' => $params['sort'] ?? null,
            'filter' => $params['filter'] ?? null,
            'expand' => $params['expand'] ?? null,
        ], static fn ($value) => $value !== null);

        $response = $this->client($token)->get($this->url("/api/collections/{$collection}/records"), $query);

        $this->ensureSuccess($response);

        return $response->json();
    }

    private function client(?string $token = null): PendingRequest
    {
        $request = Http::timeout($this->timeout)->acceptJson();

        if ($token !== null) {
            $request = $request->withHeaders(['Authorization' => $token]);
        }

        return $request;
    }

    private function url(string $path): string
    {
        return $this->baseUrl.$path;
    }

    private function ensureSuccess(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();
        $payload = $response->json();
        $message = is_array($payload) ? ($payload['message'] ?? 'PocketBase request failed.') : 'PocketBase request failed.';

        if ($status === 404) {
            throw new PocketBaseNotFoundException($message, 404);
        }

        if ($status === 400 && is_array($payload) && ! empty($payload['data'])) {
            throw (new PocketBaseValidationException($message, 422))
                ->withErrors($this->flattenErrors($payload['data']));
        }

        if ($status === 401 || $status === 403) {
            throw new PocketBaseAuthException($message, 401);
        }

        throw new PocketBaseException($message." (HTTP {$status})", $status);
    }

    private function flattenErrors(array $data): array
    {
        $errors = [];
        foreach ($data as $field => $detail) {
            if (is_array($detail) && isset($detail['message'])) {
                $errors[$field] = [$detail['message']];
            } elseif (is_array($detail)) {
                foreach ($this->flattenErrors($detail) as $sub => $msgs) {
                    $errors["{$field}.{$sub}"] = $msgs;
                }
            }
        }

        return $errors;
    }
}
