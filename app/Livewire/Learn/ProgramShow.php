<?php

namespace App\Livewire\Learn;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CertificationProgram;
use App\Models\CertificationEnrollment;
use App\Models\TrainingFlag;

class ProgramShow extends Component
{
    public CertificationProgram $program;

    // Derived
    public int $total = 0;
    public int $done  = 0;

    // Next flag + media
    public ?TrainingFlag $nextFlag = null;
    public ?string $embedUrl = null;
    public ?string $pdfUrl   = null;

    public function mount(string $slug): void
    {
        $user = Auth::user();

        $this->program = CertificationProgram::query()
            ->where('slug', $slug)
            ->with(['flags' => function ($q) {
                $q->with('sessions'); // sessions supply video_url, pdf_path, goal, etc.
            }])
            ->firstOrFail();

        // Respect visibility
        abort_unless($this->program->visibleTo($user), 403);

        $this->computeProgressAndNext();
    }

    public function startOrResume(): void
    {
        $user = Auth::user();

        $enroll = CertificationEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'cert_program_id' => $this->program->id],
            ['status'  => 'in_progress']
        );

        // Seed flag_user for all flags as pending (idempotent)
        foreach ($this->program->flags as $flag) {
            DB::table('flag_user')->updateOrInsert(
                ['user_id' => $user->id, 'training_flag_id' => $flag->id],
                ['status' => DB::raw("IF(status='completed', 'completed', 'pending')")]
            );
        }

        if ($enroll->status === 'enrolled') {
            $enroll->status = 'in_progress';
            $enroll->save();
        }

        $this->computeProgressAndNext();
        $this->dispatch('toast', type: 'success', message: 'Enrollment started.');
    }

    public function markStepComplete(): void
    {
        if (!$this->nextFlag) return;

        $user = Auth::user();
        $flagId = $this->nextFlag->id;

        $row = DB::table('flag_user')
            ->where('user_id', $user->id)
            ->where('training_flag_id', $flagId)
            ->first();

        if (!$row) {
            // Ensure row exists
            DB::table('flag_user')->insert([
                'user_id' => $user->id,
                'training_flag_id' => $flagId,
                'status' => 'completed',
                'started_at' => now(),
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('flag_user')
                ->where('id', $row->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                    'started_at' => $row->started_at ?: now(),
                ]);
        }

        // If all flags completed, mark enrollment completed
        $this->computeProgressAndNext();
        if ($this->done >= $this->total && $this->total > 0) {
            CertificationEnrollment::where('user_id', $user->id)
                ->where('cert_program_id', $this->program->id)
                ->update(['status' => 'completed']);
            $this->dispatch('toast', type: 'success', message: 'Program completed 🎉');
        } else {
            $this->dispatch('toast', type: 'success', message: 'Step completed. On to the next!');
        }
    }

    public function resetProgress(): void
    {
        $user = Auth::user();

        // Reset all flags in this program
        DB::table('flag_user')
            ->where('user_id', $user->id)
            ->whereIn('training_flag_id', $this->program->flags->pluck('id'))
            ->update([
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'updated_at' => now(),
            ]);

        // Reset enrollment to in_progress if existed
        CertificationEnrollment::where('user_id', $user->id)
            ->where('cert_program_id', $this->program->id)
            ->update(['status' => 'in_progress']);

        $this->computeProgressAndNext();
        $this->dispatch('toast', type: 'success', message: 'Progress reset.');
    }

    private function computeProgressAndNext(): void
    {
        $user = Auth::user();
        $flags = $this->program->flags;

        $this->total = $flags->count();
        if ($this->total === 0) {
            $this->done = 0;
            $this->nextFlag = null;
            $this->embedUrl = null;
            $this->pdfUrl = null;
            return;
        }

        $completedIds = DB::table('flag_user')
            ->where('user_id', $user->id)
            ->whereIn('training_flag_id', $flags->pluck('id'))
            ->where('status', 'completed')
            ->pluck('training_flag_id')
            ->all();

        $this->done = count($completedIds);

        // Find first incomplete flag by program order
        $this->nextFlag = null;
        foreach ($flags as $flag) {
            if (!in_array($flag->id, $completedIds, true)) {
                $this->nextFlag = $flag;
                break;
            }
        }

        $this->embedUrl = null;
        $this->pdfUrl   = null;

        if ($this->nextFlag) {
            // Choose the first session (ordered by pivot position) to display its media
            $session = $this->nextFlag->sessions->first(); // relation already ordered
            if ($session) {
                $this->embedUrl = self::youtubeEmbedUrl($session->video_url ?? null);
                if (filled($session->pdf_path ?? null)) {
                    $this->pdfUrl = Storage::disk('public')->url($session->pdf_path);
                }
            }
        }
    }

    /** Convert various YouTube URL forms to embeddable URL */
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
        // Recompute lightweight “upcoming” list (next 2 flags)
        $flags = $this->program->flags;
        $completedIds = DB::table('flag_user')
            ->where('user_id', Auth::id())
            ->whereIn('training_flag_id', $flags->pluck('id'))
            ->where('status', 'completed')
            ->pluck('training_flag_id')
            ->all();

        $index = 0;
        foreach ($flags as $i => $f) {
            if (!in_array($f->id, $completedIds, true)) { $index = $i; break; }
            $index = $i + 1; // all done moves index past end
        }

        $upcoming = [];
        if ($index < $flags->count() - 1) {
            $upcoming = $flags->slice($index + 1, 2)->values();
        }

        return view('livewire.learn.program-show', [
            'upcoming' => $upcoming,
        ])->layout('layouts.app');
    }
}
