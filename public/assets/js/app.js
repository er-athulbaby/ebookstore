// Vanilla JS only — no build step, no Node. Progressive enhancement over
// plain <form> posts, so everything still works with JS disabled.
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // AJAX "Add to cart" buttons update the header badge without a full reload.
    document.querySelectorAll('form[data-ajax-cart]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('[type="submit"]');
            if (btn && btn.classList.contains('is-loading')) return; // ignore double-clicks while in flight
            var formData = new FormData(form);
            var original = btn ? btn.innerHTML : '';
            if (btn) {
                btn.classList.add('is-loading');
                btn.setAttribute('aria-busy', 'true');
                btn.innerHTML = '<span class="spinner-border" aria-hidden="true"></span>Adding…';
            }
            function done() {
                if (!btn) return;
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
                btn.innerHTML = original;
            }

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('add to cart failed');
                    return r.json();
                })
                .then(function (data) {
                    done();
                    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                        el.textContent = data.cartItemCount;
                        el.hidden = data.cartItemCount === 0;
                    });
                    var toastEl = document.getElementById('cartToast');
                    if (toastEl && window.bootstrap) {
                        new bootstrap.Toast(toastEl).show();
                    }
                })
                // A normal post shows the server's own message (e.g. out of stock).
                .catch(function () { done(); form.submit(); });
        });
    });

    // AJAX wishlist heart toggle — the icon fills in so the saved state is obvious.
    document.querySelectorAll('form[data-ajax-wishlist]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('.wishlist-btn');
            if (btn && btn.getAttribute('aria-busy') === 'true') return;
            if (btn) btn.setAttribute('aria-busy', 'true');
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('wishlist toggle failed');
                    return r.json();
                })
                .then(function (data) {
                    if (!btn) return;
                    btn.removeAttribute('aria-busy');
                    btn.classList.toggle('active', data.added);
                    btn.setAttribute('aria-pressed', data.added ? 'true' : 'false');
                    var icon = btn.querySelector('i');
                    if (icon) icon.className = data.added ? 'bi bi-heart-fill' : 'bi bi-heart';
                })
                .catch(function () { form.submit(); });
        });
    });

    // Quantity steppers on cart page — submit the enclosing form on change.
    document.querySelectorAll('[data-qty-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            input.form.submit();
        });
    });

    // Variant picker on book detail page updates the price + form field.
    document.querySelectorAll('[data-variant-radio]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var price = radio.getAttribute('data-price');
            var priceEl = document.querySelector('[data-selected-price]');
            if (priceEl) priceEl.textContent = price;
        });
    });
});
