{{-- resources/views/livewire/dogs/table.blade.php --}}
<div>
    {{-- Make every row clickable --}}
    <x-ts-table
        :$headers
        :$rows
        link="{{ url('/dogs/{id}') }}"  {{-- {id} will be swapped per row --}}
        striped
        paginate
        id="dogs">

        {{-- Action buttons column --}}
        @interact('column_action', $row)
            {{-- 👁 View --}}
            <x-ts-button.circle
                color="gray"
                icon="eye"
                href="{{ route('dogs.show', $row) }}"
                title="View" />

            {{-- ✏️ Edit --}}
            <x-ts-button.circle
                color="blue"
                icon="pencil"
                href="{{ route('dogs.edit', $row) }}"
                title="Edit" />
        @endinteract
    </x-ts-table>
</div>
