<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Dog;
use App\Models\CareNote;
use Illuminate\Support\Carbon;

class CareNotes extends Component
{
    public Dog $dog;

    // Form inputs
    public ?string $author_name = null;
    public ?string $body = null;
    public bool $pin = false;

    // UI state
    public bool $showNotes = false;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog->load('careNotes');

        // Prefill author_name from current user if available
        $this->author_name = $this->author_name ?? (Auth::user()->name ?? null);
    }

    public function addNote(): void
    {
        $data = $this->validate([
            'author_name' => ['nullable', 'string', 'max:120'],
            'body'        => ['required', 'string', 'min:2'],
            'pin'         => ['boolean'],
        ]);

        // Create note
        $note = CareNote::create([
            'dog_id'      => $this->dog->id,
            'user_id'     => Auth::id(),
            'author_name' => $data['author_name'] ?? (Auth::user()->name ?? null),
            'body'        => $data['body'],
            'pinned_at'   => $this->pin ? now() : null,
        ]);

        // Ensure only one pinned note per dog
        if ($this->pin) {
            CareNote::forDog($this->dog->id)
                ->where('id', '!=', $note->id)
                ->update(['pinned_at' => null]);
        }

        // Reset form
        $this->body = null;
        $this->pin = false;

        // Refresh
        $this->dog->load('careNotes');
        $this->showNotes = true;

        $this->dispatch('toast', type: 'success', message: 'Note added.');
    }

    public function pin(int $noteId): void
    {
        $note = CareNote::forDog($this->dog->id)->findOrFail($noteId);

        // Pin this one, unpin others
        CareNote::forDog($this->dog->id)->update(['pinned_at' => null]);
        $note->update(['pinned_at' => now()]);

        $this->dog->load('careNotes');
        $this->dispatch('toast', type: 'success', message: 'Note pinned.');
    }

    public function unpin(int $noteId): void
    {
        $note = CareNote::forDog($this->dog->id)->findOrFail($noteId);
        $note->update(['pinned_at' => null]);

        $this->dog->load('careNotes');
        $this->dispatch('toast', type: 'success', message: 'Note unpinned.');
    }

    public function delete(int $noteId): void
    {
        $note = CareNote::forDog($this->dog->id)->findOrFail($noteId);
        $note->delete();

        $this->dog->load('careNotes');
        $this->dispatch('toast', type: 'success', message: 'Note deleted.');
    }

    public function render()
    {
        // Build datasets
        $pinned = $this->dog->careNotes->filter(fn ($n) => !is_null($n->pinned_at))
                     ->sortByDesc('pinned_at')->first();

        $notes = $this->dog->careNotes
            ->when($pinned, fn($c) => $c->where('id', '!=', $pinned->id))
            ->take(20); // simple limit

        return view('livewire.dogs.care-notes', [
            'pinned' => $pinned,
            'notes'  => $notes,
        ]);
    }
}
