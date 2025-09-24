<?php

namespace App\Jobs;

use App\Models\Dog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;   // <-- add this
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MoveDogFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // <-- include Dispatchable here

    public function __construct(public int $dogId, public int $fromTeamId, public int $toTeamId) {}

    public function handle(): void
    {
        $dog = Dog::find($this->dogId);
        if (!$dog) return;

        $root   = config('dog_transfer.storage_root','teams');
        $dogDir = config('dog_transfer.dog_dir','dogs');

        $disk = Storage::disk('public');
        $from = "{$root}/{$this->fromTeamId}/{$dogDir}/{$dog->id}";
        $to   = "{$root}/{$this->toTeamId}/{$dogDir}/{$dog->id}";

        if (!$disk->exists($from)) return;

        if (!$disk->exists($to)) $disk->makeDirectory($to);

        foreach ($disk->allFiles($from) as $file) {
            $new = str_replace($from, $to, $file);
            $dir = dirname($new);
            if (!$disk->exists($dir)) $disk->makeDirectory($dir);
            $disk->move($file, $new);
        }

        foreach (array_reverse($disk->allDirectories($from)) as $dir) {
            if (empty($disk->files($dir))) $disk->deleteDirectory($dir);
        }
        if (empty($disk->files($from)) && empty($disk->directories($from))) {
            $disk->deleteDirectory($from);
        }
    }
}
