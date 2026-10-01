<div class="ag-demo" x-data>
    <div class="ag-demo-head">
        <x-filament::icon icon="heroicon-o-sparkles" class="ag-demo-icon" />
        <span>{{ __('admin.login.demo_title') }}</span>
        <span class="ag-demo-lang">
            @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $name)
                <a href="{{ route('locale.switch', $code) }}" @class(['is-active' => app()->getLocale() === $code])>{{ strtoupper($code) }}</a>
            @endforeach
        </span>
    </div>
    <p class="ag-demo-text">{{ __('admin.login.demo_text') }}</p>
    <ul class="ag-demo-list">
        @foreach ([['admin@demo.com', 'admin'], ['manager@demo.com', 'manager'], ['reception@demo.com', 'reception']] as [$email, $role])
            <li>
                <button type="button" class="ag-demo-row"
                        x-on:click="$wire.set('data.email', '{{ $email }}'); $wire.set('data.password', 'password')">
                    <span class="ag-demo-role">{{ __('admin.roles.'.$role) }}</span>
                    <code>{{ $email }}</code>
                </button>
            </li>
        @endforeach
    </ul>
    <p class="ag-demo-pass">{{ __('admin.login.password') }}: <code>password</code></p>
</div>
