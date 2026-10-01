@php($current = app()->getLocale())
<div class="ag-lang" role="group" aria-label="{{ __('admin.lang.switch') }}">
    @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $name)
        <a href="{{ route('locale.switch', $code) }}" title="{{ $name }}"
           @class(['ag-lang-item', 'is-active' => $current === $code])>{{ strtoupper($code) }}</a>
    @endforeach
</div>
