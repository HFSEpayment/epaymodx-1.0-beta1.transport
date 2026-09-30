<?php return {'manifest-version': '1.1',
 'manifest-attributes': {'license': 'MIT',
                         'readme': 'UniversalEpay — Halyk ePay for MODX 3 / MiniShop3. Настройки Halyk находятся в '
                                   'Extras → UniversalEpay. Для ShopKeeper3 используется тот же Extra через отдельный '
                                   'адаптер.',
                         'changelog': '1.0.0-beta2: MiniShop3 1.11.x compatibility, correct redirect response, clean '
                                      'Halyk payment object, robust order amount fallback.'},
 'manifest-vehicles': [{'vehicle_package': 'transport',
                        'vehicle_class': 'xPDOObjectVehicle',
                        'class': 'modNamespace',
                        'guid': 'fa180df9e6834154bb844cc0b9058fc5',
                        'native_key': 'universalepay',
                        'filename': 'modNamespace/universalepay.vehicle',
                        'namespace': 'universalepay'}]};