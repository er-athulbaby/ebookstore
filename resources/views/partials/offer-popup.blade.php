{{-- Timed offer popup, configured in Admin → Storefront → Offer Popup. --}}
@php
    $popupOn = \App\Models\Setting::get('popup_enabled') === '1' && \App\Models\Setting::get('popup_title')
        && ! request()->routeIs('cart.*', 'checkout.*', 'login', 'register', 'order.*');
@endphp
@if($popupOn)
    @php
        $popupUrl = \App\Models\Setting::get('popup_button_url');
        if ($popupUrl && str_starts_with($popupUrl, '/')) $popupUrl = url($popupUrl); // keep /demo in subfolder installs
        $popupImage = \App\Models\Setting::get('popup_image');
    @endphp
    <div class="modal fade" id="offerPopup" tabindex="-1" aria-labelledby="offerPopupTitle" aria-hidden="true"
         data-delay="{{ (int) \App\Models\Setting::get('popup_delay', 30) }}" data-repeat-days="{{ (int) \App\Models\Setting::get('popup_repeat_days', 1) }}"
         data-version="{{ \App\Models\Setting::get('popup_version', '1') }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content offer-popup">
                <button type="button" class="btn-close offer-popup-close" data-bs-dismiss="modal" aria-label="Close"></button>
                @if($popupImage)
                    <img src="{{ asset('storage/'.$popupImage) }}" alt="" class="offer-popup-img">
                @endif
                <div class="offer-popup-body">
                    <h2 class="offer-popup-title" id="offerPopupTitle">{{ \App\Models\Setting::get('popup_title') }}</h2>
                    @if(\App\Models\Setting::get('popup_message'))
                        <p class="offer-popup-text">{{ \App\Models\Setting::get('popup_message') }}</p>
                    @endif
                    @if($code = \App\Models\Setting::get('popup_coupon'))
                        <div class="offer-popup-code">
                            <span>{{ $code }}</span>
                            <button type="button" class="btn btn-sm btn-outline-brand" data-copy="{{ $code }}">Copy code</button>
                        </div>
                    @endif
                    @if($popupUrl)
                        <a href="{{ $popupUrl }}" class="btn btn-brand w-100">{{ \App\Models\Setting::get('popup_button_text') ?: 'Shop now' }}</a>
                    @else
                        <button type="button" class="btn btn-brand w-100" data-bs-dismiss="modal">{{ \App\Models\Setting::get('popup_button_text') ?: 'Got it' }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var el = document.getElementById('offerPopup');
        if (!el || !window.bootstrap) return;
        var delay = +el.dataset.delay * 1000, repeatDays = +el.dataset.repeatDays, version = el.dataset.version;
        var store = function (s) { try { return window[s]; } catch (e) { return null; } };
        var local = store('localStorage'), session = store('sessionStorage');
        var get = function (s, k) { try { return s && s.getItem(k); } catch (e) { return null; } };
        var set = function (s, k, v) { try { s && s.setItem(k, v); } catch (e) {} };

        // Already seen this version? Hide for repeatDays (0 = until the browser tab session ends).
        var seenAt = +get(local, 'rcOfferSeen:' + version) || 0;
        if (repeatDays > 0 && Date.now() - seenAt < repeatDays * 864e5) return;
        if (repeatDays === 0 && get(session, 'rcOfferSeen:' + version)) return;

        // Browsing time adds up across pages within this visit.
        var spent = +get(session, 'rcOfferTime') || 0, startedAt = Date.now();
        window.addEventListener('pagehide', function () { set(session, 'rcOfferTime', spent + (Date.now() - startedAt)); });

        setTimeout(function () {
            if (document.querySelector('.modal.show')) return; // don't stack on another open dialog
            bootstrap.Modal.getOrCreateInstance(el).show();
            set(local, 'rcOfferSeen:' + version, Date.now());
            set(session, 'rcOfferSeen:' + version, '1');
        }, Math.max(0, delay - spent));

        var copy = el.querySelector('[data-copy]');
        if (copy) copy.addEventListener('click', function () {
            var done = function () { copy.textContent = 'Copied!'; };
            if (navigator.clipboard) navigator.clipboard.writeText(copy.dataset.copy).then(done, done); else done();
        });
    })();
    </script>
@endif
