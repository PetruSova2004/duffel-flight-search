@props(['name', 'id' => null])

@error($name)
    <p @if ($id) id="{{ $id }}" @endif class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
@enderror
