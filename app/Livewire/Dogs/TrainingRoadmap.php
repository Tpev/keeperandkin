<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class TrainingRoadmap extends Component
{
    public Dog $dog;

    /**
     * Static demo plan — single, linear path.
     * Add either `youtube` (full URL) OR `pdf` (public disk path) per step.
     */
    public array $plan = [];

    /** Current index (0-based) into the plan */
    public int $index = 0;

    protected function cacheKey(): string
    {
        return "dog:{$this->dog->id}:training_index";
    }

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;

        $LABEL_CC = 'Comfort & Confidence';
        $LABEL_SO = 'Sociability';
        $LABEL_TR = 'Trainability';

        // Demo plan with media
        $this->plan = [
            [
                'category' => $LABEL_CC,
                'title'    => 'Acclimation',
                'goal'     => 'Relax in kennel with handler present (2 min)',
                'youtube'  => 'https://www.youtube.com/embed/dQw4w9WgXcQ?si=RAWHCkyKh5Z4pJ0U',
            ],
            [
                'category' => $LABEL_CC,
                'title'    => 'Novel surfaces',
                'goal'     => 'Cross metal grate calmly',
                'pdf'      => 'training-modules/novel-surfaces.pdf', // public storage path (demo)
            ],
            [
                'category' => $LABEL_CC,
                'title'    => 'Startle recovery',
                'goal'     => 'Bounce-back under 5 seconds',
                'youtube'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'category' => $LABEL_SO,
                'title'    => 'Polite greetings',
                'goal'     => 'No jumping, sit to say hello',
                'pdf'      => 'training-modules/polite-greetings.pdf',
            ],
            [
                'category' => $LABEL_SO,
                'title'    => 'Handler focus',
                'goal'     => 'Hold eye contact 5 seconds',
                'youtube'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'category' => $LABEL_TR,
                'title'    => 'Place',
                'goal'     => 'Go-to-mat from 3 meters',
                'pdf'      => 'training-modules/place.pdf',
            ],
            [
                'category' => $LABEL_TR,
                'title'    => 'Loose leash',
                'goal'     => 'Walk 20 m without pulling',
                'youtube'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'category' => $LABEL_TR,
                'title'    => 'Recall (foundation)',
                'goal'     => 'Come when called (5 m)',
                'pdf'      => 'training-modules/recall-foundation.pdf',
            ],
        ];

        // Restore demo progress (24h TTL)
        $this->index = (int) Cache::get($this->cacheKey(), 0);
        $this->index = max(0, min($this->index, count($this->plan))); // clamp
    }

    public function markComplete(): void
    {
        if ($this->index < count($this->plan)) {
            $this->index++;
            Cache::put($this->cacheKey(), $this->index, now()->addDay());
            $this->dispatch('toast', type: 'success', message: 'Module completed. Next up!');
        }
    }

    public function resetPlan(): void
    {
        $this->index = 0;
        Cache::put($this->cacheKey(), $this->index, now()->addDay());
        $this->dispatch('toast', type: 'success', message: 'Training plan reset.');
    }

    /**
     * Convert a YouTube URL to an embeddable URL.
     * Handles youtu.be, watch?v=, and shorts links.
     */
    public static function youtubeEmbedUrl(?string $url): ?string
    {
        if (!$url) return null;

        // Extract ID from common forms
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
        $total    = count($this->plan);
        $done     = min($this->index, $total);
        $next     = $this->index < $total ? $this->plan[$this->index] : null;
        $upcoming = $this->index < $total ? array_slice($this->plan, $this->index + 1, 2) : [];

        // Precompute embed URL if YouTube exists
        $embedUrl = null;
        if ($next && !empty($next['youtube'])) {
            $embedUrl = self::youtubeEmbedUrl($next['youtube']);
        }

        return view('livewire.dogs.training-roadmap', compact('total', 'done', 'next', 'upcoming', 'embedUrl'));
    }
}
