{{-- Hidden until resources/js/modules/cookie-consent.js finds no saved choice. --}}
<section id="cookie-banner" data-cookie="{{ config('autokonek.consent_cookie') }}" hidden aria-labelledby="cookie-title"
         class="fixed inset-x-3 bottom-3 z-50 animate-banner-in rounded-lg border border-line bg-white p-5 shadow-2xl shadow-navy/20 sm:right-auto sm:bottom-5 sm:left-5 sm:max-w-md">
    <div class="flex items-start gap-3">
        <span class="sign size-10"><x-ui.icon name="cookie" class="size-5" /></span>
        <div class="min-w-0">
            <h2 id="cookie-title" class="text-xl">Cookies on AutoKonek</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-ink/85">
                We only use the cookies needed to keep you signed in and to protect our forms. There are no advertising or tracking cookies.
                <a href="{{ route('legal.cookies') }}" class="font-semibold text-steel underline underline-offset-2 hover:text-navy">Read the Cookie Policy</a>.
            </p>
            <p data-cookie-status hidden class="mt-2 text-sm text-success-700">You have accepted essential cookies on this browser.</p>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
        <button type="button" data-cookie-accept data-no-loading class="btn-primary">Accept essential cookies</button>
        <button type="button" data-cookie-withdraw data-no-loading hidden class="btn-outline">Withdraw choice</button>
    </div>
</section>
