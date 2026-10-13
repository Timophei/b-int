#!/usr/bin/env bash
#
# Установка и обновление сайта b-integration.ru на сервере. Запускать от root:
#
#   bash install.sh                          # основной домен b-integration.ru
#   DOMAIN=t.b-integration.ru bash install.sh # тестовый (закрыт от поисковиков)
#
# Повторный запуск обновляет код и базу, ничего не затирая: .env, база и загруженные
# картинки остаются. Другие сайты на сервере не трогаются — у сайта свой файл nginx.
# Когда DNS домена смотрит на этот сервер, скрипт сам выпускает сертификат и включает HTTPS.
set -euo pipefail

DOMAIN="${DOMAIN:-b-integration.ru}"
APP_DIR="${APP_DIR:-/var/www/b-integration}"
REPO="${REPO:-https://github.com/Timophei/b-int.git}"
BRANCH="${BRANCH:-main}"
PHP_SOCK="${PHP_SOCK:-/run/php/php8.3-fpm.sock}"
WEB_USER="${WEB_USER:-www-data}"
SKIP_NGINX="${SKIP_NGINX:-0}"
SKIP_SSL="${SKIP_SSL:-0}"
MAIN_DOMAIN="b-integration.ru"
NOINDEX="${NOINDEX:-$([ "$DOMAIN" = "$MAIN_DOMAIN" ] && echo 0 || echo 1)}"

say() { printf '\n== %s\n' "$*"; }
die() { printf '\nОШИБКА: %s\n' "$*" >&2; exit 1; }

[ "$(id -u)" = 0 ] || die "запустите от root"

say "Проверяю, что на сервере есть всё нужное"
command -v php >/dev/null || die "не найден php"
command -v git >/dev/null || die "не найден git: apt install git"
command -v composer >/dev/null || die "не найден composer: apt install composer"
php_modules="$(php -m)"
missing=()
for ext in intl mbstring xml curl zip gd pdo_sqlite fileinfo; do
    grep -qix "$ext" <<<"$php_modules" || missing+=("$ext")
