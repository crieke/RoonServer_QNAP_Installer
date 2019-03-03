#!/bin/sh

. /etc/script/lib/command.sh

APKG_PKG_DIR=/usr/local/AppCentral/RoonServer
BASH_CMD=`find /usr/local/AppCentral/entware/ -type f -name "bash"`
PID_FILE=/var/run/RoonServer_webui.pid
APKG_NAME="RoonServer"
WEBUI_STATUS="$APKG_PKG_DIR/web-status"
ROON_TMP_DIR="${APKG_PKG_DIR}/tmp"
ROON_PIDFILE="${APKG_PKG_DIR}/RoonServer.pid"
ROON_WEBACTIONS_PIDFILE="${APKG_PKG_DIR}/webactions.pid"
ROON_DATABASE_DIR=`awk -F "= " '/DB_Path/ {print $2}' ${APKG_PKG_DIR}/etc/RoonServer.conf`
ROON_DATABASE_DIR_FS=`df -T "${ROON_DATABASE_DIR}" | grep "^/dev" | awk '{print $2}'`
ROON_LOG_FILE="${APKG_PKG_DIR}/RoonServer.log"
ROON_DEBUG_EXTERNAL_LOG="${ROON_DATABASE_DIR}/ROON_DEBUG_EXTERNAL_LOG.txt"
WEBUI_HELPER_SCRIPT="${APKG_PKG_DIR}/helper-scripts/webui-actions.sh"

JAVA_CMD="/usr/local/bin/java"

if [[ -f $ROON_DEBUG_EXTERNAL_LOG ]]; then
	ROON_LOG_FILE=$ROON_DEBUG_EXTERNAL_LOG
fi

## Log Function
echolog () {
	TIMESTAMP=$(date +%d.%m.%y-%H:%M:%S)
	if [[ $# == 2 ]]; then
		PARAMETER1=$1
		PARAMETER2=$2
		echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${HL_COLOR}${PARAMETER1}:${REG_COLOR} ${PARAMETER2}"
		echo "${TIMESTAMP} --- ${PARAMETER1}: ${PARAMETER2}" >> $ROON_LOG_FILE
	elif [[ $# == 1 ]]; then
		PARAMETER1=$1
		echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${PARAMETER1}"
		echo "${TIMESTAMP} --- ${PARAMETER1}" >> $ROON_LOG_FILE
	else
		echo -e "The echolog function requires 1 or 2 parameters."
	fi
}

# Check if bash symlink exists
if [ ! -L /bin/bash ]; then
    echolog "Creating symlink for bash."
    ln -sf $BASH_CMD /bin/bash
else
    echolog "bash exists."
fi

if [ -f $ROON_PIDFILE ]; then
	PID=`cat "${ROON_PIDFILE}"`
fi


case $1 in

	start)
    	echo "" > $ROON_LOG_FILE
		# start script here
    	watch -n 5 $WEBUI_HELPER_SCRIPT &
        echo $! > $ROON_WEBACTIONS_PIDFILE;
		echo "start" > $WEBUI_STATUS
	;;

	stop)
		# Stopping RoonServer itself
		if [ -f "$ROON_PIDFILE" ]; then
			echolog "Stopping RoonServer..."
			echolog "Roon PID to be killed" "$PID"
			kill ${PID} >> $ROON_LOG_FILE
			rm "${ROON_PIDFILE}"
		fi
		
		#Stop watch process, which is watching for processes from the ui
		if [ -f "$ROON_WEBACTIONS_PIDFILE" ]; then
			kill `cat $ROON_WEBACTIONS_PIDFILE` 2> /dev/null
			rm "${ROON_WEBACTIONS_PIDFILE}"
		fi

	    echolog "RoonServer has been stopped."

		#Garbage Cleanup
		#Clean RoonServer's tmp directory
		if [ ! -z "$(ls -A ${ROON_TMP_DIR})" ]; then
			rm -R "${ROON_TMP_DIR}/*"
		fi

		#Remove old logfiles zip in RoonServer's web tmp directory
		if [ ! -z "$(ls -A $APKG_PKG_DIR/www/tmp)" ]; then
			rm -R "$APKG_PKG_DIR/www/tmp/*"  
		fi

		#Remove lockfile if RoonServer has been stopped when helper process was active
		if [ ! -f "${APKG_PKG_DIR}/.webui.lock" ]; then
		    rm "${APKG_PKG_DIR}/.webui.lock";
		fi
		;;

	*)
		echo "usage: $0 {start|stop}"
		exit 1
		;;
		
esac

exit 0
