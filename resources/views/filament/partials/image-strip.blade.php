@if (count($urls))
    <div class="ag-image-strip">
        @foreach ($urls as $i => $url)
            @if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL))
                <figure>
                    <img src="{{ $url }}" alt="" loading="lazy" onerror="this.closest('figure').classList.add('is-broken')">
                    <figcaption>{{ $i === 0 ? __('admin.room_type.cover') : '#'.($i + 1) }}</figcaption>
                </figure>
            @endif
        @endforeach
    </div>
@endif
