#!/bin/bash

inotifywait -r -e "create,modify,move" --monitor /tpl/ | \
    while read -r notifies;
    do
	MATCHES=`echo "$notifies" | grep 'MODIFY .*\.tpl$'`
	if [ -n "$MATCHES" ]; then
		FILENAME=`echo "$notifies" | grep -oP 'MODIFY \K.*.tpl$'`
		DIR=`echo "$notifies" | grep -oP '(?<=^/tpl/).*(?= MOVED_TO)'`
		echo "Rebuilding $DIR$FILENAME"
		wget -q -O/dev/null "lamp:80/templatemgr.php?rebuild=/$DIR$FILENAME"
	fi
done
