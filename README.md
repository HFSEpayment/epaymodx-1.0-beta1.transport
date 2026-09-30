UniversalEpay

Halyk ePay для MODX 3 + MiniShop3.

Установка
Установить universalepay-x.x.x.transport.zip через Extras → Installer.
Открыть Extras → UniversalEpay и выполнить настройку.
Выбрать Test mode для тестирования.
UniversalEpay автоматически добавит способ оплаты в MiniShop3.
Сборка

В терминале перейти в папку исходников UniversalEpay:

cd /Users/dos/Desktop/Plugins/universalepay-1.0.0-full

Затем выполнить:

php _build/build.php

Готовый .transport.zip появится в папке core/packages/ вашего MODX-проекта:

/Users/dos/Desktop/Plugins/modx/core/packages/

После этого .transport.zip устанавливается через MODX → Extras → Installer.

Требования: MODX 3.x, MiniShop3, PHP 8.1+.