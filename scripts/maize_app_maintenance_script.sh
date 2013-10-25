#!/bin/bash

#	This script does the basic maintenance work for the maize web application.
#   Run this script with root privileges.

run_logs=""

# clean old temporary csv files
find ../data/temp_csv_files -mtime +1 | xargs \rm -rf
OUT=$?
if [ $OUT -eq 0 ];then
	run_logs="Deleted old temporary CSV files .."
else
	run_logs="Failed to delete old temporary CSV files .."
fi

# clean old log files older than 1 day
find ../data/temp_csv_files -mtime +1 | xargs \rm -rf
OUT=$?
if [ $OUT -eq 0 ];then
	run_logs=" $run_logs \n Deleted old log files .."
else
	run_logs=" $run_logs \n Failed to delete old log files .."
fi

# invoke the database refresh script
psql -U maizeuser -f maize_view_as_table_refresh_script.sql
OUT=$?
if [$OUT -eq 0];then
 	run_logs =  " $run_logs \n Refreshed crosstab tables in maize database .."
else
 	run_logs = " $run_logs \n Failed to refresh crosstab tables in maize database .."
fi

# mail the logs of the run
echo $run_logs | mailx -s "Maize Web App Job Run Logs" skprasad@cs.wisc.edu
