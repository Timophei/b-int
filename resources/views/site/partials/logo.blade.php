{{-- Логотип: знак — файл из фирменных материалов, название набрано текстом в цвет знака --}}
<a @class(['logo', 'logo-footer' => $footer ?? false]) href="/" aria-label="{{ $settings->company_name }} — на главную">
    <img src="/img/logo-mark.png" alt="" width="85" height="92">
    <span class="logo-name">Байкал<br>Интеграция</span>
</a>
