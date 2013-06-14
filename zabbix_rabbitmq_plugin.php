<?php

/**********************************************************************
                     GNU GENERAL PUBLIC LICENSE
***********************************************************************
Copyright (c) 2013 by Adam Curtis

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

************************************************************************/

// Zabbix Configuration
define('ZABBIX_HOSTNAME', 'rabbitmq');

// RabbitMQ Configuration
define('API_HOSTNAME', 'rabbitmq');
define('API_PORT', 15672);
define('API_USER', 'guest');
define('API_PASS', 'guest');

// Log file, contacts the output when we pass data to zabbix
$log_file_name = "/tmp/zabbix_rabbitmq_plugin.log";
$log_file_handle = fopen($log_file_name, 'w');
$log_file_data = array();

// Data file, contacts the output to pass to zabbix
$data_file_name = '/tmp/zabbix_rabbitmq_plugin.data';
$data_file_handle = fopen($data_file_name, 'w');

// This function will write to log file
function write_to_log_file($output_line)
{
    global $log_file_handle ;
    fwrite($log_file_handle, "$output_line\n") ;
}

// This will write to data file
function write_to_data_file($variable, $value)
{
   global $data_file_handle ;
   fwrite($data_file_handle, ZABBIX_HOSTNAME . " rabbitmq.$variable $value\n") ;
}

// This will handle all the curl requests to the endpoint
function get_curl_request($api_endpoint = '/api/overview')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . API_HOSTNAME . ':' . API_PORT . $api_endpoint);
    curl_setopt($ch, CURLOPT_USERPWD, API_USER . ":" . API_PASS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    curl_close($ch);

    return $output;
}

// Get the RabbitMQ stats
$output = get_curl_request();

if(!empty($output)){
	//Decode the json
	$data = json_decode($output);

	if($data){
		// Version information
		write_to_data_file('version', $data->rabbitmq_version);
		write_to_data_file('erlang.version', $data->erlang_version);

		// Message stats
		write_to_data_file('message.publish', $data->message_stats->publish);
		write_to_data_file('message.publish.rate', $data->message_stats->publish_details->rate);
		write_to_data_file('message.ack', $data->message_stats->ack);
		write_to_data_file('message.ack.rate', $data->message_stats->ack_details->rate);
		write_to_data_file('message.deliver_get', $data->message_stats->deliver_get);
		write_to_data_file('message.deliver_get.rate', $data->message_stats->deliver_get_details->rate);
		write_to_data_file('message.redeliver_details', $data->message_stats->redeliver);
		write_to_data_file('message.redeliver_details.rate', $data->message_stats->redeliver_details->rate);
		write_to_data_file('message.deliver', $data->message_stats->deliver);
		write_to_data_file('message.deliver.rate', $data->message_stats->deliver_details->rate);
		write_to_data_file('message.get', $data->message_stats->get);
		write_to_data_file('message.get.rate', $data->message_stats->get_details->rate);

		// Object Totals
		write_to_data_file('total.consumers', $data->object_totals->consumers);
		write_to_data_file('total.queues', $data->object_totals->queues);
		write_to_data_file('total.exchanges', $data->object_totals->exchanges);
		write_to_data_file('total.connections', $data->object_totals->connections);
		write_to_data_file('total.channels', $data->object_totals->channels);

		write_to_data_file('total.listeners', count($data->listeners));

		// Send data to zabbix
		exec("zabbix_sender -vv -z 127.0.0.1 -i $data_file_name 2>&1", $log_file_data);

		// Write any logs
		foreach ($log_file_data as $log_line) {
			write_to_log_file("$log_line\n");
		}
	}
}
else {
	write_to_log_file("Couldn't contact RabbitMQ API\n") ;
}

fclose($data_file_handle);
fclose($log_file_handle);

exit;