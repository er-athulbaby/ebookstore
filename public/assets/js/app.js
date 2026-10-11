// Vanilla JS only — no build step, no Node. Progressive enhancement over
// plain <form> posts, so everything still works with JS disabled.
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // AJAX "Add to cart" buttons update the header badge without a full reload.
    document.querySelectorAll('form[data-ajax-cart]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                        el.textContent = data.cartItemCount;
                        el.hidden = data.cartItemCount === 0;
                    });
                    var toastEl = document.getElementById('cartToast');
                    if (toastEl && window.bootstrap) {
                        new bootstrap.Toast(toastEl).show();
                    }
                })
                .catch(function () { form.submit(); });
        });
    });

    // AJAX wishlist heart toggle.
    document.querySelectorAll('form[data-ajax-wishlist]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var btn = form.querySelector('.wishlist-btn');
                    if (btn) btn.classList.toggle('active', data.added);
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
