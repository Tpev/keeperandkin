<?php
namespace App\Services;

use App\Models\Dog;
use App\Models\DogTransfer;
use App\Models\AuditLog;
use App\Mail\TransferInviteMail;
use App\Jobs\MoveDogFilesJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DogTransferService
{
    public function initiate(Dog $dog, int $fromTeamId, string $toEmail, array $opts, int $initiatorUserId): DogTransfer
    {
        // Optional: prevent multiple pending
        if ($dog->transfer()->where('status','pending')->exists()) {
            abort(422, 'A pending transfer already exists for this dog.');
        }

        $ttlDays = config('dog_transfer.ttl_days', 7);
        $rawToken = Str::random(64);
        $tokenHash = hash_hmac('sha256', $rawToken, config('app.key'));

        [$countEval, $countFiles, $countNotes] = $this->countsForSummary($dog);

        $transfer = DogTransfer::create([
            'dog_id' => $dog->id,
            'from_team_id' => $fromTeamId,
            'to_email' => $toEmail,
            'status' => 'pending',
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays($ttlDays),
            'include_private_notes' => (bool)($opts['include_private_notes'] ?? false),
            'include_adopter_pii'   => (bool)($opts['include_adopter_pii'] ?? false),
            'initiator_user_id' => $initiatorUserId,
            'count_evaluations' => $countEval,
            'count_files' => $countFiles,
            'count_notes' => $countNotes,
        ]);

        // Audit
        AuditLog::create([
            'event' => 'transfer.initiated',
            'subject_type' => DogTransfer::class,
            'subject_id' => $transfer->id,
            'actor_user_id' => $initiatorUserId,
            'actor_team_id' => $fromTeamId,
            'context' => [
                'to_email' => $toEmail,
                'options' => $opts,
                'counts' => compact('countEval','countFiles','countNotes'),
            ],
        ]);

        // Send email w/ magic link (rawToken only in email)
        Mail::to($toEmail)->send(new TransferInviteMail($transfer, $rawToken));

        return $transfer;
    }

    public function accept(DogTransfer $transfer, int $destinationTeamId, int $acceptedUserId): void
    {
        $dog = $transfer->dog;
        $fromId = $transfer->from_team_id;
        $toId   = $destinationTeamId;

        $transfer->update([
            'to_team_id' => $toId,
            'accepted_user_id' => $acceptedUserId,
            'accepted_at' => now(),
            'status' => 'accepted',
        ]);

        // Re-parent DB pointers (Dog + known children)
        $dog->update(['team_id' => $toId]);

        foreach (config('dog_transfer.reparentables', []) as $cfg) {
            $class = $cfg[0] ?? null;
            if (!$class) continue;
            $teamKey = $cfg['team_key'] ?? 'team_id';
            $dogKey  = $cfg['dog_key'] ?? 'dog_id';
            $extra   = $cfg['extra_where'] ?? null;

            $q = $class::where($dogKey, $dog->id);
            if ($extra instanceof \Closure) { $q = $extra($q); }
            $q->update([$teamKey => $toId]);
        }

        // Respect inclusion toggles
        if (!$transfer->include_private_notes && class_exists(config('dog_transfer.private_note_model'))) {
            $model = config('dog_transfer.private_note_model');
            $flag  = config('dog_transfer.private_note_flag', 'is_private');
            // Optional choice: delete or keep with old team. Safer: keep with old team and disassociate from dog.
            $model::where('dog_id', $dog->id)->where($flag, true)->update(['team_id' => $fromId]);
        }

        if (!$transfer->include_adopter_pii) {
            $piiCols = config('dog_transfer.pii_columns', []);
            $toNull = array_fill_keys($piiCols, null);
            if (!empty($toNull)) $dog->update($toNull);
        }

        // Move files (public disk), non-blocking
        MoveDogFilesJob::dispatch($dog->id, $fromId, $toId);

        // Audit
        AuditLog::create([
            'event' => 'transfer.accepted',
            'subject_type' => DogTransfer::class,
            'subject_id' => $transfer->id,
            'actor_user_id' => $acceptedUserId,
            'actor_team_id' => $toId,
        ]);
    }

    public function decline(DogTransfer $transfer, int $userId): void
    {
        $transfer->update(['status' => 'declined', 'declined_at' => now()]);
        AuditLog::create([
            'event' => 'transfer.declined',
            'subject_type' => DogTransfer::class,
            'subject_id' => $transfer->id,
            'actor_user_id' => $userId,
        ]);
    }

    public function cancel(DogTransfer $transfer, int $userId): void
    {
        $transfer->update(['status' => 'canceled', 'canceled_at' => now()]);
        AuditLog::create([
            'event' => 'transfer.canceled',
            'subject_type' => DogTransfer::class,
            'subject_id' => $transfer->id,
            'actor_user_id' => $userId,
        ]);
    }

    public function validateToken(DogTransfer $transfer, string $rawToken): bool
    {
        $hash = hash_hmac('sha256', $rawToken, config('app.key'));
        // constant-time compare
        return hash_equals($transfer->token_hash, $hash);
    }

    protected function countsForSummary(Dog $dog): array
    {
        // Adjust to your real relations
        $evals = method_exists($dog,'evaluations') ? $dog->evaluations()->count() : 0;
        $notes = method_exists($dog,'notes') ? $dog->notes()->count() : 0;

        // crude file count on public disk
        $root = config('dog_transfer.storage_root','teams');
        $dir  = $root.'/'. $dog->team_id .'/'. config('dog_transfer.dog_dir','dogs') .'/'.$dog->id;
        $files = 0;
        if (\Storage::disk('public')->exists($dir)) {
            $files = count(\Storage::disk('public')->allFiles($dir));
        }
        return [$evals, $files, $notes];
    }
}
