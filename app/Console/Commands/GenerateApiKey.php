<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('api:key:generate')]
#[Description('Generate one frontend API key and save it to .env.')]
class GenerateApiKey extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $plainKey = 'memorial_'.Str::random(48);

        $this->writeEnvValue('FRONTEND_API_KEY', $plainKey);

        $this->info('API key generated and saved to .env as FRONTEND_API_KEY.');
        $this->line($plainKey);

        return self::SUCCESS;
    }

    private function writeEnvValue(string $key, string $value): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';
        $line = $key.'='.$value;

        if (preg_match("/^{$key}=.*$/m", $contents)) {
            $contents = preg_replace("/^{$key}=.*$/m", $line, $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL.PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);
    }
}
