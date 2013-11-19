#!/bin/bash

#	This script does the basic maintenance work for the maize web application.
#   Run this script with root privileges.

run_logs=""

# clean old temporary csv files
find /var/www/html/MaizeWebApp/data/temp_csv_files/*.csv -mtime +1 | /usr/bin/xargs \rm -rf
OUT=$?
if [ $OUT -eq 0 ];then
	run_logs="    Deleted old temporary CSV files .."
else
	run_logs="    Failed to delete old temporary CSV files .."
fi

# clean old log files older than 1 day
find /var/www/html/MaizeWebApp/application/logs/*.php -mtime +1 | /usr/bin/xargs \rm -rf
OUT=$?
if [ $OUT -eq 0 ];then
	run_logs=" $run_logs \n Deleted old log files .."
else
	run_logs=" $run_logs \n Failed to delete old log files .."
fi

# invoke the database refresh script
psql -U maizeuser -d maize -f /var/www/html/MaizeWebApp/scripts/maize_view_as_table_refresh_script.sql  
OUT=$?
if [ $OUT -eq 0 ];then
	run_logs=" $run_logs \n Refreshed materialized views in maize database .."
else
	run_logs=" $run_logs \n Failed to refresh materialized views in maize database .."
fi

# mail the logs of the run
echo -e $run_logs