done
if [ ${#missing[@]} -gt 0 ]; then
    die "в PHP не хватает расширений: ${missing[*]}. Поставьте: apt install php8.3-intl php8.3-sqlite3 php8.3-gd php8.3-zip php8.3-xml php8.3-mbstring php8.3-curl && systemctl restart php8.3-fpm"
fi

say "Код: $APP_DIR (ветка $BRANCH)"
if [ -d "$APP_DIR/.git" ]; then
    git -C "$APP_DIR" fetch --quiet origin "$BRANCH"
    git -C "$APP_DIR" checkout --quiet "$BRANCH"
    git -C "$APP_DIR" reset --quiet --hard "origin/$BRANCH"
elif [ -e "$APP_DIR" ] && [ -n "$(ls -A "$APP_DIR")" ]; then
    die "папка $APP_DIR уже занята. Укажите другую: APP_DIR=/var/www/другая bash install.sh"
else
    git clone --quiet --branch "$BRANCH" "$REPO" "$APP_DIR"
fi
cd "$APP_DIR"

say "Зависимости"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --quiet

first_install=0
if [ ! -f .env ]; then
    say "Первая установка: создаю .env и базу"
    first_install=1
    cp .env.example .env
    sed -i \
        -e 's|^APP_ENV=.*|APP_ENV=production|' \
        -e 's|^APP_DEBUG=.*|APP_DEBUG=false|' \
        -e "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" \
        -e 's|^LOG_LEVEL=.*|LOG_LEVEL=warning|' .env
    php artisan key:generate --force --no-interaction
fi
[ -f database/database.sqlite ] || touch database/database.sqlite

say "База данных"
php artisan migrate --force --no-interaction
# Стартовые тексты раздела добавляются, только если такого раздела ещё нет
php artisan db:seed --force --no-interaction

say "Папка с картинками и кэш"
php artisan storage:link --force --no-interaction >/dev/null
php artisan optimize --no-interaction >/dev/null
php artisan filament:optimize --no-interaction >/dev/null 2>&1 || true
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache database

# Собирает файл nginx из шаблона: $1 — шаблон, $2 — имя сертификата (для HTTPS)
render_nginx() {
    local noindex_line=""
    [ "$NOINDEX" = 1 ] && noindex_line='add_header X-Robots-Tag "noindex, nofollow" always;'
    python3 - "$1" deploy/nginx.body.conf "$DOMAIN" "$APP_DIR" "$PHP_SOCK" "$noindex_line" "${2:-}" <<'PY'
import sys
tpl, body, domain, app_dir, sock, noindex, cert = sys.argv[1:8]
text = open(tpl, encoding='utf-8').read().replace('__BODY__', open(body, encoding='utf-8').read().rstrip('\n'))
for k, v in {'__DOMAIN__': domain, '__APP_DIR__': app_dir, '__PHP_SOCK__': sock, '__NOINDEX__': noindex, '__CERT__': cert}.items():
    text = text.replace(k, v)
sys.stdout.write(text)
PY
}

# Ставит файл nginx; если nginx его не принимает — возвращает прежний
install_nginx() {
    local new="$1" conf="/etc/nginx/sites-available/$DOMAIN"
    local backup=""
    [ -f "$conf" ] && backup="$(mktemp)" && cp "$conf" "$backup"
    cp "$new" "$conf"
    ln -sf "$conf" "/etc/nginx/sites-enabled/$DOMAIN"
    if nginx -t 2>/tmp/bint-nginx-test.log; then
        systemctl reload nginx
        return 0
    fi
    cat /tmp/bint-nginx-test.log >&2
    if [ -n "$backup" ]; then cp "$backup" "$conf"; else rm -f "/etc/nginx/sites-enabled/$DOMAIN" "$conf"; fi
    nginx -t >/dev/null 2>&1 && systemctl reload nginx
    return 1
}

points_here() {
    # Домен смотрит на этот сервер, если его A-запись — один из адресов сервера
    local ip; ip="$(getent ahostsv4 "$1" | awk 'NR==1{print $1}')"
    [ -n "$ip" ] && hostname -I | tr ' ' '\n' | grep -qx "$ip" && return 0
    # За NAT адрес сервера не виден в hostname -I — сверяем с внешним
    [ -n "$ip" ] && [ "$ip" = "$(curl -fs4 --max-time 5 https://ifconfig.me 2>/dev/null || true)" ]
}

if [ "$SKIP_NGINX" != 1 ]; then
    say "nginx: $DOMAIN"
    [ -S "$PHP_SOCK" ] || die "не найден сокет PHP-FPM $PHP_SOCK. Посмотрите ls /run/php/ и укажите: PHP_SOCK=/run/php/... bash install.sh"
    for f in /etc/nginx/sites-enabled/*; do
        [ "$(basename "$(readlink -f "$f")")" = "$DOMAIN" ] && continue
        if grep -Eq "server_name[^;]*[[:space:]](www\.)?$DOMAIN[[:space:];]" "$f" 2>/dev/null; then
            echo "Внимание: домен $DOMAIN упомянут и в $f — уберите его оттуда, иначе nginx может отдавать не тот сайт."
        fi
    done

    tmp="$(mktemp)"
    cert_name="$DOMAIN"
    if [ -f "/etc/letsencrypt/live/$cert_name/fullchain.pem" ]; then
        render_nginx deploy/nginx.ssl.conf.template "$cert_name" > "$tmp"
        install_nginx "$tmp" && echo "HTTPS включён (сертификат $cert_name)." || die "nginx не принял настройку — оставил прежнюю"
    else
        render_nginx deploy/nginx.conf.template > "$tmp"
        install_nginx "$tmp" || die "nginx не принял настройку — остальные сайты не затронуты"

        if [ "$SKIP_SSL" != 1 ] && command -v certbot >/dev/null; then
            domains=(-d "$DOMAIN")
            points_here "www.$DOMAIN" && domains+=(-d "www.$DOMAIN")
            if points_here "$DOMAIN"; then
                say "Выпускаю сертификат: ${domains[*]}"
                if certbot certonly --webroot -w "$APP_DIR/public" "${domains[@]}" --cert-name "$cert_name" \
                        --non-interactive --agree-tos --keep-until-expiring --register-unsafely-without-email \
                        --deploy-hook "systemctl reload nginx"; then
                    render_nginx deploy/nginx.ssl.conf.template "$cert_name" > "$tmp"
                    install_nginx "$tmp" && echo "HTTPS включён." || echo "Сертификат есть, но nginx не принял HTTPS-настройку — сайт пока работает по http."
                else
                    echo "Сертификат выпустить не удалось — сайт работает по http. Повторите установку позже."
                fi
            else
                echo "DNS для $DOMAIN пока не указывает на этот сервер. Когда обновится — запустите установку ещё раз, HTTPS включится сам."
            fi
        elif ! command -v certbot >/dev/null; then
            echo "certbot не найден (apt install certbot) — сайт работает по http."
        fi
    fi
    rm -f "$tmp"
fi

if [ "$(php artisan tinker --execute 'echo App\Models\User::count();' 2>/dev/null | tail -1)" = "0" ]; then
    if [ -t 0 ]; then
        say "Заведите администратора для входа в /admin"
        php artisan make:filament-user
    else
        say "Администратора пока нет. Создайте: cd $APP_DIR && php artisan make:filament-user"
    fi
fi

scheme=http; [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ] && scheme=https
say "Готово"
echo "Сайт:    $scheme://$DOMAIN/"
echo "Админка: $scheme://$DOMAIN/admin"
