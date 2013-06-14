Zabbix RabbitMQ Monitoring Plugin
===============

A Zabbix plugin to monitor RabbitMQ. This plugin is very basic and needs lots more work for production usage.

This plugin requires the RabbitMQ HTTP Management to be installed.


Installation Instructions
-------------
1. Copy __zabbix_rabbitmq_plugin.sh__ and __zabbix_rabbitmq_plugin.php__ in to your Zabbix external scripts folder.
2. Edit the variables in __zabbix_rabbitmq_plugin.php__ to match your environment setup.
3. Make sure both files are executable by the zabbix user.
4. Import the Template in to Zabbix and have fun!
