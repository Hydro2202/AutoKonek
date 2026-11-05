{{-- Full-screen "drive to the next page" overlay. Driven by resources/js/modules/route-transition.js. --}}
@php
    $places = [
        ['6%', 'fuel', 'Gas'],
        ['27%', 'wrench', 'Repair'],
        ['49%', 'parking', 'Parking'],
        ['71%', 'droplet', 'Car wash'],
        ['93%', 'battery', 'Battery'],
        ['115%', 'tire', 'Tires'],
    ];
@endphp
<div id="route-transition" class="rt" aria-hidden="true">
    <ul class="rt-places">
        @foreach ($places as [$left, $icon, $label])
            <li class="rt-place" style="left: {{ $left }}">
                <span class="sign size-12"><x-ui.icon :name="$icon" class="size-6" /></span>
                {{ $label }}
            </li>
        @endforeach
    </ul>

    <div class="rt-road"><span class="rt-lane"></span></div>

    {{-- Side view of a small car, facing right. Wheels spin. --}}
    <svg class="rt-car" viewBox="0 0 152 60" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 40c0-4 2-7 6-8l14-3 14-14c2-2 5-3 8-3h34c4 0 7 1 10 4l11 11 20 4c6 1 10 5 10 11v3c0 3-2 5-5 5H11c-3 0-5-2-5-5z" fill="#F28C28"/>
        <path d="M42 18c1-1 2-2 4-2h32c2 0 4 1 5 2l9 10H31z" fill="#0a1828"/>
        <path d="M66 17v11M46 18l-9 10" stroke="#F28C28" stroke-width="3"/>
        <rect x="132" y="33" width="10" height="5" rx="2" fill="#FDEBD6"/>
        <rect x="8" y="35" width="6" height="4" rx="1.5" fill="#d9761a"/>
        <g class="rt-wheel"><circle cx="38" cy="47" r="11" fill="#0a1828" stroke="#F4F6F8" stroke-width="2"/><circle cx="38" cy="47" r="4" fill="#F4F6F8"/><path d="M38 38v18M29 47h18" stroke="#F4F6F8" stroke-width="2"/></g>
        <g class="rt-wheel"><circle cx="112" cy="47" r="11" fill="#0a1828" stroke="#F4F6F8" stroke-width="2"/><circle cx="112" cy="47" r="4" fill="#F4F6F8"/><path d="M112 38v18M103 47h18" stroke="#F4F6F8" stroke-width="2"/></g>
    </svg>

    <p class="rt-label">Heading to <strong data-rt-label>the next page</strong></p>
</div>
