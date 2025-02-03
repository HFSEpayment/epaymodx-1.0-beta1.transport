<?php return array (
  'manifest-version' => '1.1',
  'manifest-attributes' => 
  array (
    'license' => '
--------------------------

Everyone is permitted to copy and distribute verbatim copies
of this license document, but changing it is not allowed

---------------------------
END OF TERMS AND CONDITIONS',
    'readme' => 'Инструкция по установке модуля

1. Для установки платежного модуля hbepay необходимо произвести следующие действия:
    Скачайте архив модуля и распакуйте его. Загрузите архив hbepay: “Пакеты” – “Установщик” – “Загрузить плагины” – “Загрузить пакет с компьютера”. 
    В таблице пакетов появится пакет "hbepay". Нажмите кнопку “Установить”. Завершите установку.


2. Необходимо создать 3 страницы:
    страница оплаты через hbepay. 
    Вставить в поле "Содержимое ресурса" вызов сниппета:
    [[!hbepay? action=`payment`]]
    страница с сообщением об успешной оплате (с любым содержанием). Вставить в поле "Содержимое ресурса" вызов сниппета:
    [[!hbepay? action=`success`]]
    страница с сообщением об отмене оплаты. Вставить в поле "Содержимое ресурса" вызов сниппета:
    [[!hbepay? action=`fail`]]


3. Измените параметры сниппета hbepay:
    Импортируем файл со списком параметров: Добавить набор параметров – выбираем config.js, который поставляется вместе с плагином.
    Test – 0. Если прописать 1, включается тестовый режим и денежные средства со счета не снимаются.
    Backlink - http://имя_вашего_сайта/index.php?id=ID_документа страницы
        ID_ документа страницы - страница с сообщением об успешной оплате
    Failure_backlink- http://имя_вашего_сайта/index.php?id=ID_ документа страницы
        ID_ документа страницы - страница с сообщением об отмене оплаты
    Payment_form- http://имя_вашего_сайта/index.php?id=ID_ документа страницы
        ID_ документа страницы - страница с формой оплаты hbepay



4. На странице формы оформления заказа в вызове сниппета FormIt в список используемых хуков необходимо добавить hbepay перед redirect. 
    В методе оплаты добавить hbepay. Теперь после отправки заказа на следующей странице будет появляться кнопка "Оплатить сейчас".
Удачных платежей.',
    'changelog' => 'Everyone is permitted to copy and distribute verbatim copies
of this license document, but changing it is not allowed.',
  ),
  'manifest-vehicles' => 
  array (
    0 => 
    array (
      'vehicle_package' => 'transport',
      'vehicle_class' => 'xPDOObjectVehicle',
      'class' => 'modNamespace',
      'guid' => '9e5c94ae30ca0471ad0c12403b53f730',
      'native_key' => 'hbepay',
      'filename' => 'modNamespace/ad543c3135b235a7666c4046ed18c1a4.vehicle',
      'namespace' => 'hbepay',
    ),
    1 => 
    array (
      'vehicle_package' => 'transport',
      'vehicle_class' => 'xPDOObjectVehicle',
      'class' => 'modCategory',
      'guid' => 'e4196ab2f0412ba779cb10c398ef3e70',
      'native_key' => 1,
      'filename' => 'modCategory/bf356cc82470666d841cbde34627ba58.vehicle',
      'namespace' => 'hbepay',
    ),
  ),
);