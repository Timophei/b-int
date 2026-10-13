<svg class="scene" viewBox="0 0 1440 560" preserveAspectRatio="xMaxYMid slice" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#061219"/><stop offset=".55" stop-color="#0d2b3b"/><stop offset="1" stop-color="#123a4d"/>
        </linearGradient>
        <linearGradient id="water" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#174459"/><stop offset=".35" stop-color="#0c2836"/><stop offset="1" stop-color="#050f15"/>
        </linearGradient>
        <radialGradient id="glow" cx="1060" cy="250" r="330" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#8fd4f0" stop-opacity=".42"/><stop offset=".45" stop-color="#3f8fb0" stop-opacity=".16"/><stop offset="1" stop-color="#0d2b3b" stop-opacity="0"/>
        </radialGradient>
        <linearGradient id="shade" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#050f15" stop-opacity=".92"/><stop offset=".42" stop-color="#050f15" stop-opacity=".55"/><stop offset=".7" stop-color="#050f15" stop-opacity="0"/>
        </linearGradient>
        <filter id="blur-lg" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="28"/></filter>
        <filter id="blur-sm" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="3"/></filter>
        <clipPath id="above"><rect width="1440" height="352"/></clipPath>
    </defs>
    <rect width="1440" height="560" fill="url(#sky)"/>
    <rect width="1440" height="560" fill="url(#glow)"/>
    {{-- Кольцо и его свечение --}}
    <g clip-path="url(#above)">
        <circle cx="1060" cy="250" r="182" fill="none" stroke="#9fdcf5" stroke-width="10" opacity=".22" filter="url(#blur-sm)"/>
        <circle cx="1060" cy="250" r="182" fill="none" stroke="#d9f3ff" stroke-width="1.6" opacity=".9"/>
        <ellipse cx="1110" cy="240" rx="200" ry="90" fill="#cfe9f5" opacity=".18" filter="url(#blur-lg)"/>
        <ellipse cx="960" cy="320" rx="260" ry="40" fill="#bfe3f2" opacity=".16" filter="url(#blur-lg)"/>
    </g>
    {{-- Горы --}}
    <path d="M560 352 L640 318 L700 330 L780 290 L850 312 L930 280 L1010 300 L1090 268 L1170 296 L1250 276 L1330 306 L1440 288 L1440 352 Z" fill="#0f3040" opacity=".85"/>
    <path d="M620 352 L720 334 L800 322 L880 336 L960 318 L1060 340 L1150 326 L1240 338 L1340 328 L1440 336 L1440 352 Z" fill="#0a2230"/>
    {{-- Вода и отражение --}}
    <rect y="352" width="1440" height="208" fill="url(#water)"/>
    <ellipse cx="1060" cy="360" rx="240" ry="12" fill="#bfe8f8" opacity=".25" filter="url(#blur-sm)"/>
    <g stroke="#cdeefc" stroke-linecap="round">
        <line x1="1000" y1="368" x2="1120" y2="368" stroke-width="2" opacity=".55"/>
        <line x1="1018" y1="382" x2="1102" y2="382" stroke-width="2" opacity=".45"/>
        <line x1="1032" y1="398" x2="1092" y2="398" stroke-width="1.6" opacity=".4"/>
        <line x1="1010" y1="414" x2="1060" y2="414" stroke-width="1.4" opacity=".3"/>
        <line x1="1072" y1="418" x2="1116" y2="418" stroke-width="1.4" opacity=".3"/>
        <line x1="1040" y1="436" x2="1080" y2="436" stroke-width="1.2" opacity=".25"/>
        <line x1="1000" y1="456" x2="1030" y2="456" stroke-width="1" opacity=".2"/>
        <line x1="1060" y1="462" x2="1110" y2="462" stroke-width="1" opacity=".2"/>
        <line x1="878" y1="372" x2="930" y2="372" stroke-width="1" opacity=".2"/>
        <line x1="1190" y1="376" x2="1250" y2="376" stroke-width="1" opacity=".22"/>
    </g>
    <path d="M878 352 A182 182 0 0 0 1242 352" fill="none" stroke="#bfe8f8" stroke-width="1" opacity=".18" transform="translate(0 6) scale(1 .35)" transform-origin="1060 352"/>
    <rect width="1440" height="560" fill="url(#shade)"/>
</svg>
