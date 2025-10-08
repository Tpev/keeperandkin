<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use App\Models\DogTrainingAssignment;
use App\Models\TrainingSession;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TrainingRoadmap extends Component
{
    public Dog $dog;

    /** @var \Illuminate\Support\Collection<DogTrainingAssignment> */
    public $items;

    /** index of the first incomplete item (computed) */
    public int $index = 0;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
        $this->reload();
    }

    private function orderByCategoryThenId()
    {
        // Preferred ordering: CC -> SO -> TR, then by id
        $order = ['comfort_confidence' => 1, 'sociability' => 2, 'trainability' => 3];

        return function ($a, $b) use ($order) {
            $ac = $a->session?->category ?? 'trainability';
            $bc = $b->session?->category ?? 'trainability';
            $aw = $order[$ac] ?? 99;
            $bw = $order[$bc] ?? 99;
            if ($aw === $bw) {
                return ($a->id <=> $b->id);
            }
            return $aw <=> $bw;
        };
    }

    public function reload(): void
    {
        $this->items = DogTrainingAssignment::query()
            ->where('dog_id', $this->dog->id)
            ->with(['session']) // session has: category, name, video_url, pdf_path, goal (optional)
            ->orderBy('id')     // no position column on this model; stable by id
            ->get()
            ->sort($this->orderByCategoryThenId())
            ->values();

        // first incomplete index
        $this->index = 0;
        foreach ($this->items as $i => $it) {
            if ($it->status !== 'completed') {
                $this->index = $i;
                return;
            }
        }
        // all complete → index to count (past-the-end)
        $this->index = $this->items->count();
    }

    public function markComplete(): void
    {
        if ($this->index >= $this->items->count()) {
            return;
        }

        /** @var DogTrainingAssignment|null $item */
        $item = $this->items[$this->index] ?? null;
        if (!$item) {
            return;
        }

        if (!$item->started_at) {
            $item->started_at = Carbon::now();
        }
        $item->status = 'completed';
        $item->completed_at = Carbon::now();
        $item->save();

        $this->reload();
        $this->dispatch('toast', type: 'success', message: 'Module completed. Next up!');
    }

    public function resetPlan(): void
    {
        DogTrainingAssignment::where('dog_id', $this->dog->id)->update([
            'status'       => 'pending',
            'started_at'   => null,
            'completed_at' => null,
        ]);
        $this->reload();
        $this->dispatch('toast', type: 'success', message: 'Training plan reset.');
    }

    /**
     * Generate a program for this dog if none exists.
     * This uses ALL TrainingSession records, ordered by category then id.
     * Replace this with your smarter builder if desired (e.g., from flags).
     */
    public function generateProgram(): void
    {
        if (DogTrainingAssignment::where('dog_id', $this->dog->id)->exists()) {
            $this->dispatch('toast', type: 'info', message: 'Program already exists for this dog.');
            return;
        }

        $sessions = TrainingSession::query()->get()->sort(function ($a, $b) {
            $order = ['comfort_confidence' => 1, 'sociability' => 2, 'trainability' => 3];
            $aw = $order[$a->category] ?? 99;
            $bw = $order[$b->category] ?? 99;
            if ($aw === $bw) return $a->id <=> $b->id;
            return $aw <=> $bw;
        });

        foreach ($sessions as $s) {
            DogTrainingAssignment::create([
                'dog_id'              => $this->dog->id,
                'training_session_id' => $s->id,
                'training_flag_id'    => null,    // optional: fill if you’re tying to a specific flag
                'evaluation_id'       => null,    // optional: fill if coming from a specific evaluation
                'status'              => 'pending',
                'started_at'          => null,
                'completed_at'        => null,
                'notes'               => null,
            ]);
        }

        $this->reload();
        $this->dispatch('toast', type: 'success', message: 'Program generated.');
    }

    /**
     * Convert a YouTube URL to an embeddable URL.
     */
    public static function youtubeEmbedUrl(?string $url): ?string
    {
        if (!$url) return null;

        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{6,})~i',
            '~youtube\.com/watch\?v=([A-Za-z0-9_-]{6,})~i',
            '~youtube\.com/embed/([A-Za-z0-9_-]{6,})~i',
            '~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~i',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
            }
        }
        return null;
    }

    public function render()
    {
        $total = $this->items->count();
        $done  = $this->items->where('status', 'completed')->count();

        $nextAssignment = ($this->index < $total) ? $this->items[$this->index] : null;

        // next 2 upcoming, if any
        $upcoming = [];
        if ($this->index < $total - 1) {
            $slice = $this->items->slice($this->index + 1, 2);
            $upcoming = $slice->values()->all();
        }

        // Precompute embed URL for YouTube
        $embedUrl = null;
        if ($nextAssignment && $nextAssignment->session && filled($nextAssignment->session->video_url)) {
            $embedUrl = self::youtubeEmbedUrl($nextAssignment->session->video_url);
        }

        return view('livewire.dogs.training-roadmap', compact('total', 'done', 'nextAssignment', 'upcoming', 'embedUrl'));
    }
}
