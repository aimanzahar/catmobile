<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MobileJumpCommand extends Command
{
    protected $signature = 'mobile:jump
                            {platform=android : Target platform (android/ios)}
                            {--port=3000 : Jump server port the device connects to}';

    protected $description = 'Detect the LAN IP, sync ASSET_URL, and start native:jump';

    public function handle(): int
    {
        $ip = $this->detectLocalIp();

        if (! $ip) {
            $this->components->error('Could not detect a local network IP. Are you on Wi-Fi?');

            return self::FAILURE;
        }

        $port = (int) $this->option('port');
        $assetUrl = "http://{$ip}:{$port}";

        $this->components->info("LAN IP: {$ip}");
        $this->components->twoColumnDetail('ASSET_URL', $assetUrl);

        $changed = $this->setEnv('ASSET_URL', $assetUrl);
        $this->callSilent('config:clear');

        if ($changed) {
            $this->components->warn('ASSET_URL changed — restart `php artisan serve` so it picks up the new value.');
        }

        return $this->call('native:jump', [
            'platform' => $this->argument('platform'),
            '--ip' => $ip,
        ]);
    }

    private function detectLocalIp(): ?string
    {
        $sock = @stream_socket_client('udp://8.8.8.8:53', $errno, $errstr, 2);

        if (! $sock) {
            return null;
        }

        $local = @stream_socket_get_name($sock, false);
        @fclose($sock);

        if (! $local) {
            return null;
        }

        $ip = explode(':', $local)[0];

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }

        if (str_starts_with($ip, '127.') || str_starts_with($ip, '169.254.')) {
            return null;
        }

        return $ip;
    }

    private function setEnv(string $key, string $value): bool
    {
        $path = base_path('.env');
        $content = file_get_contents($path);
        $pattern = "/^{$key}=.*$/m";
        $newLine = "{$key}={$value}";

        if (preg_match($pattern, $content, $matches)) {
            if ($matches[0] === $newLine) {
                return false;
            }
            $content = preg_replace($pattern, $newLine, $content);
        } else {
            $content = rtrim($content)."\n{$newLine}\n";
        }

        file_put_contents($path, $content);

        return true;
    }
}
