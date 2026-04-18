<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('api:key:generate {name=frontend : Friendly name for this API key}')]
#[Description('Generate a frontend API key and store only its SHA-256 hash.')]
class GenerateApiKey extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $plainKey = 'memorial_'.Str::random(48);

        ApiKey::query()->create([
            'name' => $this->argument('name'),
            'key_hash' => hash('sha256', $plainKey),
        ]);

        $this->info('API key generated. Save this value now; it will not be shown again.');
        $this->line($plainKey);

        return self::SUCCESS;
    }
}
